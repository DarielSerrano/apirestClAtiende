<?php


//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de análisis, búsqueda y preguntas frecuentes.";
    echo json_encode ($respuesta);      
}; 

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
        // Código que puede generar una excepción
        throw new Exception("¡Excepción intencional!");
    } catch (\Throwable $th) {
        $respuesta = "No se logró hacer la prueba.";
        $error = $th->getMessage();
        $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                     
        $rutaLog = "logs_de_error.txt";                    
        // Abre o crea el archivo de log en modo de escritura al final del archivo
        $rutaLog = fopen($rutaLog, "a");
        // Escribe la excepcion junto con la fecha y hora en el archivo de log
        fwrite($rutaLog, "[$fechaHora]($respuesta)_$error" . PHP_EOL);
        // Cierra el archivo de log
        fclose($rutaLog);
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    date_default_timezone_set('America/Santiago');
    $response[] = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual
    
    echo json_encode($response,JSON_UNESCAPED_UNICODE);
}



?>