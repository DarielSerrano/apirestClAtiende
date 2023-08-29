<?php
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
    if (password_verify($UsuarioContrasena, $Contrasena)) {
        return true;
    } 
    else {
        return false;
    }
}
?>