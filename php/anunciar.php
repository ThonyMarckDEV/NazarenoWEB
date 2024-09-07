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
    <title>Anunciar - C.E.B.E</title>
    <link rel="stylesheet" href="../css/anunciarPC.css">
    <link rel="stylesheet" href="../css/anunciarMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../css/sidebarMAESTROMobile.css">
</head>
<body>
       <!-- Incluir la Sidebar -->
       <?php include 'sidebarMAESTRO.php'; ?>
       <?php
            // Obtener el username de la sesión iniciada
            $username = $_SESSION['user'];

            // Obtener el idUsuario de la tabla usuarios
            $queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = '$username'";
            $resultUsuario = mysqli_query($conn, $queryUsuario);
            $idUsuario = mysqli_fetch_assoc($resultUsuario)['idUsuario'];

            // Obtener los cursos asignados al docente (idDocente = idUsuario) de la tabla especialidad_docente
            $queryCursos = "
                SELECT cursos.nombreCurso 
                FROM especialidaddocente 
                JOIN cursos ON especialidaddocente.idEspecialidad = cursos.idCurso 
                WHERE especialidaddocente.idDocente = '$idUsuario'
            ";
            $resultCursos = mysqli_query($conn, $queryCursos);
            ?>

            <div class="contenedor-cursos">
                <?php while ($curso = mysqli_fetch_assoc($resultCursos)) { ?>
                    <div class="curso-modulo">
                        <button><?php echo $curso['nombreCurso']; ?></button>
                    </div>
                <?php } ?>
            </div>
</body>
</html>
