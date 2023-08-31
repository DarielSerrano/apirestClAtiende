<?php
$directorioPermitido = '/var/www/html/apirestClAtiende/archivos/';

if (isset($_GET['archivo'])) {
    $nombreArchivoReal = $_GET['archivo'];
    $rutaArchivo = $directorioPermitido . $nombreArchivoReal;

    // Verifica si la ruta del archivo está dentro del directorio permitido
    if (strpos(realpath($rutaArchivo), $directorioPermitido) === 0 && file_exists($rutaArchivo)) {
        // Configura las cabeceras para forzar la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($rutaArchivo) . '"');
        header('Content-Length: ' . filesize($rutaArchivo));

        // Envía el contenido del archivo
        readfile($rutaArchivo);
        exit;
    } else {
        echo 'Archivo no permitido para descarga.';
    }
} else {
    echo 'Archivo no especificado.';
}
?>