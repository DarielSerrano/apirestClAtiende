<?php

//codigo del metodo post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (!empty($_FILES['archivo']))
    {
        if ($_FILES['archivo']['type'] == 'application/pdf')
        {    
            $ruta_destino = "./archivos/";
            $namefinal= trim ($_FILES['archivo']['name']);  
            $namefinal = preg_replace('([^[A-Z][a-z]*\s*.+])', '', $namefinal);
            $namefinal= preg_replace('/\s+/', '_', $namefinal);
            $namefinal= strtolower($namefinal);
            $uploadfile= $ruta_destino . $namefinal; 
            if(is_uploaded_file($_FILES['archivo']['tmp_name'])) 
            {                    
                if(move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadfile)) 
                {      
                    $ruta_destino = "archivos/";
                    $txt= preg_replace("/pdf/", 'txt', $namefinal);
                    $txt = $ruta_destino . $txt;            
                    $uploadfile = $ruta_destino . $namefinal;  
                    exec ("C:/xampp/htdocs/apirestClAtiende/paquetes/pdftotext.exe $uploadfile $txt");
                    $nlp_pos = "paquetes/nlp_pos.py";
                    exec("py $nlp_pos $txt",$salida);                       
                    echo json_encode(print_r ($salida[0]));
                    exit();                                        
                }else
                {
                    $respuesta['mensaje']='Analisis fallo';
                    header("HTTP/1.1 200 OK");
                    echo json_encode($respuesta); 
                    exit();
                }     
            }else
            {
                $respuesta='Archivo subido? Fallo';
                echo json_encode($respuesta);
                exit();
            }                               
        }else
        {
            $respuesta='Archivo no es pdf';
            echo json_encode($respuesta);
            exit();
        }
    }else
    {
        $respuesta='Archivo vacio';
        echo json_encode($respuesta);
        exit();
    }
};

?>