<?php
date_default_timezone_set('America/Santiago');

//codigo del metodo post para recibir pdfs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $rut = $_POST['UsuarioRut'];
    $UsuarioContrasena = $_POST['UsuarioContrasena'];

    // Eliminar caracteres no válidos
    $rut = preg_replace('/[^kK0-9]/', '', $rut); 

    // Validar si el RUT y la contraseña no están vacíos
    if (empty($rut)) {
        $respuesta = "Debe completar con su Rut.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } 
    elseif (empty($UsuarioContrasena)) {
        $respuesta = "Debe completar con su contraseña.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        // Realizar otras validaciones específicas, como formato de RUT válido
        // Verificación de contraseña 
        if (!formatoRUTValido($rut)) {
            $respuesta = "Rut en formato incorrecto, revise el dígito verificador.";
            header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
            header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        } 
        elseif (!validarContrasena($rut, $UsuarioContrasena)) {
            $respuesta = "Contraseña incorrecta.";
            header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
            header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        }         
    }    
    if (!empty($_FILES['archivo'])) {
        if ($_FILES['archivo']['type'] == 'application/pdf') {    
            $ruta_destino = "archivos/";
            $namefinal = trim ($_FILES['archivo']['name']);  
            $namefinal = preg_replace('([^[A-Z][a-z]*\s*.+])', '', $namefinal);
            $namefinal = preg_replace('/\s+/', '_', $namefinal);
            $nombreDocumento = $namefinal;
            $ruta_archivo = $ruta_destino . $namefinal; 
            if(is_uploaded_file($_FILES['archivo']['tmp_name'])) {                    
                if(move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo)) {      
                    //guardar en la variable txt el nombre del archivo, pero cambiando la extensión 
                    $ruta_txt= preg_replace("/pdf/", 'txt', $namefinal);                    
                    //creación de rutas y nombres 
                    $ruta_txt = $ruta_destino . $ruta_txt;            
                    $ruta_pdf = $ruta_archivo;
                    
                    //intentar ejecutar la aplicación pdftotext 
                    try {
                        shell_exec ("cd /var/www/html/apirestClAtiende/archivos && pdftotext $ruta_pdf $ruta_txt");
                    } 
                    catch (\Throwable $th) {
                        $respuesta = "No se logró hacer la transformación de pdf a texto.";
                        $error = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe la excepcion junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $error" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                        exit;
                    }

                    // Inicio creacion automatica de etiqueta
                    /* $etiquetar = "paquetes/etiquetar.py";
                    $resultados = null;
                    try {
                        //ejecucion extraccion NLP
                        exec("python3.10 $etiquetar $ruta_txt", $salida);
                        $jsonOutput = implode("", $salida);
                        $data = json_decode($jsonOutput, true);
                        
                        // Obtener los resultados
                        $resultados = $data["resultados"];                                                                        
                    }
                    catch (\Throwable $th) {
                        $respuesta = "No se logró hacer la extracción NLP.";
                        $error = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe la excepcion junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $error" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                        exit;
                    } */
                    

                    // Inicio creacion del documento en DB
                    try {
                        //code...
                    } catch (\Throwable $th) {
                        //throw $th;
                    }


                    // Inicio extraccion palabras clave y guardado en DB
                    $extraer = "cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py";
                    $resultados = null;
                    try {
                        //ejecucion extraccion NLP
                        shell_exec("$extraer $ruta_txt", $salida);
                        $jsonOutput = implode("", $salida);
                        $data = json_decode($jsonOutput, true);
                        
                        // Obtener los resultados
                        $resultados = $data["resultados"];                                                                        
                    }
                    catch (\Throwable $th) {
                        $respuesta = "No se logró hacer la extracción NLP.";
                        $error = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe la excepcion junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $error" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    try {
                        /* // Inicializar los contadores
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
                        $conn->close(); */

                    } 
                    catch (\Throwable $th) {
                        $respuesta = "Hubo un problema al ingresar las palabras clave de la extraccion en la base de datos.";
                        $error = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe la excepcion junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $error" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    

                }
                else {
                    $mensaje = "El archivo internamente no se logró mover al directorio.";
                    $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                    $nombreArchivo = "logs_de_error.txt";                    
                    // Abre o crea el archivo de log en modo de escritura al final del archivo
                    $nombreArchivo = fopen($rutaLog, "a");
                    // Escribe el mensaje junto con la fecha y hora en el archivo de log
                    fwrite($nombreArchivo, "[$fechaHora] $mensaje" . PHP_EOL);
                    // Cierra el archivo de log
                    fclose($nombreArchivo);
                }     
            }
            else {
                $respuesta = 'El servidor no pudo efectuar la subida de archivo.';
                $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                $nombreArchivo = "logs_de_error.txt";                    
                // Abre o crea el archivo de log en modo de escritura al final del archivo
                $nombreArchivo = fopen($rutaLog, "a");
                // Escribe la excepcion junto con la fecha y hora en el archivo de log
                fwrite($nombreArchivo, "[$fechaHora] $respuesta" . PHP_EOL);
                // Cierra el archivo de log
                fclose($nombreArchivo);
                header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
                exit;
            }                               
        }
        else{
            $respuesta = 'El archivo adjunto no es un documento PDF.';
            $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
            $nombreArchivo = "logs_de_error.txt";                    
            // Abre o crea el archivo de log en modo de escritura al final del archivo
            $nombreArchivo = fopen($rutaLog, "a");
            // Escribe la excepcion junto con la fecha y hora en el archivo de log
            fwrite($nombreArchivo, "[$fechaHora] $respuesta" . PHP_EOL);
            // Cierra el archivo de log
            fclose($nombreArchivo);
            header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
            header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    else{
        $respuesta = 'No se encuentra adjunto un documento PDF.';
        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
        $nombreArchivo = "logs_de_error.txt";                    
        // Abre o crea el archivo de log en modo de escritura al final del archivo
        $nombreArchivo = fopen($rutaLog, "a");
        // Escribe la excepcion junto con la fecha y hora en el archivo de log
        fwrite($nombreArchivo, "[$fechaHora] $respuesta" . PHP_EOL);
        // Cierra el archivo de log
        fclose($nombreArchivo);
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

function validarContrasena($rut,$pass) {
    $UsusarioRut = $rut;
    $UsuarioContrasena = $pass;

    // Declarar la variable $Contrasena
    $Contrasena = null;
    
    // funcion para transformar la contraseña a hash
    // $passwordhash = password_hash($UsuarioContrasena, PASSWORD_DEFAULT);
    include 'conexiondb.php';

    try {
        // Consulta SQL con cláusula WHERE
        $sql = "SELECT UsuarioContrasena FROM Usuario WHERE UsuarioRut = $UsusarioRut";
        $result = $conn->query($sql);
    } 
    catch (\Throwable $th) {
        $respuesta = "No se logró hacer la consulta a la base de datos para validar contraseña.";
        $error = $th->getMessage();
        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
        $nombreArchivo = "logs_de_error.txt";                    
        // Abre o crea el archivo de log en modo de escritura al final del archivo
        $nombreArchivo = fopen($rutaLog, "a");
        // Escribe la excepcion junto con la fecha y hora en el archivo de log
        fwrite($nombreArchivo, "[$fechaHora] $error" . PHP_EOL);
        // Cierra el archivo de log
        fclose($nombreArchivo);
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }        

    if ($result->num_rows > 0) {
        // Encontrado, procesa los resultados
        while ($row = $result->fetch_assoc()) {
            // Accede a los valores en $row
            $Contrasena = $row["UsuarioContrasena"];
        }
    } 

    // Cerrar la conexión
    $conn->close(); 

    //Verificar la contraseña hash con la almacenada 
    if (password_verify($UsuarioContrasena, $Contrasena)) {
        return true;
    } 
    else {
        return false;
    }
}

?>