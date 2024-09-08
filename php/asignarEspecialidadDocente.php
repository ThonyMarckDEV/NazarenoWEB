<?php
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

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
    case 'DOCENTE':
        header("Location: UIMaestro.php"); // Redirige a la interfaz de maestro
        exit();
    case 'APODERADO':
        header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

// Verificar si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idEspecialidad = $_POST['especialidad'];
    $idDocente = $_POST['docente'];

    // Validar los datos recibidos
    if (empty($idEspecialidad) || empty($idDocente)) {
        header("Location: asignarEspecialidadDocente.php?status=error&message=" . urlencode("Especialidad o docente no seleccionado."));
        exit();
    }

    // Verificar si la combinación ya existe en la tabla especialidaddocente
    $checkSql = "SELECT COUNT(*) FROM especialidaddocente WHERE idEspecialidad = ? AND idDocente = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $idEspecialidad, $idDocente);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header("Location: asignarEspecialidadDocente.php?status=error&message=" . urlencode("Este docente ya tiene asignada esta especialidad."));
        exit();
    }

    // Insertar los datos en la tabla especialidaddocente
    $sql = "INSERT INTO especialidaddocente (idEspecialidad, idDocente) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idEspecialidad, $idDocente);

    if ($stmt->execute()) {
        header("Location: asignarEspecialidadDocente.php?status=success&message=" . urlencode("Especialidad asignada al docente exitosamente."));
    } else {
        header("Location: asignarEspecialidadDocente.php?status=error&message=" . urlencode($conn->error));
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
    <title>Asignar Especialidad a Docente</title>
    <link rel="stylesheet" href="../css/asignarEspecialidadPC.css">
    <link rel="stylesheet" href="../css/asignarEspecialidadMobile.css">
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
                <h2>Asignar Especialidad a Docente</h2>
                <form action="asignarEspecialidadDocente.php" method="POST">
                    <label for="especialidad" style="color:white">Especialidad:</label>
                    <select name="especialidad" id="especialidad" required>
                        <option value="">Seleccione una especialidad</option>
                        <?php
                        // Conectar a la base de datos
                        include 'conexion.php';
                        
                        // Obtener la lista de especialidades
                        $sql = "SELECT idEspecialidad, nombreEspecialidad FROM especialidad";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['idEspecialidad'] . '">' . $row['nombreEspecialidad'] . '</option>';
                        }
                        ?>
                    </select>

                    <label for="docente" style="color:white">Docente:</label>
                    <select name="docente" id="docente" required>
                        <option value="">Seleccione un docente</option>
                        <?php
                        // Obtener la lista de docentes
                        $sql = "SELECT idUsuario, CONCAT(nombres, ' ', apellidos) AS nombreCompleto FROM usuarios WHERE rol = 'DOCENTE'";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['idUsuario'] . '">' . $row['nombreCompleto'] . '</option>';
                        }
                        ?>
                    </select>

                    <div id="asignacion">
                        <p>Especialidad Seleccionada: <span id="especialidadSeleccionada" style="color:white"></span></p>
                        <p>Docente Seleccionado: <span id="docenteSeleccionado" style="color:white"></span></p>
                        <button type="submit">Asignar</button>
                    </div>
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
                notification.innerText = 'Especialidad asignada exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al asignar especialidad: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }

            // Mostrar selección
            document.getElementById('especialidad').addEventListener('change', function() {
                var especialidad = this.options[this.selectedIndex].text;
                document.getElementById('especialidadSeleccionada').innerText = especialidad;
            });

            document.getElementById('docente').addEventListener('change', function() {
                var docente = this.options[this.selectedIndex].text;
                document.getElementById('docenteSeleccionado').innerText = docente;
            });
        };
    </script>
</body>
</html>
