<?php
include '../conexion.php'; // Asegúrate de que la ruta es correcta

if (isset($_GET['idTarea'])) {
    $idTarea = $_GET['idTarea'];

    // Obtener el archivo de la base de datos
    $queryArchivo = "SELECT archivo_nombre, archivo_contenido, archivo_tipo FROM tareas_alumnos WHERE idTarea = ?";
    $stmt = $conn->prepare($queryArchivo);
    $stmt->bind_param("i", $idTarea);
    $stmt->execute();
    $stmt->bind_result($archivo_nombre, $archivo_contenido, $archivo_tipo);
    $stmt->fetch();

    if ($archivo_contenido) {
        header("Content-Disposition: attachment; filename=" . $archivo_nombre);
        header("Content-Type: " . $archivo_tipo);
        echo $archivo_contenido;
        exit();
    } else {
        echo "Archivo no encontrado.";
    }
}
?>
