<?php

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de análisis, búsqueda y preguntas frecuentes.";
    echo json_encode ($respuesta);      
}; 

?>