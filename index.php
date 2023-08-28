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
    $response = array(); // Crear un array para almacenar las respuestas

    $response[] = "Intentando ejecutar script python, espere";
    try {
        // Ejecuta el script de Python y captura la salida
        $python_output = shell_exec('python3.10 ./paquetes/extraer.py ./archivos/prueba.txt'); 
        $python_output2 = shell_exec('python3.10 ./paquetes/test.py'); 

        // Agregar la salida del script de Python al array de respuesta
        $response[] = "Script ejecutado:";
        $response[] = $python_output;
        $response[] = $python_output2;
        

    } catch (\Throwable $th) {
        $response[] = "Script no ejecutado, error:";
        $error = $th->getMessage();
        $response[] = $error;
    }

    // Imprimir la respuesta completa como JSON
    echo json_encode($response);
    exit();
}

?>