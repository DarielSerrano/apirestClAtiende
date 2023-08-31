<?php
$directorioPermitido = '/var/www/html/apirestClAtiende/archivos/';

if (isset($_GET['doc'])) {
    $nombreArchivoReal = $_GET['doc'];
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
        header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
        header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
        echo json_encode('Archivo no existe o no esta permitido para descarga.', JSON_UNESCAPED_UNICODE);
    }
} else {
    header("HTTP/1.1 400 Bad Request");  // Encabezado de estado
    header('Content-Type: application/json; charset=UTF-8');  // Encabezado Content-Type
    echo json_encode('Archivo no especificado.', JSON_UNESCAPED_UNICODE);
}
?>