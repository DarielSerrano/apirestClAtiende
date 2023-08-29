<?php
 
//codigo inicial del metodo get 
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    header("HTTP/1.1 200 OK");
    $respuesta = "Api de analisis, busqueda y preguntas frecuentes";
    echo json_encode ($respuesta);      
}; 

/* if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aumentar el tiempo límite de ejecución a un valor en segundos (por ejemplo, 300 segundos)
    set_time_limit(1200);

    $response = array();

    $response[] = "Intentando ejecutar script python, espere";
    try {
        // Ejecuta el script de Python y captura la salida
        $python_output1 = shell_exec('cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py archivos/prueba.txt'); 
        $python_output2 = shell_exec('cd /var/www/html/apirestClAtiende && python3.10 paquetes/etiquetar.py archivos/prueba.txt'); 
        $python_output3 = shell_exec('cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache python3.10 paquetes/resumir.py archivos/prueba.txt'); 
        
        // Agregar la salida del script de Python al array de respuesta
        $response[] = "Script ejecutado:";
        $response[] = $python_output1;
        $response[] = $python_output2;
        $response[] = $python_output3;
        
        // Imprimir la respuesta completa como JSON
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
        exit();

    } catch (\Throwable $th) {
        $response[] = "Script no ejecutado, error:";
        $error = $th->getMessage();
        $response[] = $error;
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
    }
} */

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
        $fechaHora = preg_replace('/\s+/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                        
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
    $response[] = preg_replace('/\s+/', '_', date("Y-m-d H:i:s"));
    
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
                    $output = shell_exec ("cd /var/www/html/apirestClAtiende && pdftotext");
                    if ($output === null) {
                        throw new Exception("Hubo un problema al hacer la transformación de pdf a texto.");
                    }
                } 
                catch (Exception $th) {
                    $error = $th->getMessage();
                    $fechaHora = preg_replace('/\s+/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                        
                    $rutaLog = "logs_de_error.txt";                    
                    // Abre o crea el archivo de log en modo de escritura al final del archivo
                    $rutaLog = fopen($rutaLog, "a");
                    // Escribe la excepcion junto con la fecha y hora en el archivo de log
                    fwrite($rutaLog, "[$fechaHora]_$error" . PHP_EOL);
                    // Cierra el archivo de log
                    fclose($rutaLog);
                    header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
                    header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
                    echo json_encode($error, JSON_UNESCAPED_UNICODE);
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

function validarContrasena($rut,$pass) {
    
    // Declarar la variable $Contrasena
    $Contrasena = null;
    
    // funcion para transformar la contraseña a hash
    // $passwordhash = password_hash($pass, PASSWORD_DEFAULT);
    include 'conexiondb.php';

    try {
        // Consulta SQL con cláusula WHERE
        $sql = "SELECT UsuarioContrasena FROM Usuario WHERE UsuarioRut = $rut";
        $result = $conn->query($sql);
    } 
    catch (\Throwable $th) {
        $respuesta = "No se logró hacer la consulta a la base de datos para validar contraseña.";
        $error = $th->getMessage();
        $fechaHora = preg_replace('/\s+/', '_', date("Y-m-d H:i:s")); // Obtiene la fecha y hora actual                                        
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
    if (password_verify($pass, $Contrasena)) {
        return true;
    } 
    else {
        return false;
    }

}

?>