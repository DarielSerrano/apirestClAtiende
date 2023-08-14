<?php
include 'sesionadmin.php';
if(isset($_SESSION['idUsuario']) && $_SESSION['idRol'] != 1) 
{
    // El usuario tiene una sesión válida pero no es administrador
    // El usuario no tiene una sesión válida o no tiene el rol de administrador
    $respuesta['mensaje']='El Usuario no es administrador';
    header("HTTP/1.1 200 OK");
    echo json_encode($respuesta); 
    exit();
} 
?>