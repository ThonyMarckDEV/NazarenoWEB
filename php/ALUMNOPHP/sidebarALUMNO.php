<?php

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

// Incluir la conexión a la base de datos
include '../conexion.php'; // Asegúrate de que la ruta es correcta

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
        case 'DOCENTE':
            header("Location: ../DOCENTEPHP/UIMaestro.php"); // Redirige a la interfaz de administrador
            exit();
        case 'ADMIN':
            header("Location: ../ADMINPHP/UIAdmin.php"); // Redirige a la interfaz de maestro
            exit();
        case 'APODERADO':
            header("Location: ../APODERADO/UIApoderado.php"); // Redirige a la interfaz de apoderado
            exit();
    }
?>
 <aside class="sidebar">
            <div class="logo-section">
                <a href="UIAlumno.php"><img src="../../img/C.E.B.E.LOGO.png" alt="Logo" class="logo-img"></a>
                <a href="UIAlumno.php"><h2>C.E.B.E</h2></a>
                <style>
                    a {
                        text-decoration: none; /* Elimina el subrayado */
                    }
                </style>
            </div>
            <div class="divider"></div>
            <nav class="menu">
                <ul>
                    <li><a href="perfilAlumno.php"><img src="../../img/perfil.png" alt="Perfil" class="menu-icon">Perfil</a></li>
                    <li><a href="anunciosAlumno.php"><img src="../../img/perfil.png" alt="Perfil" class="menu-icon">Anuncios</a></li>
                    <li><a href="cursosAlumno.php"><img src="../../img/perfil.png" alt="Perfil" class="menu-icon">Cursos</a></li>
                    <li><a href="calificacionesAlumno.php"><img src="../../img/perfil.png" alt="Perfil" class="menu-icon">Calificaciones</a></li>
                    <li><a href="../logout.php"><img src="../../img/perfil.png" alt="Perfil" class="menu-icon">Cerrar Sesion</a></li>
                </ul>
            </nav>
</aside>
<script>
        // Función para verificar el estado de la sesión del usuario
        function verificarEstadoUsuario() {
            fetch('../verificar_estado.php', { // Ajusta la ruta según la ubicación de verificar_estado.php
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                cache: 'no-cache'  // Evitar caché en solicitudes
            })
            .then(response => response.text())
            .then(data => {
                if (data === "loggedOff") {
                    // Redirigir al index si está deslogueado
                    window.location.href = '../../index.php';
                }
            })
            .catch(error => console.error('Error al verificar el estado del usuario:', error));
        }

        // Ejecutar la verificación cada 3 segundos
        setInterval(verificarEstadoUsuario, 3000);

        // Ejecutar la verificación al cargar la página
        verificarEstadoUsuario();
    </script>
