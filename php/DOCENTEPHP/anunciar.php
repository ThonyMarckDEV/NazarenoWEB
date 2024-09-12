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
    case 'ESTUDIANTE':
        header("Location: ../ALUMNOPHP/UIAlumno.php");
        exit();
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php");
        exit();
    case 'APODERADO':
        header("Location: ../APODERADOPHP/UIApoderado.php");
        exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['nombreCurso']) && isset($_POST['descripcion']) && isset($_POST['seccion'])) {
        $nombreCurso = $_POST['nombreCurso'];
        $descripcion = $_POST['descripcion'];
        $seccion = $_POST['seccion'];
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt = $conn->prepare("INSERT INTO anunciosMaestro (nombreCurso, descripcion, seccion, fecha, hora) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $errorMsg = urlencode("Error en la preparación de la consulta: " . $conn->error);
            header("Location: anunciarMaestro.php?status=error&message=$errorMsg");
            exit();
        }

        $stmt->bind_param("sssss", $nombreCurso, $descripcion, $seccion, $fecha, $hora);
        if ($stmt->execute()) {
            header("Location: anunciarMaestro.php?status=success");
        } else {
            $errorMsg = urlencode("Error al registrar el anuncio.");
            header("Location: anunciarMaestro.php?status=error&message=$errorMsg");
        }
        $stmt->close();
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anunciar - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/DOCENTECSS/anunciarPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/anunciarMobile.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css">
</head>
<body>
    <div class="notification" id="notification"></div> <!-- Contenedor de la notificación -->
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?>

    <?php
    $username = $_SESSION['user'];
    $queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = '$username'";
    $resultUsuario = mysqli_query($conn, $queryUsuario);
    $idUsuario = mysqli_fetch_assoc($resultUsuario)['idUsuario'];

    $queryCursos = "
    SELECT cursos.nombreCurso, grados.seccion
    FROM cursos
    JOIN grados ON cursos.idGrado = grados.idGrado
    JOIN especialidaddocente ON cursos.idEspecialidad = especialidaddocente.idEspecialidad
    WHERE especialidaddocente.idDocente = '$idUsuario'
    ";
    $resultCursos = mysqli_query($conn, $queryCursos);
    ?>
    <div class="contenedor-cursos">
        <?php while ($curso = mysqli_fetch_assoc($resultCursos)) { ?>
            <div class="curso-modulo">
                <form action="anunciarMaestro.php" method="POST">
                    <input type="hidden" name="nombreCurso" value="<?php echo htmlspecialchars($curso['nombreCurso']); ?>">
                    <input type="hidden" name="seccion" value="<?php echo htmlspecialchars($curso['seccion']); ?>">
                    <button type="submit" name="submit">
                        <?php echo htmlspecialchars($curso['nombreCurso']); ?> - <?php echo htmlspecialchars($curso['seccion']); ?>
                    </button>
                </form>
            </div>
        <?php } ?>
    </div>
    <script>
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            var status = urlParams.get('status');
            var message = urlParams.get('message') || '';

            var notification = document.getElementById('notification');
            
            if (status === 'success') {
                notification.innerText = 'Anuncio agregado exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al agregar Anuncio: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }
        };
    </script>
</body>
</html>