<?php
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

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
$conn->close();

// Redirigir basado en el rol del usuario
switch ($user_role) {
    case 'ALUMNO':
        header("Location: UIAlumno.php"); // Redirige a la interfaz de administrador
        exit();
    case 'MAESTRO':
        header("Location: UIMaestro.php"); // Redirige a la interfaz de maestro
        exit();
    case 'APODERADO':
        header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreEspecialidad = $_POST['nombreEspecialidad'] ?: null;

    // Validar los datos recibidos
    if (empty($nombreEspecialidad)) {
        header("Location: agregar_especialidad.php?status=error&message=" . urlencode("El nombre de la especialidad es requerido."));
        exit();
    }

    // Insertar los datos en la base de datos
    $sql = "INSERT INTO especialidad (nombreEspecialidad) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombreEspecialidad);

    if ($stmt->execute()) {
        header("Location: agregar_especialidad.php?status=success");
    } else {
        header("Location: agregar_especialidad.php?status=error&message=" . urlencode($conn->error));
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - C.E.B.E</title>
    <link rel="stylesheet" href="../css/agregar_especialidadPC.css">
    <link rel="stylesheet" href="../css/agregar_especialidadMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebarADMINPC.css">
    <link rel="stylesheet" href="../css/sidebarADMINMobile.css">
</head>
<body>
    <div class="notification" id="notification"></div> <!-- Contenedor de la notificación -->
    <div class="container">
        <!-- Incluir la Sidebar -->
        <?php include 'sidebarADMIN.php'; ?>

       
        <!-- Main Content -->
        <main class="main-content">
            <section>
                <h2>Agregar Especialidad</h2>
                <form action="agregar_especialidad.php" method="POST">
                    <input type="text" name="nombreEspecialidad" placeholder="Nombre de la especialidad" required>
                    <button type="submit">Agregar Especialidad</button>
                </form>
            </section>
        </main>
    </div>
    <script>
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            var status = urlParams.get('status');
            var message = urlParams.get('message') || '';

            var notification = document.getElementById('notification');
            
            if (status === 'success') {
                notification.innerText = 'Especialidad agregada exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al agregar usuario: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }
        };
    </script>
</body>
</html>