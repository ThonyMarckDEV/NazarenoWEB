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
        header("Location: ../DOCENTEPHP/UIMaestro.php"); // Redirige a la interfaz de docente
        exit();
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php"); // Redirige a la interfaz de administrador
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

// Obtener los cursos en los que el alumno está matriculado
$queryCursos = "
    SELECT cursos.nombreCurso
    FROM cursos
    JOIN alumnosmatriculados ON cursos.idGrado = alumnosmatriculados.idGrado
    WHERE alumnosmatriculados.idUsuario = ?
";
$stmtCursos = $conn->prepare($queryCursos);
if ($stmtCursos === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmtCursos->bind_param("i", $idUsuario);
$stmtCursos->execute();
$resultCursos = $stmtCursos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anunciar - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/ALUMNOCSS/cursosAlumnoPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/cursosAlumnoMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <div class="contenedor-cursos">
    <?php
        if ($resultCursos->num_rows > 0) {
            while ($curso = $resultCursos->fetch_assoc()) { ?>
                <div class="curso-modulo">
                    <form action="verCursoAlumno.php" method="POST">
                        <input type="hidden" name="nombreCurso" value="<?php echo htmlspecialchars($curso['nombreCurso']); ?>">
                        <button type="submit"><?php echo htmlspecialchars($curso['nombreCurso']); ?></button>
                    </form>
                </div>
            <?php }
        } else {
            echo '<p style="color:black;">No estás matriculado en ningún curso.</p>';
        }
    ?>
    </div>
</body>
</html>
