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

$stmt->bind_param("s", $username); // Cambia el tipo de parámetro a 's' para string
$stmt->execute();
$stmt->bind_result($user_role);
$stmt->fetch();
$stmt->close();

// Redirigir basado en el rol del usuario
switch ($user_role) {
    case 'DOCENTE':
        header("Location: ../DOCENTEPHP/UIMaestro.php");
        exit();
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php");
        exit();
    case 'APODERADO':
        header("Location: ../APODERADO/UIApoderado.php");
        exit();
}

// Obtener el idUsuario de la tabla usuarios
$user = $_SESSION['user'];
$queryUsuario = "SELECT idUsuario FROM usuarios WHERE username = ?";
$stmtUsuario = $conn->prepare($queryUsuario);
$stmtUsuario->bind_param("s", $user);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows > 0) {
    $usuario = $resultUsuario->fetch_assoc();
    $idUsuario = $usuario['idUsuario'];
} else {
    die("Usuario no encontrado.");
}

// Verificar si se ha enviado el idModulo
if (isset($_POST['idModulo'])) {
    $idModulo = $_POST['idModulo'];
} elseif (isset($_GET['idModulo'])) {
    $idModulo = $_GET['idModulo'];
} else {
    die("No se ha seleccionado ningún módulo.");
}

// Obtener el archivo del módulo
$queryArchivo = "SELECT idArchivo, nombre, tipo, contenido FROM archivos WHERE idModulo = ?";
$stmtArchivo = $conn->prepare($queryArchivo);
$stmtArchivo->bind_param("i", $idModulo);
$stmtArchivo->execute();
$resultArchivo = $stmtArchivo->get_result();

// Si hay un archivo, lo asignamos a la variable $archivo
if ($resultArchivo->num_rows > 0) {
    $archivo = $resultArchivo->fetch_assoc();
} else {
    $archivo = null;
}

// Obtener las actividades asignadas al módulo
$queryActividades = "
    SELECT idActividad, titulo, descripcion, fecha, fecha_vencimiento 
    FROM actividades 
    WHERE idModulo = ?";
$stmtActividades = $conn->prepare($queryActividades);
$stmtActividades->bind_param("i", $idModulo);
$stmtActividades->execute();
$resultActividades = $stmtActividades->get_result();

// Verificar si hay un mensaje de éxito o error en la URL
$mensaje = '';
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] == 'exito') {
        $mensaje = '<p style="color: white;">Tarea subida exitosamente.</p>';
    } elseif ($_GET['mensaje'] == 'error') {
        $mensaje = '<p style="color: red;">Error al subir la tarea.</p>';
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material y Actividades del Módulo</title>
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verModuloAlumnoPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/verModuloAlumnoMobile.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOPC.css">
    <link rel="stylesheet" href="../../css/ALUMNOCSS/sidebarALUMNOMobile.css">
    <style>
        .notificacion {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: green;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarAlumno.php'; ?>

    <!-- Mostrar notificación -->
    <?php if (!empty($mensaje)): ?>
        <div class="notificacion" id="notificacion">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar el archivo del módulo -->
    <div class="material-modulo">
        <h2>Material del Módulo</h2>
        <?php if ($archivo): ?>
            <p><strong>Nombre del archivo:</strong> <?php echo htmlspecialchars($archivo['nombre']); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($archivo['tipo']); ?></p>
            <a href="descargarArchivo.php?idArchivo=<?php echo $archivo['idArchivo']; ?>">Descargar archivo</a>
        <?php else: ?>
            <p>No hay material disponible para este módulo.</p>
        <?php endif; ?>
    </div>

    <!-- Mostrar las actividades asignadas -->
    <div class="actividades-modulo">
        <h2>Actividades Asignadas</h2>
        <?php if ($resultActividades->num_rows > 0): ?>
            <?php while ($actividad = $resultActividades->fetch_assoc()): ?>
                <div class="actividad">
                    <h3><?php echo htmlspecialchars($actividad['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars($actividad['descripcion']); ?></p>
                    <p><strong>Fecha de asignación:</strong> <?php echo htmlspecialchars($actividad['fecha']); ?></p>
                    <p><strong>Fecha de vencimiento:</strong> <?php echo htmlspecialchars($actividad['fecha_vencimiento']); ?></p>

                    <?php
                    // Verificar si el usuario ya ha subido una tarea para esta actividad
                    $queryTareaSubida = "
                        SELECT idTarea FROM tareas_alumnos 
                        WHERE idActividad = ? AND idUsuario = ?";
                    $stmtTareaSubida = $conn->prepare($queryTareaSubida);
                    $stmtTareaSubida->bind_param("ii", $actividad['idActividad'], $idUsuario);
                    $stmtTareaSubida->execute();
                    $resultTareaSubida = $stmtTareaSubida->get_result();

                    // Si ya se ha subido una tarea, no mostrar el formulario
                    if ($resultTareaSubida->num_rows > 0):
                    ?>
                        <p style="color: green;">Ya has subido una tarea para esta actividad.</p>
                    <?php else: ?>
                        <form action="subirTarea.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="idActividad" value="<?php echo $actividad['idActividad']; ?>">
                            <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>">
                            <input type="hidden" name="idModulo" value="<?php echo $idModulo; ?>">
                            <input type="file" style="color: black;" name="archivo" required>
                            <button type="submit" name="subirTarea">Subir Tarea</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay actividades asignadas para este módulo.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const notificacion = document.getElementById('notificacion');
            if (notificacion) {
                notificacion.style.display = 'block';

                setTimeout(() => {
                    notificacion.style.opacity = '0';
                    setTimeout(() => {
                        notificacion.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>
