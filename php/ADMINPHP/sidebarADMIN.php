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
<script>
    // Variables para rastrear la inactividad
    let tiempoInactividad = 0;
    let sesionCerrada = false;  // Bandera para evitar múltiples redirecciones

    // Función para restablecer el temporizador de inactividad
    function reiniciarTiempoInactividad() {
        tiempoInactividad = 0;  // Restablecer el contador de inactividad
    }

    // Función para verificar el estado del usuario
    function verificarEstadoUsuario() {
        fetch('../verificar_estado.php', {  // Verificar si la sesión sigue activa
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            cache: 'no-cache'
        })
        .then(response => response.text())
        .then(data => {
            if (data === "loggedOff" && !sesionCerrada) {
                // Redirigir a logout.php si el estado es "loggedOff"
                window.location.href = '../logout.php';
            }
        })
        .catch(error => console.error('Error al verificar el estado del usuario:', error));
    }

    // Función para redirigir a logout.php por inactividad
    function cerrarSesionPorInactividad() {
        if (!sesionCerrada) {  // Solo redirigir si no se ha cerrado la sesión aún
            sesionCerrada = true;  // Marcar la sesión como cerrada
            window.location.href = '../logout.php';  // Redirigir a logout.php
        }
    }

    // Incrementar el tiempo de inactividad cada segundo
    setInterval(() => {
        tiempoInactividad += 1;

        if (tiempoInactividad >= 30) { // 30 segundos de inactividad
            cerrarSesionPorInactividad();
        }
    }, 1000);

    // Escuchar eventos de actividad (teclado o mouse) y reiniciar el temporizador
    window.addEventListener('mousemove', reiniciarTiempoInactividad);
    window.addEventListener('keydown', reiniciarTiempoInactividad);

    // Ejecutar la verificación de sesión al cargar la página
    verificarEstadoUsuario();

    // Ejecutar la verificación de sesión cada 3 segundos
    setInterval(verificarEstadoUsuario, 3000);

</script>
