<?php
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['user'])) {
    $nombre = $_SESSION['user'];

    // Actualizar el estado del usuario a 'loggedOff'
    $update_sql = "UPDATE usuarios SET status = 'loggedOff' WHERE username = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();

    // Cerrar la sesión
    session_unset();
    session_destroy();
}

// Redirigir a la página de inicio de sesión
header("Location: ../index.php");
exit();
?>
