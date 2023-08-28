<?php

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de analisis, busqueda y preguntas frecuentes";
    echo json_encode ($respuesta); 
}; 

// pruebas en index usando post
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    echo json_encode("Intentando ejecutar script python espere");
    try {
        // Ejecuta el script de Python y captura la salida
        $python_output = shell_exec('python3.10 ./paquetes/extraer.py ./archivos/prueba.txt');

        // Decodifica la salida JSON en un array asociativo
        $response_data = json_decode($python_output, true);

        // Imprime la respuesta JSON en pantalla
        echo json_encode($response_data);
        echo json_encode("Script ejecutado");
        exit();
    } catch (\Throwable $th) {
        echo json_encode("Script no ejecutado, error");
        $error = $th->getMessage();
        echo json_encode($error);
    }
}; 

?>