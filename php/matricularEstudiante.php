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

// Verificar si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idEstudiante = $_POST['estudiante'];
    $idCurso = $_POST['curso'];

    // Validar los datos recibidos
    if (empty($idEstudiante) || empty($idCurso)) {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("Estudiante o curso no seleccionado."));
        exit();
    }

    // Verificar si la combinación ya existe en la tabla alumnosmatriculados
    $checkSql = "SELECT COUNT(*) FROM alumnosmatriculados WHERE idUsuario = ? AND idCurso = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $idEstudiante, $idCurso);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("Este estudiante ya está matriculado en este curso."));
        exit();
    }

    // Obtener el cupo actual del curso
    $cupoSql = "SELECT cupos FROM cursos WHERE idCurso = ?";
    $stmt = $conn->prepare($cupoSql);
    $stmt->bind_param("i", $idCurso);
    $stmt->execute();
    $stmt->bind_result($cupos);
    $stmt->fetch();
    $stmt->close();

    // Verificar si hay cupos disponibles
    if ($cupos <= 0) {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("No hay cupos disponibles para este curso."));
        exit();
    }

    // Insertar los datos en la tabla alumnosmatriculados
    $sql = "INSERT INTO alumnosmatriculados (idUsuario, idCurso) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idEstudiante, $idCurso);

    if ($stmt->execute()) {
        // Actualizar el cupo del curso
        $updateCuposSql = "UPDATE cursos SET cupos = cupos - 1 WHERE idCurso = ?";
        $stmt = $conn->prepare($updateCuposSql);
        $stmt->bind_param("i", $idCurso);
        $stmt->execute();

        header("Location: matricularEstudiante.php?status=success&message=" . urlencode("Estudiante matriculado exitosamente."));
    } else {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode($conn->error));
    }

    $stmt->close();
    $conn->close();
}

// Eliminar matrícula
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idMatricula = $_GET['id'];

    // Obtener el idCurso asociado a la matrícula
    $getCursoSql = "SELECT idCurso FROM alumnosmatriculados WHERE idMatricula = ?";
    $stmt = $conn->prepare($getCursoSql);
    $stmt->bind_param("i", $idMatricula);
    $stmt->execute();
    $stmt->bind_result($idCurso);
    $stmt->fetch();
    $stmt->close();

    // Eliminar la matrícula
    $deleteSql = "DELETE FROM alumnosmatriculados WHERE idMatricula = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $idMatricula);
    $stmt->execute();

    // Recuperar el cupo del curso
    $updateCuposSql = "UPDATE cursos SET cupos = cupos + 1 WHERE idCurso = ?";
    $stmt = $conn->prepare($updateCuposSql);
    $stmt->bind_param("i", $idCurso);
    $stmt->execute();

    header("Location: matricularEstudiante.php?status=success&message=" . urlencode("Matrícula eliminada exitosamente."));
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matricular Estudiante</title>
    <link rel="stylesheet" href="../css/matricularEstudiantePC.css">
    <link rel="stylesheet" href="../css/matricularEstudianteMobile.css">
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
                <h2>Matricular Estudiante en Curso</h2>
                <form action="matricularEstudiante.php" method="POST">
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante" required>
                        <option value="">Seleccione un estudiante</option>
                        <?php
                        // Obtener la lista de estudiantes
                        $sql = "SELECT idUsuario, CONCAT(nombres, ' ', apellidos) AS nombreCompleto FROM usuarios WHERE rol = 'ESTUDIANTE'";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['idUsuario'] . '">' . $row['nombreCompleto'] . '</option>';
                        }
                        ?>
                    </select>

                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php
                        // Obtener la lista de cursos con los cupos disponibles
                        $sql = "SELECT idCurso, nombreCurso, cupos FROM cursos";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()) {
                            $idCurso = $row['idCurso'];
                            $nombreCurso = $row['nombreCurso'];
                            $cupos = $row['cupos'];
                            echo '<option value="' . $idCurso . '">' . $nombreCurso . ' (Cupos: ' . $cupos . ')</option>';
                        }
                        ?>
                    </select>

                    <div id="matriculacion">
                        <p>Estudiante Seleccionado: <span id="estudianteSeleccionado"></span></p>
                        <p>Curso Seleccionado: <span id="cursoSeleccionado"></span></p>
                        <button type="submit">Matricular</button>
                    </div>
                </form>
                        <br>
                <h2>Matriculas Actuales</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Obtener la lista de matrículas
                        $sql = "SELECT a.idMatricula, CONCAT(u.nombres, ' ', u.apellidos) AS nombreEstudiante, c.nombreCurso 
                                FROM alumnosmatriculados a 
                                JOIN usuarios u ON a.idUsuario = u.idUsuario 
                                JOIN cursos c ON a.idCurso = c.idCurso";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $row['nombreEstudiante'] . '</td>';
                            echo '<td>' . $row['nombreCurso'] . '</td>';
                            echo '<td><a href="matricularEstudiante.php?action=eliminar&id=' . $row['idMatricula'] . '">Eliminar</a></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
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
                notification.innerText = 'Operación realizada exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }

            // Mostrar selección
            document.getElementById('estudiante').addEventListener('change', function() {
                var estudiante = this.options[this.selectedIndex].text;
                document.getElementById('estudianteSeleccionado').innerText = estudiante;
            });

            document.getElementById('curso').addEventListener('change', function() {
                var curso = this.options[this.selectedIndex].text;
                document.getElementById('cursoSeleccionado').innerText = curso;
            });
        };
    </script>
</body>
</html>
