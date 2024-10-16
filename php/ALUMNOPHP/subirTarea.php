<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../conexion.php';

// Verificar si se ha enviado el formulario para subir la tarea
if (isset($_POST['subirTarea'])) {
    $idUsuario = $_POST['idUsuario']; // Ahora obtenemos el idUsuario del POST
    $idActividad = $_POST['idActividad'];
    $archivoNombre = $_FILES['archivo']['name'];
    $archivoTipo = $_FILES['archivo']['type'];
    $archivoContenido = file_get_contents($_FILES['archivo']['tmp_name']);
    $fechaSubida = date("Y-m-d H:i:s"); // Formato completo de datetime

    // Insertar la tarea en la tabla 'tareas_alumnos'
    $querySubirTarea = "
        INSERT INTO tareas_alumnos (idUsuario, idActividad, archivo_nombre, archivo_tipo, archivo_contenido, fecha_subida, revisado) 
        VALUES (?, ?, ?, ?, ?, ?, 'no')";
    $stmtSubirTarea = $conn->prepare($querySubirTarea);
    $stmtSubirTarea->bind_param("iissss", $idUsuario, $idActividad, $archivoNombre, $archivoTipo, $archivoContenido, $fechaSubida);

    if ($stmtSubirTarea->execute()) {
        // Redirigir de vuelta a verModuloAlumno.php con un mensaje de éxito
        header("Location: verModuloAlumno.php?mensaje=exito&idModulo=" . $_POST['idModulo']);
        exit();
    } else {
        // Redirigir de vuelta a verModuloAlumno.php con un mensaje de error
        header("Location: verModuloAlumno.php?mensaje=error&idModulo=" . $_POST['idModulo']);
        exit();
    }
} else {
    header("Location: verModuloAlumno.php");
    exit();
}
