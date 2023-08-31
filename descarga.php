<?php
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Ruta real del archivo en el servidor
    $rutaArchivo = '/var/www/html/apirestClAtiende/' . $archivo;

    if (file_exists($rutaArchivo)){

        ob_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='.basename($rutaArchivo));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaArchivo));
        ob_end_clean();
        flush();
        readfile($rutaArchivo);
        exit;
    }
    else{
        echo 'Archivo no especificado.';
    }
} else {
    echo 'Archivo no especificado.';
}
?>