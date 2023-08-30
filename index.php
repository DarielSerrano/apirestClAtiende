<?php
include 'sesiones/validarsesionadmin.php';

//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de analisis, busqueda y preguntas frecuentes";
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

//codigo del metodo post para recibir pdfs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);
    $response = array();    
    // Obtener los datos del formulario
    $rut = $_POST['rut'];
    $pass = $_POST['password'];
    // Eliminar caracteres no válidos
    $rut = preg_replace('/[^kK0-9]/', '', $rut); 
    // Validar si el RUT y la contraseña no están vacíos
    if (empty($rut)) {
        $respuesta = "Debe completar con su Rut.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    } 
    elseif (empty($pass)) {
        $respuesta = "Debe completar con su contraseña.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }// Realizar otras validaciones específicas, como formato de RUT válido
    elseif (!formatoRUTValido($rut)) {
        $respuesta = "Rut en formato incorrecto, revise el dígito verificador.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    } // Verificación de contraseña 
    elseif (!validarContrasena($rut, $pass)) {
        $respuesta = "Contraseña incorrecta.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }           
    elseif (empty($_FILES['archivo'])) {
        $respuesta = "El archivo no se adjunto.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);                                 
    }
    elseif ($_FILES['archivo']['type'] == 'application/pdf') {    
        $ruta_destino = "archivos/";
        $namefinal = trim ($_FILES['archivo']['name']); 
        $namefinal = preg_replace('/\s/', '_', $namefinal);
        $namefinal = correccion_tildes($namefinal);
        $namefinal = preg_replace('/[^A-Za-z\s.:-_]/', '', $namefinal);        
        $nombreDocumento = $namefinal;
        $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual 
        $namefinal = $fechaHora."_".$namefinal;
        $ruta_archivo = $ruta_destino . $namefinal; 
        if(is_uploaded_file($_FILES['archivo']['tmp_name'])) {                    
            if(move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo)) {      
                //guardar en la variable txt el nombre del archivo, pero cambiando la extensión 
                $ruta_txt= preg_replace("/pdf/", 'txt', $namefinal);                    
                //creación de rutas, nombres y variables
                $ruta_txt = $ruta_destino . $ruta_txt;            
                $ruta_pdf = $ruta_archivo;                
                //intentar ejecutar la aplicación pdftotext 
                try {
                    $output = array();
                    $return_var = 0;
                    $errcapture = "2>&1";
                    // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
                    exec("cd /var/www/html/apirestClAtiende && pdftotext $ruta_pdf $ruta_txt $errcapture", $output, $return_var);
                    // Verificar el estado de retorno para determinar si hubo un error
                    if ($return_var === 0) {
                        /* // El comando se ejecutó correctamente*/
                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al hacer la transformación de pdf a texto.";
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
                $dbetiqueta = null;       
                // Inicio creacion automatica de etiqueta y guardado en BD
                try {
                    $output = array();
                    $return_var = 0;
                    $errcapture = "2>&1";
                    $etiquetar = "cd /var/www/html/apirestClAtiende && python3.10 paquetes/etiquetar.py";
                    // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
                    exec("$etiquetar $ruta_txt $errcapture", $output, $return_var);                    
                    // Verificar el estado de retorno para determinar si hubo un error
                    if ($return_var === 0) {
                        // Filtrar y extraer los objetos JSON de la salida
                        foreach ($output as $line) {
                            if (preg_match('/^\{.*\}$/', $line)) {
                                $dbetiqueta = json_decode($line, true);
                                $dbetiqueta = $dbetiqueta['etiqueta'];
                            }
                        }                        
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($dbetiqueta, JSON_UNESCAPED_UNICODE);                          
                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al generar el etiquetado.";
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
                $dbextraer = null;
                $pal = null;
                $cla = null;            
                // Inicio extraccion palabras clave NLP y guardado en BD
                try {
                    $output = array();
                    $return_var = 0;
                    $errcapture = "2>&1";
                    $extraer = "cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py";
                    // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
                    exec("$extraer $ruta_txt $errcapture", $output, $return_var);
                    
                    // Verificar el estado de retorno para determinar si hubo un error
                    if ($return_var === 0) {
                        // Filtrar y extraer los objetos JSON de la salida
                        foreach ($output as $line) {
                            if (preg_match('/^\{.*\}$/', $line)) {
                                $dbextraer = json_decode($line, true);
                                $dbextraer = $dbextraer['resultados'];
                                $pal = $dbextraer['palabra'];
                                $cla = $dbextraer['clasificacion'];
                            }
                        }
                        // Puedes usar $dbextraer según tus necesidades
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($pal, JSON_UNESCAPED_UNICODE);
                        echo json_encode($cla, JSON_UNESCAPED_UNICODE);

                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al hacer la extracción NLP.";
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
                /* try { // guardado en BD
                    // Inicializar los contadores
                    $frecuenciaVerbos = [];
                    $frecuenciaSustantivos = [];

                    // Iterar sobre los resultados y contar las repeticiones
                    foreach ($resultados as $resultado) {
                        $palabra = $resultado["word"];
                        $entidad = $resultado["entity"];
                        
                        if ($entidad === "VERB") {
                            if (!array_key_exists($palabra, $frecuenciaVerbos)) {
                                $frecuenciaVerbos[$palabra] = 1;
                            } else {
                                $frecuenciaVerbos[$palabra]++;
                            }
                        } elseif ($entidad === "NOUN" || $entidad === "PROPN") {
                            if (!array_key_exists($palabra, $frecuenciaSustantivos)) {
                                $frecuenciaSustantivos[$palabra] = 1;
                            } else {
                                $frecuenciaSustantivos[$palabra]++;
                            }
                        }
                    }

                    // Ordenar verbos por frecuencia de mayor a menor
                    arsort($frecuenciaVerbos);

                    // Tomar los primeros 20 verbos y sus frecuencias
                    $top20Verbos = array_slice($frecuenciaVerbos, 0, 20, true);

                    // Ordenar sustantivos por frecuencia de mayor a menor
                    arsort($frecuenciaSustantivos);

                    // Tomar los primeros 30 sustantivos y sus frecuencias
                    $top30Sustantivos = array_slice($frecuenciaSustantivos, 0, 30, true);

                    include 'conexiondb.php';

                    // Insertar frecuencias de verbos
                    foreach ($top20Verbos as $verbo => $frecuencia) {
                        $sql = "INSERT INTO Verbos (VerbosNombre, VerbosFrecuencia) VALUES ('$verbo', $frecuencia)";
                        $conn->query($sql);
                    }

                    // Insertar frecuencias de sustantivos (PROPN y NOUN juntos)
                    foreach ($top30Sustantivos as $sustantivo => $frecuencia) {
                        $sql = "INSERT INTO Sustantivos (SustantivosNombre, SustantivosFrecuencia) VALUES ('$sustantivo', $frecuencia)";
                        $conn->query($sql);
                    }

                    // Cerrar la conexión
                    $conn->close();

                } 
                catch (\Throwable $th) {
                    $respuesta = "Hubo un problema al guardar la extraccion.";
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
                } */
                
                $dbresumen = null;
                // Inicio creacion del resumen de documento NLP y guardado en BD
                try {
                    $output = array();
                    $return_var = 0;
                    $errcapture = "2>&1";
                    $resumir = "cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache python3.10 paquetes/resumir.py";
                    // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
                    exec("$resumir $ruta_txt $errcapture", $output, $return_var);
                    
                    // Verificar el estado de retorno para determinar si hubo un error
                    if ($return_var === 0) {
                        // Filtrar y extraer los objetos JSON de la salida
                        foreach ($output as $line) {
                            if (preg_match('/^\{.*\}$/', $line)) {
                                $dbresumen = json_decode($line, true);
                                $dbresumen = $dbresumen['resumen'];
                            }
                        }

                        // Puedes usar $dbresumen según tus necesidades
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($dbresumen, JSON_UNESCAPED_UNICODE);                          
                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al generar el resumen.";
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
                $dbidetiqueta = null;                          
                /* try { // Inicio busqueda de id por nombre etiqueta extraida de documento
                    include 'conexiondb.php';                                    
                    // Consulta SQL con cláusula WHERE
                    $sql = "SELECT idDocumentosCategoria FROM DocumentosCategoria WHERE DocumentosCategoriaNombre = '$dbetiqueta'";                    
                    // Ejecutar la consulta
                    $result = $conn->query($sql);                    
                    if ($result) {
                        if ($result->num_rows > 0) {
                            // Encontrado, procesa los resultados
                            while ($row = $result->fetch_assoc()) {
                                // Accede a los valores en $row
                                $dbidetiqueta = $row["idDocumentosCategoria"];
                            }
                        } else {
                            // No se encontraron resultados
                        }
                    } else {
                        // Manejar error en la consulta
                        $error_message = $conn->error; // Mensaje de error generado
                        $conn->close(); 
                        throw new Exception($error_message);
                    } 
                    // Cerrar la conexión
                    $conn->close();                                                                                                  
                } 
                catch (\Throwable $th) {
                    $respuesta = "Hubo un problema al guardar la etiqueta.";
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
                try { // Creacion Documento en db
                    include 'conexiondb.php';
                    $nombreDocumento;
                    $dbidetiqueta;                    
                    // Consulta SQL con cláusula WHERE
                    $sql = "INSERT INTO 'Documentos'('idDocumentos', 'DocumentosTitulo', 'DocumentosRutaGuardado', 'DocumentosResumen', 'DocumentosCategoria_idDocumentosCategoria') VALUES (NULL,$nombreDocumento,[value-3],[value-4],$dbidetiqueta)";
                    
                    // Ejecutar la consulta
                    $result = $conn->query($sql);
                    
                    if ($result) {
                        if ($result->num_rows > 0) {
                            // Encontrado, procesa los resultados
                            while ($row = $result->fetch_assoc()) {
                                // Accede a los valores en $row
                                $a = $row["idDocumentosCategoria"];
                            }
                        } else {
                            // No se encontraron resultados
                        }
                    } else {
                        // Manejar error en la consulta
                        $error_message = $conn->error; // Mensaje de error generado
                        $conn->close(); 
                        throw new Exception($error_message);
                    } 
                    // Cerrar la conexión
                    $conn->close();                                                                                                  
                } 
                catch (\Throwable $th) {
                    $respuesta = "Hubo un problema al guardar la etiqueta.";
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
                } */                                           
            }
            else {
                $respuesta = "El archivo internamente no se logró mover al directorio.";
                header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                exit;
            }     
        }
        else {
            $respuesta = 'El servidor no pudo efectuar la subida de archivo.';
            header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
            header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        }                               
    }
    else{
        $respuesta = 'El archivo adjunto no es un documento PDF.';
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
}


function formatoRUTValido($rut) {
    // Eliminar caracteres no válidos
    $rut = preg_replace('/[^kK0-9]/', '', $rut); 
    if (empty($rut)) {
    return false;
    }

    $rutNumeros = substr($rut, 0, strlen($rut)-1);
    $dv = strtoupper(substr($rut, -1));
    // Validar si el dígito verificador es K o número
    if ($dv != 'K' && !is_numeric($dv)) {
    return false;
    }

    // Cálculo del dígito verificador
    $i = 2;
    $suma = 0;
    foreach(array_reverse(str_split($rutNumeros)) as $v) {
    if($i==8){
        $i = 2;
    }            
    $suma += $v * $i;
    ++$i;
    }
    $digitoVerificadorCalculado = 11 - ($suma % 11);

    // Comparar dígito verificador calculado con el ingresado (considerando K como 10)
    if ($digitoVerificadorCalculado == 11 && ($dv == '0')) {
        return true;
    } 
    elseif ($digitoVerificadorCalculado == 10 && $dv == 'K') {
        return true;
    } 
    elseif ($digitoVerificadorCalculado == intval($dv)) {
        return true;
    } 
    else {
        return false;
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