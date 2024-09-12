<?php
session_start();

// Incluir la conexión a la base de datos
include '../conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
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
        header("Location: ../ALUMNOPHP/UIAlumno.php");
        exit();
    case 'DOCENTE':
        header("Location: ../DOCENTEPHP/UIMaestro.php");
        exit();
    case 'APODERADO':
        header("Location: ../APODERADOPHP/UIApoderado.php");
        exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['estudiante']; // Agregar la variable para el estudiante
    $idGrado = $_POST['grado'];

    // Validar los datos recibidos
    if (empty($idUsuario) || empty($idGrado)) {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("Estudiante o grado no seleccionado."));
        exit();
    }

    // Verificar si el estudiante ya está matriculado en el grado
    $checkMatriculaSql = "SELECT idUsuario FROM alumnosmatriculados WHERE idUsuario = ?";
    $stmt = $conn->prepare($checkMatriculaSql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("El estudiante ya está matriculado."));
        exit();
    }
    $stmt->close();

    // Obtener el cupo actual del grado
    $cupoSql = "SELECT cupos FROM grados WHERE idGrado = ?";
    $stmt = $conn->prepare($cupoSql);
    $stmt->bind_param("i", $idGrado);
    $stmt->execute();
    $stmt->bind_result($cupos);
    $stmt->fetch();
    $stmt->close();

    // Verificar si hay cupos disponibles
    if ($cupos <= 0) {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode("No hay cupos disponibles para este grado."));
        exit();
    }

    // Insertar los datos en la tabla alumnosmatriculados
    $sql = "INSERT INTO alumnosmatriculados (idUsuario, idGrado, fechaMatricula) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idUsuario, $idGrado);

    if ($stmt->execute()) {
        // Actualizar el cupo del grado
        $updateCuposSql = "UPDATE grados SET cupos = cupos - 1 WHERE idGrado = ?";
        $stmt = $conn->prepare($updateCuposSql);
        $stmt->bind_param("i", $idGrado);
        $stmt->execute();

        header("Location: matricularEstudiante.php?status=success&message=" . urlencode("Grado matriculado exitosamente."));
    } else {
        header("Location: matricularEstudiante.php?status=error&message=" . urlencode($conn->error));
    }

    $stmt->close();
    $conn->close();
}

// Eliminar matrícula
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idMatricula = $_GET['id'];

    // Obtener el idGrado asociado a la matrícula
    $getGradoSql = "SELECT idGrado FROM alumnosmatriculados WHERE idMatricula = ?";
    $stmt = $conn->prepare($getGradoSql);
    $stmt->bind_param("i", $idMatricula);
    $stmt->execute();
    $stmt->bind_result($idGrado);
    $stmt->fetch();
    $stmt->close();

    // Eliminar la matrícula
    $deleteSql = "DELETE FROM alumnosmatriculados WHERE idMatricula = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $idMatricula);
    $stmt->execute();

    // Recuperar el cupo del grado
    $updateCuposSql = "UPDATE grados SET cupos = cupos + 1 WHERE idGrado = ?";
    $stmt = $conn->prepare($updateCuposSql);
    $stmt->bind_param("i", $idGrado);
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
    <title>Matricular Grado</title>
    <link rel="stylesheet" href="../../css/ADMINCSS/matricularEstudiantePC.css">
    <link rel="stylesheet" href="../../css/ADMINCSS/matricularEstudianteMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/ADMINCSS/sidebarADMINPC.css">
    <link rel="stylesheet" href="../../css/ADMINCSS/sidebarADMINMobile.css">
</head>
<body>
    <div class="notification" id="notification"></div>
    <div class="container">
        <!-- Incluir la Sidebar -->
        <?php include 'sidebarADMIN.php'; ?>
        <!-- Main Content -->
        <main class="main-content">
            <section>
                <h2 style="color: white;">Matricular Estudiante</h2>
                <form action="matricularEstudiante.php" method="POST">
                    <label for="estudiante" style="color: white;">Estudiante:</label>
                    <select name="estudiante" id="estudiante" required>
                        <option value="">Seleccione un estudiante</option>
                        <?php
                        // Obtener la lista de estudiantes con nombres y apellidos concatenados
                        $estudiantesSql = "SELECT idUsuario, CONCAT(nombres, ' ', apellidos) AS nombreCompleto FROM usuarios WHERE rol = 'ESTUDIANTE'";
                        $estudiantesResult = $conn->query($estudiantesSql);

                        while ($row = $estudiantesResult->fetch_assoc()) {
                            echo '<option value="' . $row['idUsuario'] . '">' . $row['nombreCompleto'] . '</option>';
                        }
                        ?>
                    </select>

                    <div id="matriculacion">
                    <strong><p>Alumno Seleccionado: <span id="estudianteSeleccionado" style="color: white;"></span></p></strong>
                    </div>

                    <label for="grado" style="color: white;">Grado:</label>
                    <select name="grado" id="grado" required>
                        <option value="">Seleccione un grado</option>
                        <?php
                        // Obtener la lista de grados
                        $sql = "SELECT idGrado, nombreGrado, nivel, seccion, cupos FROM grados";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            $grado = $row['nombreGrado'] . ' - ' . $row['nivel'] . ' - Sección ' . $row['seccion'];
                            $cupo = $row['cupos'];
                            echo '<option value="' . $row['idGrado'] . '">' . $grado . ' (Cupos disponibles: ' . $cupo . ')</option>';
                        }
                        ?>
                    </select>

                    <div id="matriculacion">
                    <strong><p>Grado Seleccionado: <span id="gradoSeleccionado" style="color: white;"></span></p></strong>
                        <button type="submit">Matricular</button>
                    </div>
                </form>
                <br>
                <h2 style="color: white;">Matrículas Actuales</h2>

                <table id="matriculasTabla">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Grado</th>
                        <th>Nivel</th>
                        <th>Sección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php
                        // Obtener la lista de matrículas con el nombre del estudiante concatenado
                        $sql = "SELECT a.idMatricula, CONCAT(u.nombres, ' ', u.apellidos) AS nombreEstudiante, g.nombreGrado, g.nivel, g.seccion
                                FROM alumnosmatriculados a
                                JOIN grados g ON a.idGrado = g.idGrado
                                JOIN usuarios u ON a.idUsuario = u.idUsuario";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td style="color: white;">' . $row['nombreEstudiante'] . '</td>';
                            echo '<td style="color: white;">' . $row['nombreGrado'] . '</td>';
                            echo '<td style="color: white;">' . $row['nivel'] . '</td>';
                            echo '<td style="color: white;">' . $row['seccion'] . '</td>';
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
            document.getElementById('grado').addEventListener('change', function() {
                var grado = this.options[this.selectedIndex].text;
                document.getElementById('gradoSeleccionado').innerText = grado;
            });

            document.getElementById('estudiante').addEventListener('change', function() {
                var estudiante = this.options[this.selectedIndex].text;
                document.getElementById('estudianteSeleccionado').innerText = estudiante;
            });
        };
    </script>
</body>
</html>
