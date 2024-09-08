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
        case 'DOCENTE':
            header("Location: UIMaestro.php"); // Redirige a la interfaz de administrador
            exit();
        case 'ADMIN':
            header("Location: UIAdmin.php"); // Redirige a la interfaz de maestro
            exit();
        case 'APODERADO':
            header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
            exit();
    }


// Obtener el idUsuario de la tabla usuarios
$queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = '$username'";
$resultUsuario = mysqli_query($conn, $queryUsuario);

// Verificar si la consulta fue exitosa
if (!$resultUsuario) {
    die("Error en la consulta del usuario: " . mysqli_error($conn));
}

$idUsuario = mysqli_fetch_assoc($resultUsuario)['idUsuario'];

// Verificar si se encontró el idUsuario
if (!$idUsuario) {
    die("Error: No se encontró el idUsuario para el usuario '$username'.");
}

// Obtener los cursos que el alumno está matriculado (idUsuario = idAlumno) de la tabla alumnosmatriculados
$queryCursos = "
    SELECT nombreCurso 
    FROM cursos 
    JOIN alumnosmatriculados ON alumnosmatriculados.idCurso = cursos.idCurso 
    WHERE alumnosmatriculados.idUsuario = '$idUsuario'
";
$resultCursos = mysqli_query($conn, $queryCursos);

// Verificar si la consulta fue exitosa
if (!$resultCursos) {
    die("Error en la consulta de cursos: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anunciar - C.E.B.E</title>
    <link rel="stylesheet" href="../css/anunciosAlumnoPC.css">
    <link rel="stylesheet" href="../css/anunciosAlumnoMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarALUMNOPC.css">
    <link rel="stylesheet" href="../css/sidebarALUMNOMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <div class="contenedor-cursos">
        <?php
            if (mysqli_num_rows($resultCursos) > 0) {
                while ($curso = mysqli_fetch_assoc($resultCursos)) { ?>
                    <div class="curso-modulo">
                        <button><?php echo htmlspecialchars($curso['nombreCurso']); ?></button>
                    </div>
                <?php }
            } else {
                echo '<p style="color:black;">No estás matriculado en ningún curso.</p>';
            }
        ?>
    </div>
</body>
</html>