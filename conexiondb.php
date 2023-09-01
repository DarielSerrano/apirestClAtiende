<?php

include 'keysdb.php';

// Crear la conexión
$conn = new mysqli($servername, $username, $dbpassword, $dbname);

// Verificar la conexión
if ($conn->connect_error) 
{
    die("Conexión fallida: " . $conn->connect_error);
}

/* Ejemplo de consulta 
$sql = "SELECT * FROM nombre_de_tabla";

$result = $conn->query($sql);  
*/

?>