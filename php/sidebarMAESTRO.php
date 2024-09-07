<?php

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

// Incluir la conexión a la base de datos
include 'conexion.php'; // Asegúrate de que la ruta es correcta

// Obtener el nombre de usuario de la sesión
$username = $_SESSION['user'];

    // Preparar y ejecutar la consulta para obtener el rol del usuario
    $stmt = $conn->prepare("SELECT rol FROM usuarios WHERE username = ?");
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("s", $username); // Cambia el tipo de parámetro a 's' para string
    $stmt->execute();
    $stmt->bind_result($user_role);
    $stmt->fetch();
    $stmt->close();


    // Redirigir basado en el rol del usuario
    switch ($user_role) {
        case 'ESTUDIANTE':
            header("Location: UIAlumno.php"); // Redirige a la interfaz de administrador
            exit();
        case 'ADMIN':
            header("Location: UIAdmin.php"); // Redirige a la interfaz de maestro
            exit();
        case 'APODERADO':
            header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
            exit();
    }
?> 
 <aside class="sidebar">
            <div class="logo-section">
                <a href="UIMaestro.php"><img src="../img/C.E.B.E.LOGO.png" alt="Logo" class="logo-img"></a>
                <a href="UIMaestro.php"><h2>C.E.B.E</h2></a>
                <style>
                    a {
                        text-decoration: none; /* Elimina el subrayado */
                    }
                </style>
            </div>
            <div class="divider"></div>
            <nav class="menu">
                <ul>
                    <li><a href="perfilMaestro.php">Perfil</a></li>
                    <li><a href="anunciar.php">Anunciar</a></li>
                    <li><a href="tareasPendientes.php">Tareas Pendientes</a></li>
                    <li><a href="agregarMaterial.php">Agregar Material</a></li>
                </ul>
            </nav>
            <div class="logout">
                <a href="logout.php">Cerrar Sesión</a>
            </div>
</aside>

