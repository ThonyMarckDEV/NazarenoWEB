<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

// Incluir la conexión a la base de datos
include '../conexion.php'; // Asegúrate de que la ruta es correcta

// Obtener el nombre de usuario de la sesión
$username = $_SESSION['user'];

// Obtener el nombre del curso y la sección desde el formulario
$nombreCurso = $_GET['nombreCurso'] ?? '';
$seccion = $_GET['seccion'] ?? '';

// Preparar y ejecutar la consulta para obtener el rol del usuario
$stmt = $conn->prepare("SELECT rol FROM usuarios WHERE username = ?");
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("s", $username);
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
        header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

// Obtener el idUsuario de la tabla usuarios
$queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = ?";
$stmtUsuario = $conn->prepare($queryUsuario);
if ($stmtUsuario === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmtUsuario->bind_param("s", $username);
$stmtUsuario->execute();
$stmtUsuario->bind_result($idUsuario);
$stmtUsuario->fetch();
$stmtUsuario->close();

// Verificar si se encontró el idUsuario
if (!$idUsuario) {
    die("Error: No se encontró el idUsuario para el usuario '$username'.");
}

// Obtener los anuncios filtrados por el curso y sección
$queryAnuncios = "
    SELECT idAnuncio, nombreCurso, seccion, descripcion, fecha, hora
    FROM anunciosMaestro
    WHERE nombreCurso = ? AND seccion = ?
";
$stmtAnuncios = $conn->prepare($queryAnuncios);
if ($stmtAnuncios === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmtAnuncios->bind_param("ss", $nombreCurso, $seccion);
$stmtAnuncios->execute();
$resultAnuncios = $stmtAnuncios->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Anuncios - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verAnunciosAlumnoPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verAnunciosAlumnoMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <div class="contenedor-anuncios">
        <?php
        while ($anuncio = $resultAnuncios->fetch_assoc()) { ?>
            <div class="anuncio-modulo">
                <h3><?php echo htmlspecialchars($anuncio['nombreCurso']) . ' - ' . htmlspecialchars($anuncio['seccion']); ?></h3>
                <p><?php echo htmlspecialchars($anuncio['descripcion']); ?></p>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($anuncio['fecha']); ?></p>
                <p><strong>Hora:</strong> <?php echo htmlspecialchars($anuncio['hora']); ?></p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
