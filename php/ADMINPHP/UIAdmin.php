<?php
    session_start();

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['user'])) {
        header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
        exit();
    }

    // Incluir la conexión a la base de datos
    include '../../php/conexion.php'; // Asegúrate de que la ruta es correcta

    // Obtener el nombre de usuario de la sesión
    $username = $_SESSION['user'];

        // Consulta para obtener el idUsuario y verificar el estado del usuario
    $sql = "SELECT idUsuario, status FROM usuarios WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Obtener el resultado de la consulta
        $row = $result->fetch_assoc();
        
        if ($row['status'] == 'loggedOff') {
            header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
        exit();
        }
    }

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
    $conn->close();

    // Redirigir basado en el rol del usuario
    switch ($user_role) {
        case 'ESTUDIANTE':
            header("Location: ../ALUMNOPHP/UIAlumno.php"); // Redirige a la interfaz de administrador
            exit();
        case 'DOCENTE':
            header("Location: ../DOCENTEPHP/UIMaestro.php"); // Redirige a la interfaz de maestro
            exit();
        case 'APODERADO':
            header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
            exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/ADMINCSS/UIAdminPC.css">
    <link rel="stylesheet" href="../../css/ADMINCSS/UIAdminMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/ADMINCSS/sidebarADMINPC.css">
    <link rel="stylesheet" href="../../css/ADMINCSS/sidebarADMINMobile.css">
</head>
<body>
    <div class="container">

       <!-- Incluir la Sidebar -->
       <?php include 'sidebarADMIN.php'; ?>

        <!-- Main Content Placeholder -->
        <main class="main-content">
            <section>
                <h2>Bienvenido al Panel de Administración</h2>
                <p>Seleccione una opción del menú lateral para comenzar.</p>
            </section>
        </main>
    </div>
</body>
</html>
