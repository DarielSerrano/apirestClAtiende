<?php

// Código del método POST para consultar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Establece la zona horaria a Santiago y limita el tiempo de ejecución a 1200 segundos (20 minutos)
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);

    // Inicializa un arreglo para almacenar las respuestas
    $response = array();

    // Obtener los datos del formulario
    $consulta = $_POST['consulta'];

    if (empty($consulta)) {
        // No se ha ingresado una consulta, enviar respuesta de error
        $respuesta = "Debe ingresar una consulta para iniciar la búsqueda.";
        header("HTTP/1.1 400 Bad Request");  
        header('Content-Type: application/json; charset=UTF-8');  
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        $consulta = preg_replace('/[^A-Za-z\s.:,_\-?¿¡!ÁáÉéÍíÓóÚúüÑñ$%º]/', '', $consulta);  
        // Escapar la consulta de texto para usarla en el comando
        $escaped_consulta = escapeshellarg($consulta);

        // Construir el comando para ejecutar el script de Python con la cadena de texto como argumento
        $comando_python = "cd /var/www/html/apirestClAtiende && STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py " . $escaped_consulta;
        $sustantivo = null;
        $verb = null;

        // Inicio extracción palabras clave NLP
        try {
            $output = array();
            $return_var = 0;
            $errcapture = "2>&1";
            // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
            exec("$comando_python $errcapture", $output, $return_var);        

            // Verificar el estado de retorno para determinar si hubo un error
            if ($return_var === 0) {
                // Filtrar y extraer los objetos JSON de la salida
                foreach ($output as $line) {
                    if (preg_match('/^\{.*\}$/', $line)) {
                        $consultaConNLP = json_decode($line, true);
                        $consultaConNLP = $consultaConNLP['resultados'];
                    }
                }  

                $sustantivo = array(); // Almacenar sustantivos
                $verb = array(); // Almacenar sustantivos
                foreach ($consultaConNLP as $resultado) {
                    $palabra = $resultado['palabra'];
                    $clasificacion = $resultado['clasificacion'];
                    // Separar en verbos y sustantivos
                    if ($clasificacion == 'NOUN' || $clasificacion == 'PROPN') {
                        $sustantivo[] = $palabra;
                    } elseif ($clasificacion == 'VERB') {
                        $verb[] = $palabra;
                    }
                }     
            } 
            else {
                // Hubo un error al ejecutar el comando
                $error_message = implode("\n", $output); // Los mensajes de error generados
                throw new Exception($error_message);
            }
        } 
        catch (Exception $th) {
            // Procesar la excepción y generar una respuesta de error
            $respuesta = "Hubo un problema al hacer la extracción NLP a la búsqueda.";
            $error = $th->getMessage();
            $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s"));
            $rutaLog = "logs_de_error.txt";                    
            // Abre o crea el archivo de log en modo de escritura al final del archivo
            $rutaLog = fopen($rutaLog, "a");
            // Escribe la excepción junto con la fecha y hora en el archivo de log
            fwrite($rutaLog, "[$fechaHora]($respuesta)_$error" . PHP_EOL);
            // Cierra el archivo de log
            fclose($rutaLog);
            header("HTTP/1.1 400 Bad Request");  
            header('Content-Type: application/json; charset=UTF-8');  
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        }  

        try {
            include 'conexiondb.php';
            $verbos = implode("', '", $verb); // Unir verbos en formato 'verbo1', 'verbo2', ...
            $sustantivos = implode("', '", $sustantivo); // Unir sustantivos en formato 'sustantivo1', 'sustantivo2', ...
            $sql = 
            "SELECT
                D.idDocumentos,
                D.DocumentosTitulo,
                D.DocumentosRutaGuardado,
                D.DocumentosResumen,
                DC.DocumentosCategoriaNombre AS Categoria,
                SUM(IFNULL(V.VerbosFrecuencia, 0)) AS SumaVerbosFrecuencia,
                SUM(IFNULL(S.SustantivosFrecuencia, 0)) AS SumaSustantivosFrecuencia,
                SUM(IFNULL(V.VerbosFrecuencia, 0) + IFNULL(S.SustantivosFrecuencia, 0)) AS TotalFrecuencia
            FROM (
                SELECT MAX(idDocumentos) AS idDocumentos, DocumentosTitulo
                FROM Documentos
                GROUP BY DocumentosTitulo
            ) MaxDocs
            INNER JOIN Documentos D ON MaxDocs.idDocumentos = D.idDocumentos
            LEFT JOIN (
                SELECT VerbosNombre, Documentos_idDocumentos, VerbosFrecuencia
                FROM Verbos
                WHERE VerbosNombre IN ('$verbos')
            ) V ON D.idDocumentos = V.Documentos_idDocumentos
            LEFT JOIN (
                SELECT SustantivosNombre, Documentos_idDocumentos, SustantivosFrecuencia
                FROM Sustantivos
                WHERE SustantivosNombre IN ('$sustantivos')
            ) S ON D.idDocumentos = S.Documentos_idDocumentos
            LEFT JOIN DocumentosCategoria DC ON D.DocumentosCategoria_idDocumentosCategoria = DC.idDocumentosCategoria
            WHERE (V.VerbosNombre IS NOT NULL OR S.SustantivosNombre IS NOT NULL)
            GROUP BY D.idDocumentos, D.DocumentosTitulo, D.DocumentosRutaGuardado, D.DocumentosResumen, DC.DocumentosCategoriaNombre
            ORDER BY TotalFrecuencia DESC
            LIMIT 5;";

            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);

            if ($resultado_consulta = $conn->query($sql)) {
                // Modificar los valores de DocumentosRutaGuardado para crear enlaces
                foreach ($resultado_consulta as $row) {
                    $enlaceDescarga = '<a href="http://146.83.194.142:1106/apirestClAtiende/descarga.php?doc=' . $row['DocumentosRutaGuardado'] . '">Descargar</a>';
                    $row['DocumentosRutaGuardado'] = $enlaceDescarga;
                    
                    $documentos_modificados[] = array(
                        "DocumentosTitulo" => $row['DocumentosTitulo'],
                        "DocumentosRutaGuardado" => $enlaceDescarga,
                        "DocumentosResumen" => $row['DocumentosResumen'],
                        "Categoria" => $row['Categoria']
                    );
                }
                header("HTTP/1.1 200 OK");
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($documentos_modificados, JSON_UNESCAPED_UNICODE);
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
            // Procesar la excepción y generar una respuesta de error
            $respuesta = "Hubo un problema intentar la búsqueda en el sistema.";
            $error = $th->getMessage();
            $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); 
            $rutaLog = "logs_de_error.txt";                    
            // Abre o crea el archivo de log en modo de escritura al final del archivo
            $rutaLog = fopen($rutaLog, "a");
            // Escribe la excepción junto con la fecha y hora en el archivo de log
            fwrite($rutaLog, "[$fechaHora]($respuesta)_$error" . PHP_EOL);
            // Cierra el archivo de log
            fclose($rutaLog);
            header("HTTP/1.1 400 Bad Request");  
            header('Content-Type: application/json; charset=UTF-8');  
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        }                                        
    }    
}



?>