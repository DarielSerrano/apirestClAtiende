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
        $python_output = shell_exec('python tu_script.py');  // Reemplaza "tu_script.py" con el nombre de tu script Python

        // Imprime la salida del script de Python (sin procesar)
        echo $python_output;

        echo json_encode("Script ejecutado");
        exit();
    } catch (\Throwable $th) {
        echo json_encode("Script no ejecutado, error");
        $error = $th->getMessage();
        echo json_encode($error);
    }
}; 

?>