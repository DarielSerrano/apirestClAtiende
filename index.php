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
    echo json_encode("Intentando ejecutar script python espere", JSON_PRETTY_PRINT);
    try {
        // Ejecuta el script de Python y captura la salida
        $python_output = exec('python3.10 paquetes/extraer.py archivos/prueba.txt');
        echo json_encode("\nScript ejecutado\n", JSON_PRETTY_PRINT);
    } catch (\Throwable $th) {
        echo json_encode("\nScript no ejecutado, error\n", JSON_PRETTY_PRINT);
        $error = $th->getMessage();
        echo json_encode($error, JSON_PRETTY_PRINT);
    }
    // Decodifica la salida JSON en un array asociativo
    $response_data = json_decode($python_output, true);

    // Imprime la respuesta JSON en pantalla
    echo json_encode($response_data, JSON_PRETTY_PRINT);
}; 

?>