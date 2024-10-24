<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../conexion.php'; 

// Obtener el nombre de usuario de la sesión
$username = $_SESSION['user'];

// Preparar y ejecutar la consulta para obtener el idUsuario del alumno
$queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = ?";
$stmtUsuario = $conn->prepare($queryUsuario);
$stmtUsuario->bind_param("s", $username);
$stmtUsuario->execute();
$stmtUsuario->bind_result($idUsuario);
$stmtUsuario->fetch();
$stmtUsuario->close();

// Verificar si se ha enviado el idModulo
if (isset($_POST['idModulo'])) {
    $idModulo = $_POST['idModulo'];

    // Obtener las actividades y las calificaciones del alumno para el módulo seleccionado
    $queryActividades = "
        SELECT a.titulo, a.descripcion, ta.nota, ta.revisado 
        FROM actividades a
        LEFT JOIN tareas_alumnos ta ON a.idActividad = ta.idActividad AND ta.idUsuario = ?
        WHERE a.idModulo = ?
    ";
    $stmtActividades = $conn->prepare($queryActividades);
    $stmtActividades->bind_param("ii", $idUsuario, $idModulo);
    $stmtActividades->execute();
    $resultActividades = $stmtActividades->get_result();
} else {
    die("No se ha seleccionado ningún módulo.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones del Módulo</title>
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verCalificacionesPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verCalificacionesMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <div class="container">
        <h1>Calificaciones del Módulo</h1>
        
        <?php if ($resultActividades->num_rows > 0): ?>
            <?php while ($actividad = $resultActividades->fetch_assoc()): ?>
                <div class="contenedor-actividad">
                    <h3><?php echo htmlspecialchars($actividad['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars($actividad['descripcion']); ?></p>
                    <div class="actividad-info">
                        <span><strong>Calificación:</strong> <?php echo htmlspecialchars($actividad['nota'] !== null ? $actividad['nota'] : 'Pendiente'); ?></span>
                        <span><strong>Estado:</strong> <?php echo htmlspecialchars($actividad['revisado'] === 'SI' ? 'Revisado' : 'No Revisado'); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay actividades disponibles para este módulo.</p>
        <?php endif; ?>
    </div>
</body>
</html>
