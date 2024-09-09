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

// Redirigir basado en el rol del usuario
switch ($user_role) {
    case 'ESTUDIANTE':
        header("Location: UIAlumno.php"); // Redirige a la interfaz de estudiante
        exit();
    case 'ADMIN':
        header("Location: UIAdmin.php"); // Redirige a la interfaz de administrador
        exit();
    case 'APODERADO':
        header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

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
        SELECT modulos.nombre,modulos.idModulo
        FROM modulos 
        WHERE modulos.idCurso = ?
    ";
    $stmtModulos = $conn->prepare($queryModulos);
    $stmtModulos->bind_param("i", $idCurso);
    $stmtModulos->execute();
    $resultModulos = $stmtModulos->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulos del Curso - C.E.B.E</title>
    <link rel="stylesheet" href="../css/agregarMaterialCursoPC.css">
    <link rel="stylesheet" href="../css/agregarMaterialCursoMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../css/sidebarMAESTROMobile.css">
</head>
<body>
        <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?>
    <div class="contenedor-cursos">
        <?php if (isset($resultModulos)): ?>
            <?php while ($modulo = $resultModulos->fetch_assoc()) { ?>
                <div class="curso-modulo">
                    <form action="agregarMaterialModulo.php" method="POST">
                        <!-- Enviar el id del módulo -->
                        <input type="hidden" name="idModulo" value="<?php echo htmlspecialchars($modulo['idModulo']); ?>">
                        <button type="submit" name="submit">
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