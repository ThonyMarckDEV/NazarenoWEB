<?php
    session_start();

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
    $conn->close();

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maestro - C.E.B.E</title>
    <link rel="stylesheet" href="../css/UIMaestroPC.css">
    <link rel="stylesheet" href="../css/UIMaestroMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../css/sidebarMAESTROMobile.css">
</head>
<body>
    <div class="container">

       <!-- Incluir la Sidebar -->
       <?php include 'sidebarMAESTRO.php'; ?>

        <!-- Main Content Placeholder -->
        <main class="main-content">
            <section>
                <h2>Bienvenido al Panel de Maestro</h2>
                <p>Seleccione una opción del menú lateral para comenzar.</p>
            </section>
        </main>
    </div>
</body>
</html>
