<?php

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    echo (ini_get("upload_max_filesize"));
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
        $python_output1 = shell_exec('cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py archivos/prueba.txt'); 
        $python_output2 = shell_exec('cd /var/www/html/apirestClAtiende && python3.10 paquetes/etiquetar.py archivos/prueba.txt'); 
        $python_output3 = shell_exec('cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache python3.10 paquetes/resumir.py archivos/prueba.txt'); 
        
        // Agregar la salida del script de Python al array de respuesta
        $response[] = "Script ejecutado:";
        $response[] = $python_output1;
        $response[] = $python_output2;
        $response[] = $python_output3;
        
        // Imprimir la respuesta completa como JSON
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
        exit();

    } catch (\Throwable $th) {
        $response[] = "Script no ejecutado, error:";
        $error = $th->getMessage();
        $response[] = $error;
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // Aumentar el tiempo límite de ejecución a un valor en segundos (por ejemplo, 300 segundos)
    set_time_limit(1200);
    $response = array();    
    /* $pdf = "Pension_Garantizada_Universal_PGU.pdf";
    $txt = "Pension_Garantizada_Universal_PGU.txt";
    try {
        shell_exec ("cd /var/www/html/apirestClAtiende/archivos && pdftotext $pdf $txt");
        $response[] = "Funciono como apache";
        $response[] = shell_exec ("cd /var/www/html/apirestClAtiende/archivos && ls -als");
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $th) {
        $response[] = "No funciono como apache";
        $error = $th->getMessage();
        $response[] = $error;
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
    } */
    
    try {
        return false;
    } catch (\Throwable $th) {
        $respuesta = "No se logró hacer la prueba.";
        $error = $th->getMessage();
        $fechaHora = preg_replace('/\s+/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                        
        $nombreArchivo = "logs_de_error.txt";                    
        // Abre o crea el archivo de log en modo de escritura al final del archivo
        $nombreArchivo = fopen($rutaLog, "a");
        // Escribe la excepcion junto con la fecha y hora en el archivo de log
        fwrite($nombreArchivo, "[$fechaHora]_$error" . PHP_EOL);
        // Cierra el archivo de log
        fclose($nombreArchivo);
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    date_default_timezone_set('America/Santiago');
    $response[] = preg_replace('/\s+/', '_', date("Y-m-d H:i:s"));
    
    echo json_encode($response,JSON_UNESCAPED_UNICODE);
}

?>