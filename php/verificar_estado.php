<?php
session_start();

// Desactivar el caché para asegurarse de que las solicitudes no se guarden en el navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    echo "loggedOff"; // No hay usuario en sesión, está deslogueado
    exit();
}

// Conexión a la base de datos
include 'conexion.php';

// Obtener el nombre de usuario de la sesión
$username = $_SESSION['user'];

// Verificar el estado del usuario en la base de datos
$sql = "SELECT status FROM usuarios WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();
$conn->close();

// Si el estado es 'loggedOff', enviar respuesta de que está deslogueado
if ($status === 'loggedOff') {
    echo "loggedOff";
} else {
    echo "loggedIn";
}
?>