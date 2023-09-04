<?php

//código inicial del método get
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    /* header("HTTP/1.1 200 OK");
    $respuesta = "Api de análisis, búsqueda y preguntas frecuentes.";
    echo json_encode ($respuesta);  */     
    header('Content-Type: application/json; charset=UTF-8');
    $namefinal = preg_replace('/([^0-9A-Za-zÑñ\s(),?¿¡!\'\":._\-$+=\*@#%º])/', '', $_GET['prueba']); 
    $namefinal = correccion_tildes($namefinal);
    $namefinal = escapeshellarg($namefinal);
    echo json_encode($namefinal, JSON_UNESCAPED_UNICODE);
};

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