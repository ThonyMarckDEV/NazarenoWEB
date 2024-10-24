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
        header("Location: ../ALUMNOPHP/UIAlumno.php"); // Redirige a la interfaz de estudiante
        exit();
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php"); // Redirige a la interfaz de administrador
        exit();
    case 'APODERADO':
        header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

// Verificar si se ha enviado el idModulo
if (isset($_POST['idModulo'])) {
    $idModulo = $_POST['idModulo'];

    // Consulta para obtener las tareas de los alumnos no revisadas para el módulo seleccionado
    $queryTareas = "
        SELECT t.idTarea, t.archivo_nombre, t.archivo_contenido, t.nota, t.fecha_subida, t.revisado, a.titulo, u.nombres, u.apellidos
        FROM tareas_alumnos t
        JOIN actividades a ON t.idActividad = a.idActividad
        JOIN usuarios u ON t.idUsuario = u.idUsuario
        WHERE a.idModulo = ? AND t.revisado = 'NO'
    ";
    $stmt = $conn->prepare($queryTareas);
    $stmt->bind_param("i", $idModulo);
    $stmt->execute();
    $resultTareas = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas del Módulo - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/DOCENTECSS/tareasPC.css"> <!-- Asegúrate de tener este CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/tareasMobile.css"> <!-- Asegúrate de tener este CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css"> <!-- Asegúrate de tener este CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css"> <!-- Asegúrate de tener este CSS -->
    <script>
        // Función para habilitar la edición de la nota
        function editarNota(idTarea) {
            var notaElemento = document.getElementById('nota-' + idTarea);
            notaElemento.contentEditable = true;
            notaElemento.focus();
        }

        // Función para marcar la tarea como revisada
        function revisarTarea(idTarea) {
            var nota = document.getElementById('nota-' + idTarea).innerText;

            // Hacer una llamada AJAX para actualizar la tarea
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "revisarTarea.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Recargar la página para mostrar las tareas restantes no revisadas
                    location.reload();
                }
            };
            xhr.send("idTarea=" + idTarea + "&nota=" + nota);
        }
    </script>
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?>

    <div class="container">
        <h1  class="aea">Tareas del Módulo Seleccionado</h1>

        <div class="actividades-modulo">
            <?php if (isset($resultTareas) && $resultTareas->num_rows > 0): ?>
                <?php while ($tarea = $resultTareas->fetch_assoc()) { ?>
                    <div class="actividad">
                        <h3><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($tarea['nombres'] . ' ' . $tarea['apellidos']); ?></p>
                        
                        <!-- Link para descargar el archivo -->
                        <p><strong>Archivo:</strong> 
                            <a href="descargarArchivo.php?idTarea=<?php echo $tarea['idTarea']; ?>">Descargar</a>
                        </p>
                        
                        <p><strong>Fecha de Subida:</strong> <?php echo htmlspecialchars($tarea['fecha_subida']); ?></p>
                        
                        <!-- Nota editable -->
                        <p><strong>Nota:</strong> 
                            <span id="nota-<?php echo $tarea['idTarea']; ?>" onclick="editarNota(<?php echo $tarea['idTarea']; ?>)">
                                <?php echo htmlspecialchars($tarea['nota'] ?? '0'); ?>
                            </span>
                        </p>

                        <!-- Botón para revisar -->
                        <button onclick="revisarTarea(<?php echo $tarea['idTarea']; ?>)">Revisar</button>
                    </div>
                <?php } ?>
            <?php else: ?>
                <p>No hay tareas no revisadas para este módulo.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
