<?php

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de analisis, busqueda y preguntas frecuentes";
    echo json_encode ($respuesta); 
}; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aumentar el tiempo límite de ejecución a un valor en segundos (por ejemplo, 300 segundos)
    set_time_limit(1200);

    $response = array();

    $response[] = "Intentando ejecutar script python, espere";
    try {
        // Ejecuta el script de Python y captura la salida
        $python_output1 = shell_exec('python3.10 paquetes/extraer.py archivos/prueba.txt'); 
        $python_output2 = shell_exec('python3.10 paquetes/etiquetar.py archivos/prueba.txt'); 
        $python_output3 = shell_exec('python3.10 paquetes/resumir.py archivos/prueba.txt'); 

        // Agregar la salida del script de Python al array de respuesta
        $response[] = "Script ejecutado:";
        $response[] = $python_output1;
        $response[] = $python_output2;
        $response[] = $python_output3;
        
        // Imprimir la respuesta completa como JSON
        echo json_encode($response);
        exit();

    } catch (\Throwable $th) {
        $response[] = "Script no ejecutado, error:";
        $error = $th->getMessage();
        $response[] = $error;
    }
}

?>