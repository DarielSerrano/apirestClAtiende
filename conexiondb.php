<?php

include 'keysdb.php';

// Crear la conexión
$conn = new mysqli($servername, $username, $dbpassword, $dbname);

try {
    if ($conn->connect_error) {
        // Manejar error en la consulta de inserción
        $error_message = $conn->error; // Mensaje de error generado
        // Cerrar la conexión
        $conn->close();
        throw new Exception($error_message);
    }
    else {
        // Conexion exitosa
    }
}
catch (\Throwable $th) {
    $respuesta = "Hubo un problema interno en el servidor.";
    $error = $th->getMessage();
    $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                      
    $rutaLog = "logs_de_error.txt";                    
    // Abre o crea el archivo de log en modo de escritura al final del archivo
    $rutaLog = fopen($rutaLog, "a");
    // Escribe la excepción junto con la fecha y hora en el archivo de log
    fwrite($rutaLog, "[$fechaHora]($respuesta)_$error" . PHP_EOL);
    // Cierra el archivo de log
    fclose($rutaLog);
    header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
    header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

?>