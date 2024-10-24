<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../conexion.php'; 

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
    $archivo = null; // Asignar null si no hay archivo
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
                top: 20px; /* Espacio desde la parte superior */
                left: 50%;
                transform: translateX(-50%);
                background-color: green; /* Fondo verde */
                color: white; /* Texto blanco */
                padding: 10px 20px; /* Espaciado interno */
                border-radius: 5px; /* Bordes curvos */
                z-index: 1000; /* Asegura que la notificación esté por encima de otros elementos */
                display: none; /* Oculta por defecto */
                opacity: 1; /* Totalmente visible */
                transition: opacity 0.5s ease; /* Transición suave para el desvanecimiento */
            }
        </style>
    </head>
    <style>
        .notificacion {
            position: fixed;
            top: 20px; /* Espacio desde la parte superior */
            left: 50%;
            transform: translateX(-50%);
            background-color: green; /* Fondo verde */
            color: white; /* Texto blanco */
            padding: 10px 20px; /* Espaciado interno */
            border-radius: 5px; /* Bordes curvos */
            z-index: 1000; /* Asegura que la notificación esté por encima de otros elementos */
            display: none; /* Oculta por defecto */
            opacity: 1; /* Totalmente visible */
            transition: opacity 0.5s ease; /* Transición suave para el desvanecimiento */
        }


        .notification.show {
            display: block;
        }
    </style>
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
                        <form action="subirTarea.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="idActividad" value="<?php echo $actividad['idActividad']; ?>">
                            <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>">
                            <input type="hidden" name="idModulo" value="<?php echo $idModulo; ?>">
                            <input type="file" style="color: black;" name="archivo" required>
                            <button type="submit" name="subirTarea">Subir Tarea</button>
                        </form>
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
                    notificacion.style.display = 'block'; // Muestra la notificación

                    // Desvanecer la notificación después de 3 segundos
                    setTimeout(() => {
                        notificacion.style.opacity = '0'; // Comienza el desvanecimiento
                        setTimeout(() => {
                            notificacion.style.display = 'none'; // Oculta la notificación después del desvanecimiento
                        }, 500); // Espera a que la transición de desvanecimiento termine
                    }, 3000); // Espera 3 segundos
                }
            });
        </script>
    </body>
</html>