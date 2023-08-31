<?php

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    try {
        include 'conexiondb.php';
        $sql = "SELECT `DocumentosCategoriaNombre` FROM `DocumentosCategoria`";
        // Limpieza ante posibles inyecciones
        $sql = preg_replace('/[^0-9A-Za-z\s(),?¿¡!\'\":._\-$+=\*%º]/',"",$sql);
        if ($resultado_consulta = $conn->query($sql)) {
            // Modificar los valores de DocumentosRutaGuardado para crear enlaces
            foreach ($resultado_consulta as $row) {
                $documentos_modificados[] = $row['DocumentosCategoriaNombre'];                
            }
            // Crear un objeto JSON con los resultados modificados
            echo json_encode($documentos_modificados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } 
        else {
            // Manejar error en la consulta de inserción
            $error_message = $conn->error; // Mensaje de error generado
            // Cerrar la conexión
            $conn->close();
            throw new Exception($error_message);
        } 
        // Cerrar la conexión
        $conn->close();

    } catch (\Throwable $th) {
        $respuesta = "Hubo un problema intentar la busqueda en el sistema.";
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
}; 

?>