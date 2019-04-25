<?php
/**
 * ConnectionDB
 *
 * @author    Alejandro Blackburn <alejandro.blackburn@gmail.com>
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.2-master
 */
final class ConnectionDB {

    private $connection;
    private $num_rows;
    private $last_id;
    private $error_query;
    private $error_connect;
    private $host = 'localhost';
    private $database = 'clientes';
    private $user_db = 'root';
    private $password_db = '';

    public function __construct($options_db = array()) {
        $this->setParamsConnection($options_db);
        $this->init();
    }

    private function setParamsConnection($options_db = array()) {

        $tam_params = count($options_db);

        if($tam_params > 0) {

            $this->host = $options_db['host'];
            $this->database = $options_db['database'];
            $this->user_db = $options_db['user_db'];
            $this->password_db = $options_db['password_db'];

        } else {
            return;
        }

    }

    private function init() {

        $this->connection = new MySQLi($this->host, $this->user_db, $this->password_db, $this->database);

        if ($this->connection->connect_error) {
            $this->error_connect = $this->connection->connect_error;
            return false;
        } else {
            $this->setCharset();
            return true;
        }

    }

    public function query($sql, $values = '') {

        if (!empty($sql)) {

            $tam_values = count($values);

            if (($tam_values > 0) && (is_array($values))) {
                return $this->executeSQL($sql,$values,'SQL_PREPARED');
            } else {
                return $this->executeSQL($sql,'','SQL_RAW');
            }

        } else {
            return false;
        }

    }

    private function executeSQL($sql, $values, $type) {

        if ($type == 'SQL_PREPARED') {

            $query = $this->connection->prepare($sql);

            if (!$query) {
                $this->error_query = $this->connection->error;
                return false;
            } else {

                $values = $this->cleanParams($values);

                if (!$values) {
                    return false;
                } else {

                    $type_params = $this->getTypeParams($values);

                    array_unshift($values,$type_params);

                    $bind = call_user_func_array(array($query, 'bind_param'), $this->refValues($values));

                    if (!$bind) {
                        $this->error_query = $query->error;
                        return false;
                    } else {

                        $status = $query->execute();

                        if (!$status) {
                            $this->error_query = $this->connection->error;
                            return false;
                        } else {

                            $type_query = $this->getTypeQuery($sql);

                            switch ($type_query) {

                                case 'INSERT':
                                    $this->last_id = (int) $this->connection->insert_id;
                                    return $query;
                                case 'UPDATE':
                                case 'DELETE':
                                    return $query;

                                case 'SELECT':
                                    $result = $query->get_result();
                                    $this->num_rows = (int) $result->num_rows;
                                    return $result;

                                default:
                                    return true;

                            }

                        }

                    }

                }

            }

        } else if ($type == 'SQL_RAW') {

            $query = $this->connection->query($sql);

            if (!$query) {
                $this->error_query = $this->connection->error;
                return false;
            } else {

                $type_query = $this->getTypeQuery($sql);

                switch ($type_query) {

                    case 'INSERT':
                        $this->last_id = (int) $this->connection->insert_id;
                        return $query;
                    case 'UPDATE':
                    case 'DELETE':
                        return $query;

                    case 'SELECT':
                        $this->num_rows = (int) $query->num_rows;
                        return $query;

                    default:
                        return true;

                }

            }

        } else {
            return false;
        }

    }

    public function getErrorQuery() {
        return $this->error_query;
    }

    public function getErrorConnection() {
        return $this->error_connect;
    }

    private function getTypeQuery($sql) {

        $q = explode(' ',$sql);
        $type_query = $q[0];
        $type_query = trim($type_query, '(');

        return $type_query;

    }

    public function getResults($result) {
        return $result->fetch_assoc();
    }

    public function freeQuery($query) {

        $query->free_result();

        return;

    }

    public function getNumRows() {
        return (int) $this->num_rows;
    }

    public function getLastId() {
        return (int) $this->last_id;
    }

    public function cleanValue($value) {

        $param = filter_var(trim($this->connection->real_escape_string($value)), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        return $param;

    }

    private function cleanParams($params) {

        if (is_array($params)) {

            $tam_params = count($params);

            if ($tam_params > 0) {

                $clean_params = array();

                foreach ($params as $key => $value) {
                    $clean_params[$key] = $this->cleanValue($value);
                }

                return $clean_params;

            } else {
                return false;
            }

        } else {
            return false;
        }

    }

    private function setCharset() {
        $this->connection->set_charset('utf8');
    }

    public function __destruct() {
        $this->close();
        return;
    }

    public function close() {
        $this->connection->close();
        return;
    }

    public function closeQuery($query) {

        $query->close();

        return;

    }

    //INNODB

    public function initCommit() {
        return $this->connection->autocommit(false);
    }

    public function setCommit() {
        $status = $this->connection->commit();
        $this->connection->autocommit(true);
        return $status;
    }

    public function rollBack() {
        $status = $this->connection->rollback();
        $this->connection->autocommit(true);
        return $status;
    }

    private function getTypeParams($values) {

        $type_values = '';

        foreach ($values as $key => $value) {

            switch (gettype($value)) {

                case 'NULL':
                case 'string':
                    $type_values .= 's';
                    break;

                case 'boolean':
                case 'integer':
                    $type_values .= 'i';
                    break;

                case 'blob':
                    $type_values .= 'b';
                    break;

                case 'double':
                    $type_values .= 'd';
                    break;

            }

        }

        return $type_values;

    }

    private function refValues($arr){

        if (strnatcmp(phpversion(),'5.3') >= 0) {

            $refs = array();

            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }

            return $refs;

        }

        return $arr;

    }

}
?>