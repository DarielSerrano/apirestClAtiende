<?php
// Obtén el nombre del archivo solicitado desde la consulta GET
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Ruta real del archivo en el servidor
    $rutaArchivo = '/ruta/completa/a/tus/archivos/' . $archivo;

    // Verifica si el archivo existe y es seguro
    if (file_exists($rutaArchivo) && is_readable($rutaArchivo)) {
        // Establece las cabeceras para forzar la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($rutaArchivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaArchivo));
        readfile($rutaArchivo);
        exit;
    } else {
        echo "El archivo no existe o no es accesible.";
    }
} else {
    echo "Nombre de archivo no proporcionado.";
}
?>