<?php
require_once('app/class/class.connectiondb.php');
$servername = "localhost";
$username = "root";
$password = "";

$nombre = $_POST["nombre"];
$tel = $_POST["tel"];
$email = $_POST["email"];
$tipo = $_POST["tipo"];

var_dump($nombre);
var_dump($tel);
var_dump($email);
var_dump($tipo);

$con = new ConnectionDB;

if ($tipo == 'comprador') {

    $data = array($nombre, $tel, $email);
    $c = "INSERT INTO compradores(nombre, telefono, correo) VALUES (?,?,?)";
    $q = $con->query($c, $data);

    if (!$q) {
        echo 'Fallo el registro';
    } else {
        echo 'Registro correcto';
    }

    $data = array($nombre, $tel, $email);
    $c = "INSERT INTO compradores(nombre, telefono, correo) VALUES (?,?,?)";
    $q = $con->query($c, $data);

    if (!$q) {
        echo 'Fallo el registro';
    } else {
        echo 'Registro correcto';
    }

} else {

    $data = array($nombre, $tel, $email);
    $c = "INSERT INTO viajeros(nombre, telefono, correo) VALUES (?,?,?)";
    $q = $con->query($c, $data);

    if (!$q) {
        echo 'Fallo el registro';
    } else {
        echo 'Registro correcto';
    }

}

$con->close();
?>