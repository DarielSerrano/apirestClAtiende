<?php

//codigo del metodo post para recibir pdfs
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Obtener los datos del formulario
    $rut = $_POST['UsuarioRut'];
    $password = $_POST['UsuarioContrasena'];

    // Validar si el RUT y la contraseña no están vacíos
    if (empty($rut)) 
    {
        $respuesta = "Debes completar con tu Rut.";
    } 
    elseif (empty($password)) 
    {
        $respuesta = "Debes completar con tu contraseña.";
    }
    else 
    {
        // Realizar otras validaciones específicas, como formato de RUT válido
        // y verificación de contraseña (por ejemplo, consultar en la base de datos)
        if (!formatoRUTValido($rut)) 
        {
            $respuesta = "Rut en formato incorrecto, revise el difito verificador.";
        } 
        elseif (!validarContrasena($rut, $password))
        {
            $respuesta = "Contraseña incorrecta.";
        }         
    }    
    if (!empty($_FILES['archivo']))
    {
        if ($_FILES['archivo']['type'] == 'application/pdf')
        {    
            $ruta_destino = "archivos/";
            $namefinal = trim ($_FILES['archivo']['name']);  
            $namefinal = preg_replace('([^[A-Z][a-z]*\s*.+])', '', $namefinal);
            $namefinal = preg_replace('/\s+/', '_', $namefinal);
            $namefinal = strtolower($namefinal);
            $uploadfile = $ruta_destino . $namefinal; 
            if(is_uploaded_file($_FILES['archivo']['tmp_name'])) 
            {                    
                if(move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadfile)) 
                {      
                    $txt= preg_replace("/pdf/", 'txt', $namefinal);
                    $txt = $ruta_destino . $txt;            
                    $pdf = $ruta_destino . $namefinal;                                          
                    try {
                        exec ("pdftotext.exe $pdf $txt");
                    } catch (\Throwable $th) {
                        $mensaje = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe el mensaje junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $mensaje" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                    }
                    $extraer = "paquetes/extraer.py";
                    try 
                    {
                        exec("python3.10 $extraer $txt",$salida);  
                        // Convertir la salida JSON en un array asociativo de PHP
                        $jsonOutput = implode("", $salida);
                        $data = json_decode($jsonOutput, true);

                        // Acceder y trabajar con los resultados
                        $resultados = $data["resultados"];
                        foreach ($resultados as $resultado) {
                            /* $dato1 = $resultado["dato1"];
                            $dato2 = $resultado["dato2"];
                            echo "Dato1: $dato1, Dato2: $dato2<br>"; */
                        }

                    }
                    catch (\Throwable $th) 
                    {
                        $mensaje = $th->getMessage();
                        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato deseado                                        
                        $nombreArchivo = "logs_de_error.txt";                    
                        // Abre o crea el archivo de log en modo de escritura al final del archivo
                        $nombreArchivo = fopen($rutaLog, "a");
                        // Escribe el mensaje junto con la fecha y hora en el archivo de log
                        fwrite($nombreArchivo, "[$fechaHora] $mensaje" . PHP_EOL);
                        // Cierra el archivo de log
                        fclose($nombreArchivo);
                        $respuesta['mensaje']='Analisis con python fallo';                    
                        header("HTTP/1.1 200 OK");
                        echo json_encode($respuesta); 
                        exit();
                    }                                                           
                }
                else
                {
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
            else
            {
                $respuesta='El servidor no pudo efectuar la subida de archivo debido a un error interno.';
                echo json_encode($respuesta);
                exit();
            }                               
        }
        else
        {
            $respuesta='El archivo adjunto no es un documento pdf.';
            echo json_encode($respuesta);
            exit();
        }
    }
    else
    {
        $respuesta='No se encuentra adjunto un documento PDF.';
        echo json_encode($respuesta);
        exit();
    }
}

function formatoRUTValido($rut) 
{
    $rut = preg_replace('/[^kK0-9]/', '', $rut); // Eliminar caracteres no válidos

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
    $suma = 0;
    $i = 2;
    $suma = 0;
    foreach(array_reverse(str_split($rutNumeros)) as $v)
    {
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
    } elseif ($digitoVerificadorCalculado == 10 && $dv == 'K') {
        return true;
    } elseif ($digitoVerificadorCalculado == intval($dv)) {
        return true;
    }

    return false;
}

function validarContrasena($rut,$password)
{
    $password = password_hash($password, PASSWORD_DEFAULT);
    include 'conexiondb.php';
    // Consulta SQL con cláusula WHERE
    $sql = "SELECT UsuarioContrasena FROM Usuarios WHERE UsuarioRut = $rut";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) 
    {
        // Encontrado, procesa los resultados
        while ($row = $result->fetch_assoc()) 
        {
            // Accede a los valores en $row
            $Contrasena = $row["UsuarioContrasena"];
            // ...
        }
    } 
    // Cerrar la conexión
    $conn->close(); 
    //Verificar la contraseña hash con la almacenada 
    if (password_verify($password, $Contrasena)) 
    {
        return true;
    }
    else
    {
        return false
    }
}

?>