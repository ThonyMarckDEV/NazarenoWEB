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

// Verificar si se ha enviado el nombreCurso
if (isset($_POST['nombreCurso'])) {
    $nombreCurso = $_POST['nombreCurso'];

    // Obtener el idCurso del nombreCurso
    $queryCursoId = "SELECT idCurso FROM cursos WHERE nombreCurso = ?";
    $stmtCursoId = $conn->prepare($queryCursoId);
    $stmtCursoId->bind_param("s", $nombreCurso);
    $stmtCursoId->execute();
    $stmtCursoId->bind_result($idCurso);
    $stmtCursoId->fetch();
    $stmtCursoId->close();

    // Obtener los módulos asignados al curso
    $queryModulos = "
        SELECT modulos.nombre, modulos.idModulo
        FROM modulos 
        WHERE modulos.idCurso = ?
    ";
    $stmtModulos = $conn->prepare($queryModulos);
    $stmtModulos->bind_param("i", $idCurso);
    $stmtModulos->execute();
    $resultModulos = $stmtModulos->get_result();
} else {
    die("No se ha seleccionado ningún curso.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulos del Curso - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/DOCENTECSS/agregarMaterialCursoPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/agregarMaterialCursoMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <div class="contenedor-cursos">
        <?php if ($resultModulos->num_rows > 0): ?>
            <?php while ($modulo = $resultModulos->fetch_assoc()) { ?>
                <div class="curso-modulo">
                <form action="verModuloAlumno.php" method="POST">
                    <input type="hidden" name="idModulo" value="<?php echo htmlspecialchars($modulo['idModulo']); ?>">
                    <button type="submit">
                        <?php echo htmlspecialchars($modulo['nombre'] . ' (' . $nombreCurso . ')'); ?>
                    </button>
                </form>
                </div>
            <?php } ?>
        <?php else: ?>
            <p>No hay módulos disponibles para este curso.</p>
        <?php endif; ?>
    </div>
</body>
</html>
