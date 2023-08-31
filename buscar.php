<?php

//codigo del metodo post para recibir pdfs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);
    $response = array();    
    // Obtener los datos del formulario
    $consulta = $_POST['consulta'];
    if (empty($consulta)) {
        $respuesta = "Debe ingresar una consulta para iniciar la búsqueda.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        $consulta = preg_replace('/[^A-Za-z\s.:,_\-?¿¡!]/', '', $consulta);        
        // Escapar la consulta de texto para usarla en el comando
        $escaped_consulta = escapeshellarg($consulta);
        // Construir el comando para ejecutar el script de Python con la cadena de texto como argumento
        $comando_python = "cd /var/www/html/apirestClAtiende && STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py " . $escaped_consulta;
        $sustantivo = null;
        $verb = null;
        // Inicio extraccion palabras clave NLP
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
            $respuesta = "Hubo un problema al hacer la extracción NLP a la busqueda.";
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
            FROM
                Documentos D
            LEFT JOIN
                (
                    SELECT
                        VerbosNombre,
                        Documentos_idDocumentos,
                        VerbosFrecuencia
                    FROM
                        Verbos
                    WHERE
                        VerbosNombre IN ('$verbos')
                ) V
            ON
                D.idDocumentos = V.Documentos_idDocumentos
            LEFT JOIN
                (
                    SELECT
                        SustantivosNombre,
                        Documentos_idDocumentos,
                        SustantivosFrecuencia
                    FROM
                        Sustantivos
                    WHERE
                        SustantivosNombre IN ('$sustantivos')
                ) S
            ON
                D.idDocumentos = S.Documentos_idDocumentos
            LEFT JOIN
                DocumentosCategoria DC
            ON
                D.DocumentosCategoria_idDocumentosCategoria = DC.idDocumentosCategoria
            WHERE
                V.VerbosNombre IS NOT NULL OR S.SustantivosNombre IS NOT NULL
            GROUP BY
                D.idDocumentos, D.DocumentosTitulo, D.DocumentosRutaGuardado, D.DocumentosResumen, DC.DocumentosCategoriaNombre
            ORDER BY
                TotalFrecuencia DESC
            LIMIT 5;";
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=]/',"",$sql);
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
    }    
}

function correccion_tildes($text) {
    // Mapeo de caracteres con tilde a sus versiones sin tilde
    $replace_map = array(
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
    );
    
    // Reemplazar los caracteres con tilde por sus versiones sin tilde
    $updated_text = strtr($text, $replace_map);
    
    return $updated_text;
}

?>