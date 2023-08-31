<?php
include 'utiles/funcionesutiles.php';
include 'utiles/validarsesionadmin.php';    
//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    // Establece la zona horaria a Santiago y limita el tiempo de ejecución a 1200 segundos (20 minutos)
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);

    // Inicializa un arreglo para almacenar las respuestas
    $response = array();

    // Verificar si se ha proporcionado la categoría en la solicitud GET
    if (isset($_GET['categoria'])) {
        $categoria = $_GET['categoria'];
        $categoriaID = null;
        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL 
            $sql = "SELECT idDocumentosCategoria FROM DocumentosCategoria WHERE DocumentosCategoriaNombre = '$categoria'";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    // Encontrado, procesa los resultados
                    while ($row = $result->fetch_assoc()) {
                        // Accede a los valores en $row
                        $categoriaID = $row["idDocumentosCategoria"];
                    }
                } else {
                    // No se encontraron resultados
                    // Manejar error en la consulta
                    $error_message = $conn->error; // Mensaje de error generado
                    $conn->close(); 
                    throw new Exception($error_message);
                }
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
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

        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL
            $sql = "SELECT PreguntasPreguntaFrecuente, PreguntasRespuesta FROM Preguntas WHERE Preguntas.DocumentosCategoria_idDocumentosCategoria = $categoriaID;";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    // Encontrado, procesa los resultados
                    while ($row = $result->fetch_assoc()) {
                        // Accede a los valores en $row
                        $documentos_modificados[] = array(
                            "PreguntaFrecuente" => $row['PreguntasPreguntaFrecuente'],
                            "Respuesta" => $row['PreguntasRespuesta'],
                        );
                    }
                    // Construir el array final de resultados
                    $resultados = array("Resultados" => $documentos_modificados);
                    header("HTTP/1.1 200 OK");
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
                } else {
                    // No se encontraron resultados
                    // Manejar error en la consulta
                    $error_message = $conn->error; // Mensaje de error generado
                    $conn->close(); 
                    throw new Exception($error_message);
                }
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close(); 
                throw new Exception($error_message);
            } 
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {
            $respuesta = "Hubo un problema con la búsqueda de preguntas en el sistema.";
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
        $respuesta = "Categoría no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }      
}; 

//codigo inicial del metodo post 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    // Establece la zona horaria a Santiago y limita el tiempo de ejecución a 1200 segundos (20 minutos)
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);

    // Inicializa un arreglo para almacenar las respuestas
    $response = array();
    
    // Obtén los datos del formulario
    $rut = $_POST['rut'];
    $pass = $_POST['password'];
    $categoria = $_POST['categoria'];
    $preguntaFrec = $_POST['pregunta'];
    $respuestaFrec = $_POST['respuesta'];

    // Elimina caracteres no válidos
    $rut = preg_replace('/[^kK0-9]/', '', $rut);
    $preguntaFrec = preg_replace('/[^0-9A-Za-z\s.:,_\-?¿¡!ÁáÉéÍíÓóÚúüÑñ$%º]/', '', $preguntaFrec);
    $respuestaFrec = preg_replace('/[^0-9A-Za-z\s.:,_\-?¿¡!ÁáÉéÍíÓóÚúüÑñ$%º]/', '', $respuestaFrec);
    // Validar si el RUT no están vacío
    if (empty($rut)) {
        $respuesta = "Debe completar con su Rut, esta sección es solo para Funcionarios.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }  // Validar si la contraseña no está vacía
    elseif (empty($pass)) {
        $respuesta = "Debe completar con su contraseña, esta sección es solo para Funcionarios.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } // Realizar otras validaciones específicas, como formato de RUT válido
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
    // Verificar si se ha proporcionado la categoría en la solicitud
    elseif (empty($categoria)) {
        $respuesta = "Categoría no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } 
    // Verificar si se ha proporcionado la categoría en la solicitud
    elseif (empty($preguntaFrec)) {
        $respuesta = "Pregunta no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Verificar si se ha proporcionado la categoría en la solicitud
    elseif (empty($respuestaFrec)) {
        $respuesta = "Respuesta no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    elseif (strlen($preguntaFrec) > 180) {
        $respuesta = "La pregunta excede los 180 caracteres permitidos.";
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    elseif (strlen($respuestaFrec) > 180) {
        $respuesta = "La respuesta excede los 180 caracteres permitidos.";
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        $categoriaID = null;
        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL 
            $sql = "SELECT idDocumentosCategoria FROM DocumentosCategoria WHERE DocumentosCategoriaNombre = '$categoria'";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    // Encontrado, procesa los resultados
                    while ($row = $result->fetch_assoc()) {
                        // Accede a los valores en $row
                        $categoriaID = $row["idDocumentosCategoria"];
                    }
                } else {
                    // No se encontraron resultados
                    // Manejar error en la consulta
                    $error_message = $conn->error; // Mensaje de error generado
                    $conn->close(); 
                    throw new Exception($error_message);
                }
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close(); 
                throw new Exception($error_message);
            } 
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {
            $respuesta = "Pruebe con una de las categorias definidas.";
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
            // Consulta SQL
            $sql = "INSERT INTO Preguntas(idPreguntasFrecuentes, PreguntasPreguntaFrecuente, PreguntasRespuesta, DocumentosCategoria_idDocumentosCategoria) VALUES (NULL,'$preguntaFrec','$respuestaFrec',$categoriaID)";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                // Consulta exitosa
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close(); 
                throw new Exception($error_message);
            } 
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {
            $respuesta = "Hubo un problema con la creacion de la pregunta y respuesta en el sistema.";
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
};  

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // Establece la zona horaria a Santiago y limita el tiempo de ejecución a 1200 segundos (20 minutos)
    date_default_timezone_set('America/Santiago');
    set_time_limit(1200);

    // Inicializa un arreglo para almacenar las respuestas
    $response = array();

    // Obtén los datos en formato multipart/form-data
    $putData = file_get_contents("php://input");

    // Define una función para analizar los datos multipart
    function multipart_parse($input)
    {
        $data = [];
        $matches = [];
        
        // Separa los bloques por la cadena delimitadora
        preg_match_all('/-{28}[0-9]+(.*?)--{28}/s', $input, $matches);
        
        foreach ($matches[1] as $match) {
            $parts = explode("\r\n\r\n", trim($match), 2);
            if (count($parts) === 2) {
                // Extrae el nombre de campo y el valor del contenido
                preg_match('/name="(.*?)"/', $parts[0], $nameMatches);
                $fieldName = $nameMatches[1];
                $data[$fieldName] = trim($parts[1]);
            }
        }
        
        return $data;
    }

    // Analiza los datos multipart
    $multipartData = multipart_parse($putData);
    echo $multipartData;
    echo $multipartData['rut'];  // Accede al valor del campo "rut"
    echo $multipartData['password'];  // Accede al valor del campo "password"
    // Accede a los valores de los campos
    $rut = $multipartData['rut'];
    $password = $multipartData['password'];
    $preguntaFrec = $multipartData['pregunta'];
    $respuestaFrec = $multipartData['respuesta'];
    $idPregunta = $multipartData['idpregunta'];
    
    // Elimina caracteres no válidos
    $rut = preg_replace('/[^kK0-9]/', '', $rut);
    $preguntaFrec = preg_replace('/[^0-9A-Za-z\s.:,_\-?¿¡!ÁáÉéÍíÓóÚúüÑñ$%º]/', '', $preguntaFrec);
    $respuestaFrec = preg_replace('/[^0-9A-Za-z\s.:,_\-?¿¡!ÁáÉéÍíÓóÚúüÑñ$%º]/', '', $respuestaFrec);
    // Validar si el RUT no están vacío
    if (empty($rut)) {
        $respuesta = "Debe completar con su Rut, esta sección es solo para Funcionarios.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }  // Validar si la contraseña no está vacía
    elseif (empty($pass)) {
        $respuesta = "Debe completar con su contraseña, esta sección es solo para Funcionarios.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    } // Realizar otras validaciones específicas, como formato de RUT válido
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
    // Verificar si se ha proporcionado la pregunta en la solicitud
    elseif (empty($preguntaFrec)) {
        $respuesta = "Pregunta no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Verificar si se ha proporcionado la respuesta en la solicitud
    elseif (empty($respuestaFrec)) {
        $respuesta = "Respuesta no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Verificar pregunta si excede los 180 caracteres permitidos.
    elseif (strlen($preguntaFrec) > 180) {
        $respuesta = "La pregunta excede los 180 caracteres permitidos.";
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Verificar respuesta si excede los 180 caracteres permitidos.
    elseif (strlen($respuestaFrec) > 180) {
        $respuesta = "La respuesta excede los 180 caracteres permitidos.";
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL 
            $sql = "UPDATE Preguntas SET PreguntasPreguntaFrecuente='$preguntaFrec',PreguntasRespuesta='$respuestaFrec' WHERE idPreguntasFrecuentes = $preguntaID";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                // Consulta exitosa
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close(); 
                throw new Exception($error_message);
            } 
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {
            $respuesta = "Pruebe con una de las categorias definidas.";
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
            // Consulta SQL
            $sql = "INSERT INTO Preguntas(idPreguntasFrecuentes, PreguntasPreguntaFrecuente, PreguntasRespuesta, DocumentosCategoria_idDocumentosCategoria) VALUES (NULL,'$preguntaFrec','$respuestaFrec',$categoriaID)";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),\'\":._\-$+=\*]/',"",$sql);
            // Ejecutar la consulta                 
            if ($result = $conn->query($sql)) {
                // Consulta exitosa
            } else {
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close(); 
                throw new Exception($error_message);
            } 
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {
            $respuesta = "Hubo un problema con la creacion de la pregunta y respuesta en el sistema.";
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
?>
