<?php
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

// Incluir la conexión a la base de datos
include '../../php/conexion.php'; // Asegúrate de que la ruta es correcta

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
            header("Location: ../DOCENTEPHP/UIMaestro.php"); // Redirige a la interfaz de administrador
            exit();
        case 'ESTUDIANTE':
            header("Location: ../ALUMNOPHP/UIAlumno.php"); // Redirige a la interfaz de maestro
            exit();
        case 'APODERADO':
            header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
            exit();
    }
?>
 <aside class="sidebar">
            <div class="logo-section">
                <a href="UIAdmin.php"><img src="../../img/C.E.B.E.LOGO.png" alt="Logo" class="logo-img"></a>
                <a href="UIAdmin.php"><h2>C.E.B.E</h2></a>
                <style>
                    a {
                        text-decoration: none; /* Elimina el subrayado */
                    }
                </style>
            </div>
            <div class="divider"></div>
            <nav class="menu">
                <ul>
                    <li><a href="agregar_usuario.php"><img src="../../img/agregarusuario.png" alt="Perfil" class="menu-icon">Agregar Usuarios</a></li>
                    <li><a href="agregar_especialidad.php"><img src="../../img/addespecialidad.jpg" alt="Perfil" class="menu-icon">Agregar Especialidad</a></li>
                    <li><a href="agregar_curso.php"><img src="../../img/addcurso.jpg" alt="Perfil" class="menu-icon">Agregar Cursos</a></li>
                    <li><a href="asignarEspecialidadDocente.php"><img src="../../img/asignar.png" alt="Perfil" class="menu-icon">Asignar Especialidad Docente</a></li>
                    <li><a href="matricularEstudiante.php"><img src="../../img/matricular.png" alt="Perfil" class="menu-icon">Matricular Estudiante</a></li>
                    <li><a href="../logout.php"><img src="../../img/logout.png" alt="Perfil" class="menu-icon">Cerrar Sesion</a></li>
                </ul>
            </nav>
</aside>
