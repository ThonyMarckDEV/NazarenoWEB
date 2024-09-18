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
    case 'ESTUDIANTE':
        header("Location: ../ALUMNOPHP/UIAlumno.php"); // Redirige a la interfaz de administrador
        exit();
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php"); // Redirige a la interfaz de maestro
        exit();
    case 'APODERADO':
        header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

// Verificar si se ha recibido el curso y la descripción para el anuncio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['nombreCurso']) && isset($_POST['descripcion'])) {
        // Obtener los valores enviados por POST
        $nombreCurso = $_POST['nombreCurso'];
        $descripcion = $_POST['descripcion'];

        // Obtener la fecha y hora actual
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        // Insertar el anuncio en la tabla anunciosMaestro
        $stmt = $conn->prepare("INSERT INTO anunciosMaestro (nombreCurso, descripcion, fecha, hora) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $errorMsg = urlencode("Error en la preparación de la consulta: " . $conn->error);
            header("Location: anunciarMaestro.php?status=error&message=$errorMsg");
            exit();
        }

        $stmt->bind_param("ssss", $nombreCurso, $descripcion, $fecha, $hora);
        if ($stmt->execute()) {
            // Redirigir con éxito
            header("Location: anunciar.php?status=success");
        } else {
            // Redirigir con error
            $errorMsg = urlencode("Error al registrar el anuncio.");
            header("Location: anunciar.php?status=error&message=$errorMsg");
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
    <link rel="stylesheet" href="../../css/DOCENTECSS/anunciarMaestroPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/anunciarMaestroMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css">
</head>
<body>
<div class="notification" id="notification"></div> <!-- Contenedor de la notificación -->
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?>
    <?php
    // Verificar si se ha recibido el nombre del curso desde el formulario
    if (isset($_POST['nombreCurso'])) {
        $nombreCurso = $_POST['nombreCurso'];
    ?>
        <h2 class="anuncio" style="margin-left: 180px; color: white;" >Crear un anuncio para el curso: <?php echo htmlspecialchars($nombreCurso); ?></h2>
        <div class="form-container">
        <form action="anunciarMaestro.php" method="POST">
            <input type="hidden" name="nombreCurso" value="<?php echo htmlspecialchars($nombreCurso); ?>">
            <textarea name="descripcion" id="descripcion" rows="4" cols="50" required></textarea><br>
            <input type="submit" value="Enviar Anuncio">
        </form>
        </div>
    <?php
    } else {
        echo "No se ha seleccionado ningún curso.";
    }
    ?>
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