<?php
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Ruta real del archivo en el servidor
    $rutaArchivo = '/var/www/html/apirestClAtiende/' . $archivo;

    // Verifica si el archivo existe y es accesible
    if (file_exists($rutaArchivo)) {
        // Configura las cabeceras para forzar la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($rutaArchivo) . '"');
        header('Content-Length: ' . filesize($rutaArchivo));

        // Desactivar la salida de PHP
        ob_clean();
        flush();

        // Lee y envía el archivo en bloques
        readfile($rutaArchivo);
        exit;
    } else {
        echo 'El archivo no existe.';
    }
} else {
    echo 'Archivo no especificado.';
}
?>