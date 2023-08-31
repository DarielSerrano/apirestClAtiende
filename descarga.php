<?php
// Obtén el nombre del archivo solicitado desde la consulta GET
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Ruta real del archivo en el servidor
    $rutaArchivo = '/var/www/html/apirestClAtiende/' . $archivo;
    $rutaArchivo = preg_replace('/\\+$/','',$rutaArchivo);

    // Verifica si el archivo existe y es accesible
    if (file_exists($rutaArchivo)) {
        // Configura las cabeceras para forzar la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($rutaArchivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaArchivo));

        // Lee y envía el archivo
        readfile($rutaArchivo);
        exit;
    } else {
        echo 'El archivo no existe.';
    }
} else {
    echo 'Archivo no especificado.';
}
?>
