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

// Verificar si se ha recibido el id del módulo
if (isset($_POST['idModulo'])) {
    $idModulo = $_POST['idModulo'];
} else {
    die("No se ha proporcionado un ID de módulo válido.");
}

// Procesar el formulario de nueva actividad
if (isset($_POST['nuevaActividad'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    $queryActividad = "INSERT INTO actividades (titulo, descripcion, fecha, fecha_vencimiento, idModulo) 
                        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($queryActividad);
    $stmt->bind_param("ssssi", $titulo, $descripcion, $fecha, $fecha_vencimiento, $idModulo);
    
    if ($stmt->execute()) {
        echo "<script>alert('Actividad agregada correctamente.');</script>";
    } else {
        echo "<script>alert('Error al agregar la actividad.');</script>";
    }
    $stmt->close();
}

// Procesar el formulario de subir archivo
if (isset($_POST['subirMaterial'])) {
    $archivoNombre = $_FILES['archivo']['name'];
    $archivoTipo = $_FILES['archivo']['type'];
    $archivoContenido = file_get_contents($_FILES['archivo']['tmp_name']); // Leer el contenido del archivo

    $queryArchivo = "INSERT INTO archivos (nombre, tipo, contenido, idModulo) 
                     VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($queryArchivo);
    $stmt->bind_param("sssi", $archivoNombre, $archivoTipo, $archivoContenido, $idModulo);
    
    if ($stmt->execute()) {
        echo "<script>alert('Archivo subido correctamente.');</script>";
    } else {
        echo "<script>alert('Error al subir el archivo.');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Actividad y Material - C.E.B.E</title>
    <link rel="stylesheet" href="../../css/DOCENTECSS/agregarMaterialModuloPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/agregarMaterialModuloMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css">
</head>
<body>
    <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?>
    <!-- Contenedor principal para los formularios -->
    <div class="form-container">
        <!-- Formulario para agregar nueva actividad -->
        <div class="form-box">
            <h2 class="actividad">Nueva Actividad</h2>
            <form action="agregarMaterialModulo.php" method="POST">
                <input type="hidden" name="idModulo" value="<?php echo htmlspecialchars($idModulo); ?>">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" required><br><br>
                <label for="descripcion">Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" required><br><br>
                <label for="fecha">Fecha de inicio:</label>
                <input type="date" id="fecha" name="fecha" required><br><br>
                <label for="fecha_vencimiento">Fecha de vencimiento:</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required><br><br>
                <button type="submit" name="nuevaActividad">Agregar Actividad</button>
            </form>
        </div>

        <!-- Formulario para agregar material -->
        <div class="form-box">
            <h2 class="material">Agregar Material</h2>
            <form action="agregarMaterialModulo.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="idModulo" value="<?php echo htmlspecialchars($idModulo); ?>">
                <label for="archivo">Selecciona un archivo:</label>
                <input type="file" id="archivo" name="archivo" required><br><br>
                <button type="submit" name="subirMaterial">Subir Archivo</button>
            </form>
        </div>
    </div>
</body>
</html>