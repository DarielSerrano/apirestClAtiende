<?php

include 'utiles/funcionesutiles.php';
include 'utiles/validarsesionadmin.php';  
//código inicial del método post
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

    // Elimina caracteres no válidos del RUT
    $rut = preg_replace('/[^kK0-9]/u', '', $rut);

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
    // Verificar si se ha proporcionado la categoría en la solicitud POST
    elseif (!isset($_POST['categoria'])) {
        $respuesta = "Categoría no especificada.";
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
    else {
        $categoria = $_POST['categoria'];
        $categoriaID = null;
        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL
            $sql = "SELECT idDocumentosCategoria FROM DocumentosCategoria WHERE DocumentosCategoriaNombre = '$categoria'";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),?¿¡!\'\":._\-$+=\*%º]/u',"",$sql);
            // Ejecutar la consulta                
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    // Encontrado, procesa los resultados
                    while ($row = $result->fetch_assoc()) {
                        // Accede a los valores en $row
                        $categoriaID = $row["idDocumentosCategoria"];
                    }
                } else {
                    $respuesta = "No se encontró la categoría indicada en el sistema.";
                    // Manejar error en la consulta
                    $error_message = $conn->error; // Mensaje de error generado
                    $conn->close();
                    throw new Exception($error_message);
                }
            } else {
                $respuesta = "Hubo un problema intentar la búsqueda de categorías en el sistema.";
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close();
                throw new Exception($error_message);
            }
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {            
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

        try {
            include 'conexiondb.php';                                                        
            // Consulta SQL
            $sql = "SELECT * FROM Preguntas WHERE Preguntas.DocumentosCategoria_idDocumentosCategoria = $categoriaID";                    
            // Limpieza ante posibles inyecciones
            $sql = preg_replace('/[^0-9A-Za-z\s(),?¿¡!\'\":._\-$+=\*%º]/u',"",$sql);
            // Ejecutar la consulta                
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    // Encontrado, procesa los resultados
                    while ($row = $result->fetch_assoc()) {
                        // Accede a los valores en $row
                        $documentos_modificados[] = $row;
                    }
                    // Construir el array final de resultados
                    $resultados = array("Resultados" => $documentos_modificados);
                    header("HTTP/1.1 200 OK");
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
                } else {
                    $respuesta = "No se encontraron datos en el sistema.";
                    // No se encontraron resultados
                    // Manejar error en la consulta
                    $error_message = $conn->error; // Mensaje de error generado
                    $conn->close();
                    throw new Exception($error_message);
                }
            } else {
                $respuesta = "Hubo un problema con la búsqueda de preguntas en el sistema.";
                // Manejar error en la consulta
                $error_message = $conn->error; // Mensaje de error generado
                $conn->close();
                throw new Exception($error_message);
            }
            // Cerrar la conexión
            $conn->close();
        } catch (\Throwable $th) {            
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
    }  
};

?>