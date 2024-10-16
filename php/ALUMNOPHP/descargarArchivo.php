<?php
include '../conexion.php'; // Asegúrate de que la ruta es correcta

if (isset($_GET['idArchivo'])) {
    $idArchivo = $_GET['idArchivo'];

    // Consultar el archivo desde la base de datos
    $query = "SELECT nombre, tipo, contenido FROM archivos WHERE idArchivo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idArchivo);
    $stmt->execute();
    $stmt->bind_result($nombre, $tipo, $contenido);
    $stmt->fetch();
    $stmt->close();

    // Forzar la descarga del archivo
    header("Content-Disposition: attachment; filename=" . $nombre);
    header("Content-Type: " . $tipo);
    echo $contenido;
    exit();
} else {
    echo "Archivo no encontrado.";
}
?>
