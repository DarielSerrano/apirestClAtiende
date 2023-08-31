<?php
include 'sesiones/validarsesionadmin.php';
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
        $respuesta = "Debe completar con su Rut, esta sección es solo para Funcionarios .";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } 
    elseif (empty($pass)) {
        $respuesta = "Debe completar con su contraseña, esta sección es solo para Funcionarios .";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }// Realizar otras validaciones específicas, como formato de RUT válido
    elseif (!formatoRUTValido($rut)) {
        $respuesta = "Rut en formato incorrecto, revise el dígito verificador.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } // Verificación de contraseña 
    elseif (!validarContrasena($rut, $pass)) {
        $respuesta = "Contraseña incorrecta.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }           
    elseif (empty($_FILES['archivo'])) {
        $respuesta = "El archivo no se adjunto.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE); 
        exit;                                
    }
    elseif ($_FILES['archivo']['type'] == 'application/pdf') {    
        $ruta_destino = "archivos/";
        $namefinal = trim ($_FILES['archivo']['name']); 
        $namefinal = correccion_tildes($namefinal);
        $namefinal = preg_replace('/\s/', '_', $namefinal);
        $namefinal = preg_replace('/[^A-Za-z\s.:-_]/', '', $namefinal);        
        $tituloDocumento = $namefinal;
        $fechaHora = preg_replace('/\s/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual         
        $namefinal = $fechaHora."_".$namefinal;
        $rutaDBpdf = $namefinal; 
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
                        // Lee el contenido del archivo
                        $contenido = file_get_contents($ruta_txt);

                        // Aplicar preg_replace para mantener solo los caracteres deseados
                        $contenido_filtrado = preg_replace('/[^A-Za-z\s,.\-_():ÁáÉéÍíÓóÚúüÑñ$%º]/u', '', $contenido);

                        // Escribir el contenido filtrado de vuelta al archivo
                        file_put_contents($ruta_txt, $contenido_filtrado);
                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al hacer la transformación de pdf a texto o su pdf no es elegible para ser convertido a texto.";
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
                // Inicio creacion automatica de etiqueta 
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
                        $respuesta = "Etiquetado creado con éxito.";
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);                         
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
                $top_30_verbos = [];
                $top_30_sustantivos = [];              
                // Inicio extraccion palabras clave NLP 
                try {
                    $output = array();
                    $return_var = 0;
                    $errcapture = "2>&1";
                    $extraer = "cd /var/www/html/apirestClAtiende && STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py";
                    // Ejecutar el comando y capturar la salida en $output y el estado de retorno en $return_var
                    exec("$extraer $ruta_txt $errcapture", $output, $return_var);        
                    // Verificar el estado de retorno para determinar si hubo un error
                    if ($return_var === 0) {
                        // Filtrar y extraer los objetos JSON de la salida
                        foreach ($output as $line) {
                            if (preg_match('/^\{.*\}$/', $line)) {
                                $dbextraer = json_decode($line, true);
                                $dbextraer = $dbextraer['resultados'];
                            }
                        }  
                    $sustantivo = array(); // Almacenar sustantivos
                    $verb = array(); // Almacenar verbos
                    $palabra_contador = array();                   
                    foreach ($dbextraer as $resultado) {
                        $palabra = $resultado['palabra'];
                        $clasificacion = $resultado['clasificacion'];
                        // Incrementar el contador de la palabra
                        if (isset($palabra_contador[$palabra])) {
                            $palabra_contador[$palabra]++;
                        } else {
                            $palabra_contador[$palabra] = 1;
                        }
                        // Separar en verbos y sustantivos
                        if ($clasificacion == 'NOUN' || $clasificacion == 'PROPN') {
                            $sustantivo[] = $palabra;
                        } elseif ($clasificacion == 'VERB') {
                            $verb[] = $palabra;
                        }
                    }

                    // Ordenar arreglo de palabra_contador por frecuencia de mayor a menor
                    arsort($palabra_contador);

                    // Filtrar y guardar los 30 verbos de mayor a menor frecuencia                    
                    $count_verbos = 0;
                    foreach ($palabra_contador as $palabra => $frecuencia) {
                        $clasificacion = in_array($palabra, $verb) ? 'VERB' : 'SUST';
                        if ($clasificacion == 'VERB' && $count_verbos < 30) {
                            $top_30_verbos[] = ["palabra" => $palabra, "frecuencia" => $frecuencia];
                            $count_verbos++;
                        }
                    }

                    // Filtrar y guardar los 30 sustantivos de mayor a menor frecuencia                    
                    $count_sustantivos = 0;
                    foreach ($palabra_contador as $palabra => $frecuencia) {
                        $clasificacion = in_array($palabra, $verb) ? 'VERB' : 'SUST';
                        if ($clasificacion == 'SUST' && $count_sustantivos < 30) {
                            $top_30_sustantivos[] = ["palabra" => $palabra, "frecuencia" => $frecuencia];
                            $count_sustantivos++;
                        }
                    }  
                        $respuesta = "Extracción creada con éxito.";
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);                       
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
                $dbResumen = null;
                // Inicio creacion del resumen de documento NLP 
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
                                $dbResumen = json_decode($line, true);
                                $dbResumen = $dbResumen['resumen'];
                            }
                        }

                        $respuesta = "Resumen creado con éxito.";
                        header("HTTP/1.1 200 OK");
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);                          
                    } else {
                        // Hubo un error al ejecutar el comando
                        $error_message = implode("\n", $output); // Los mensajes de error generados
                        throw new Exception($error_message);
                    }
                } 
                catch (Exception $th) {
                    $respuesta = "Hubo un problema al generar el resumen NLP.";
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
                $dbIDetiqueta = null;                          
                try { // Inicio busqueda de ids para creacion documento
                    include 'conexiondb.php';                                                        
                    // Consulta SQL con cláusula WHERE
                    $sql = "SELECT idDocumentosCategoria FROM DocumentosCategoria WHERE DocumentosCategoriaNombre = $dbetiqueta";                    
                    // Limpieza ante posibles inyecciones
                    $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+]/',"",$sql);
                    // Ejecutar la consulta                 
                    if ($result = $conn->query($sql)) {
                        if ($result->num_rows > 0) {
                            // Encontrado, procesa los resultados
                            while ($row = $result->fetch_assoc()) {
                                // Accede a los valores en $row
                                $dbIDetiqueta = $row["idDocumentosCategoria"];
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
                    $respuesta = "Hubo un problema al guardar la etiqueta en el sistema.";
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
                    // Consulta SQL con cláusula WHERE
                    $sql = "INSERT INTO Documentos (idDocumentos, DocumentosTitulo, DocumentosRutaGuardado, DocumentosResumen, DocumentosCategoria_idDocumentosCategoria) VALUES (NULL,'$tituloDocumento','$rutaDBpdf','$dbResumen',$dbIDetiqueta)";
                    // Limpieza ante posibles inyecciones
                    $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+]/',"",$sql);
                    // Ejecutar la consulta
                    if ($conn->query($sql)) {
                        $dbIDdocumento = $conn->insert_id; // Obtener el último ID insertado                        
                        if ($dbIDdocumento > 0) {
                            // La inserción fue exitosa
                        } else {
                            // Manejar error en la consulta de inserción
                            $error_message = $conn->error; // Mensaje de error generado
                            // Cerrar la conexión
                            $conn->close();
                            throw new Exception($error_message);
                        }
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
                } 
                catch (\Throwable $th) {
                    $respuesta = "Hubo un problema al crear el documento en el sistema.";
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
                try { // guardado verbos y sustantivos deldocumento en BD
                    include 'conexiondb.php';
                    $dbIDetiqueta;
                    $dbIDdocumento;
                    // Insertar frecuencias de verbos
                    foreach ($top_30_verbos as $frecuencia) {
                        $verbo = $frecuencia['palabra'];
                        $frecuenciaValor = $frecuencia['frecuencia'];                                                
                        $sql = "INSERT INTO Verbos(idVerbos, VerbosNombre, VerbosFrecuencia, Documentos_idDocumentos, Documentos_DocumentosCategoria_idDocumentosCategoria) VALUES (NULL,'$verbo',$frecuenciaValor,$dbIDdocumento,$dbIDetiqueta)";
                        // Limpieza ante posibles inyecciones
                        $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+]/',"",$sql);
                        if($conn->query($sql)){
                            // La consulta fue exitosa
                        }
                        else {
                            // Manejar error en la consulta de inserción
                            $error_message = $conn->error; // Mensaje de error generado
                            // Cerrar la conexión
                            $conn->close();
                            throw new Exception($error_message);
                        }
                    }
                    // Insertar frecuencias de sustantivos
                    foreach ($top_30_sustantivos as $frecuencia) {
                        $sustantivo = $frecuencia['palabra'];
                        $frecuenciaValor = $frecuencia['frecuencia'];                                
                        $sql = "INSERT INTO Sustantivos(idSustantivos, SustantivosNombre, SustantivosFrecuencia, Documentos_idDocumentos, Documentos_DocumentosCategoria_idDocumentosCategoria) VALUES (NULL,'$sustantivo',$frecuenciaValor,$dbIDdocumento,$dbIDetiqueta)";
                        // Limpieza ante posibles inyecciones
                        $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+]/',"",$sql);
                        if ($conn->query($sql)){
                            // La consulta fue exitosa
                        }
                        else {
                            // Manejar error en la consulta de inserción
                            $error_message = $conn->error; // Mensaje de error generado
                            // Cerrar la conexión
                            $conn->close();
                            throw new Exception($error_message);
                        } 
                    }
                    // Cerrar la conexión
                    $conn->close();
                } 
                catch (\Throwable $th) {
                    $respuesta = "Hubo un problema al guardar verbos y sustantivos en el sistema.";
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