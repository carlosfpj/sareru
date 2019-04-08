<?php
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

if($tipo=="comprador") {
    try {
        $conn = new PDO("mysql::host=$servername;dbname=clientes",$username,$password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "INSERT INTO compradores(nombre, telefono, correo) 
                VALUES ('$nombre', '$tel', '$email')";
        $conn->exec($sql);
        echo "registro correcto";
    
    } catch (\Throwable $th) {
        echo "conexión incorrecta: " . $th->getMessage();
    }

}else {
    try {
        $conn = new PDO("mysql::host=$servername;dbname=clientes",$username,$password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "INSERT INTO viajeros(nombre, telefono, correo) 
                VALUES ('$nombre', '$tel', '$email')";
        $conn->exec($sql);
        echo "registro correcto";
    
    } catch (\Throwable $th) {
        echo "conexión incorrecta: " . $th->getMessage();
    }
}

$conn = null;
?>