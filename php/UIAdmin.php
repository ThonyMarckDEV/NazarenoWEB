<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - C.E.B.E</title>
    <link rel="stylesheet" href="../css/UIAdminPC.css">
    <link rel="stylesheet" href="../css/UIAdminMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarADMINPC.css">
    <link rel="stylesheet" href="../css/sidebarADMINMobile.css">
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
