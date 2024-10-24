<?php
include '../conexion.php'; // Asegúrate de que la ruta es correcta

if (isset($_POST['idTarea'], $_POST['nota'])) {
    $idTarea = $_POST['idTarea'];
    $nota = $_POST['nota'];

    // Actualizar la tarea con la nueva nota y marcar como revisada
    $queryUpdate = "UPDATE tareas_alumnos SET nota = ?, revisado = 'SI' WHERE idTarea = ?";
    $stmt = $conn->prepare($queryUpdate);
    $stmt->bind_param("di", $nota, $idTarea);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Tarea actualizada y revisada.";
    } else {
        echo "Error al actualizar la tarea.";
    }

    $stmt->close();
}
?>
