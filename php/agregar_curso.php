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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombreCurso = $_POST['nombreCurso'] ?: null;
        $idEspecialidad = $_POST['idEspecialidad'] ?: null;
        
        // Validar los datos recibidos
        if (empty($nombreCurso) || empty($idEspecialidad)) {
            header("Location: agregar_curso.php?status=error&message=" . urlencode("Todos los campos son requeridos."));
            exit();
        }

        // Insertar el curso en la base de datos
        $sqlCurso = "INSERT INTO cursos (nombreCurso, idEspecialidad, cupos) VALUES (?, ?, ?)";
        $stmtCurso = $conn->prepare($sqlCurso);
        $cupos = 10; // Valor por defecto
        $stmtCurso->bind_param("ssi", $nombreCurso, $idEspecialidad, $cupos);

        if ($stmtCurso->execute()) {
            $idCurso = $stmtCurso->insert_id; // Obtener el ID del curso recién insertado

            // Insertar los módulos en la base de datos
            $sqlModulo = "INSERT INTO modulos (nombre, idCurso) VALUES (?, ?)";
            $stmtModulo = $conn->prepare($sqlModulo);
            
            // Lista de nombres de módulos a agregar
            $modulos = [
                'Módulo 1',
                'Módulo 2',
                'Módulo 3',
                'Módulo 4',
                'Módulo 5',
                'Módulo 6'
            ];

            foreach ($modulos as $modulo) {
                $stmtModulo->bind_param("si", $modulo, $idCurso);
                $stmtModulo->execute();
            }

            header("Location: agregar_curso.php?status=success");
        } else {
            header("Location: agregar_curso.php?status=error&message=" . urlencode($conn->error));
        }

        $stmtCurso->close();
        $stmtModulo->close();
        $conn->close();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Curso - C.E.B.E</title>
    <link rel="stylesheet" href="../css/agregar_cursoPC.css">
    <link rel="stylesheet" href="../css/agregar_cursoMobile.css">
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
                <h2>Agregar Curso</h2>
                <form action="agregar_curso.php" method="POST">
                    <input type="text" name="nombreCurso" placeholder="Nombre del curso" required>

                    <label for="idEspecialidad" style="color:white">Especialidad:</label>
                    <select name="idEspecialidad" id="idEspecialidad" required>
                        <?php
                        // Obtener especialidades para llenar el combo box
                        $sql = "SELECT idEspecialidad, nombreEspecialidad FROM especialidad";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"{$row['idEspecialidad']}\">{$row['nombreEspecialidad']}</option>";
                        }
                        ?>
                    </select>

                    <button type="submit">Agregar Curso</button>
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
                notification.innerText = 'Curso agregado exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al agregar curso: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }
        };
    </script>
</body>
</html>