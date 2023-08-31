<?php
// Obtén el nombre del archivo solicitado desde la consulta GET
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Ruta real del archivo en el servidor
    $rutaArchivo = '/var/www/html/apirestClAtiende/' . $archivo;

    // Verifica si el archivo existe y es accesible
    if (file_exists($rutaArchivo)) {
        // Configura las cabeceras para forzar la descarga
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename=' . basename($rutaArchivo));
        header('Content-Length: ' . filesize($rutaArchivo));

        // Lee y envía el archivo en bloques
        $fp = fopen($rutaArchivo, 'rb');
        while (!feof($fp)) {
            echo fread($fp, 1024);
        }
        fclose($fp);
        exit;
    } else {
        echo 'El archivo no existe.';
    }
} else {
    echo 'Archivo no especificado.';
}
?>