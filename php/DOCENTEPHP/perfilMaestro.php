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
    case 'ADMIN':
        header("Location: ../ADMINPHP/UIAdmin.php"); // Redirige a la interfaz de administrador
        exit();
    case 'ESTUDIANTE':
        header("Location: ../ALUMNOPHP/UIAlumno.php"); // Redirige a la interfaz de maestro
        exit();
    case 'APODERADO':
        header("Location: ../APODERADOPHP/UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="../../css/DOCENTECSS/perfilMaestroPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/perfilMaestroMobile.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROPC.css">
    <link rel="stylesheet" href="../../css/DOCENTECSS/sidebarMAESTROMobile.css">
</head>
<body>
    <div class="notification" id="notification"></div> <!-- Contenedor de la notificación -->
     <!-- Incluir la Sidebar -->
    <?php include 'sidebarMAESTRO.php'; ?> 
    <form id="perfilForm" method="POST" class="frmMain" action="procesarPerfilMaestro.php" enctype="multipart/form-data">
        <div class="perfil-info">
            <div class="perfil-foto">
                <?php
                    // Mostrar la foto de perfil si está disponible
                    $sql_foto_perfil = "SELECT perfil FROM usuarios WHERE username = ?";
                    $stmt_foto_perfil = $conn->prepare($sql_foto_perfil);
                    $stmt_foto_perfil->bind_param("s", $username);
                    $stmt_foto_perfil->execute();
                    $result_foto_perfil = $stmt_foto_perfil->get_result();

                    if ($result_foto_perfil->num_rows > 0) {
                        $row_foto_perfil = $result_foto_perfil->fetch_assoc();
                        $foto_perfil = $row_foto_perfil['perfil'];

                        if ($foto_perfil) {
                            // Decodificar la imagen binaria y mostrarla
                            $foto_base64 = base64_encode($foto_perfil);
                            $imagen_src = "data:image/jpeg;base64," . $foto_base64;
                            echo "<img src='$imagen_src' alt='Foto de perfil' class='profile-pic'>";
                        } else {
                            echo "No hay foto de perfil disponible.";
                        }
                    }
                    
                    $stmt_foto_perfil->close();
                ?>
                <input type="file" id="foto" name="foto" accept="image/*"><br>
            </div>

            <div class="perfil-datos">
                <?php
                    // Consultar los datos del usuario
                    $sql_datos_usuario = "SELECT nombres, apellidos, edad, dni, correo, nacimiento, sexo, direccion, telefono, departamento FROM usuarios WHERE username = ?";
                    $stmt_datos_usuario = $conn->prepare($sql_datos_usuario);
                    $stmt_datos_usuario->bind_param("s", $username);
                    $stmt_datos_usuario->execute();
                    $result_datos_usuario = $stmt_datos_usuario->get_result();

                    if ($result_datos_usuario->num_rows > 0) {
                        $datos_usuario = $result_datos_usuario->fetch_assoc();

                        // Mostrar campos que no se pueden editar
                        foreach (['nombres', 'apellidos', 'edad', 'dni'] as $campo) {
                            echo "<label  style='color:white;'  for='$campo'>$campo:</label><br>";
                            echo "<input type='text' id='$campo' name='$campo' value='{$datos_usuario[$campo]}' readonly><br>";
                        }

                        foreach (['correo', 'nacimiento', 'sexo', 'direccion', 'telefono', 'departamento'] as $campo) {
                            echo "<label style='color:white;' for='$campo'>$campo:</label><br>";
                        
                            if ($campo == 'nacimiento') {
                                // Si el campo es 'nacimiento', usar el input tipo 'date'
                                echo "<input type='date' id='$campo' name='$campo' value='{$datos_usuario[$campo]}' disabled>";
                            } elseif ($campo == 'sexo') {
                                // Si el campo es 'sexo', usar un combobox con opciones
                                echo "<select id='$campo' name='$campo' disabled class='cbxSexo'>";
                                echo "<option value='Masculino'" . ($datos_usuario[$campo] == 'Masculino' ? " selected" : "") . ">Masculino</option>";
                                echo "<option value='Femenino'" . ($datos_usuario[$campo] == 'Femenino' ? " selected" : "") . ">Femenino</option>";
                                echo "</select>";
                                // Recuadro de solo lectura que muestra el valor seleccionado
                                echo "<input type='text' value='{$datos_usuario[$campo]}' readonly>";
                            } elseif ($campo == 'departamento') {
                                // Si el campo es 'departamento', usar un combobox con todos los departamentos de Perú
                                $departamentos = ['Amazonas', 'Áncash', 'Apurímac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco', 'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto', 'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali'];
                        
                                echo "<select id='$campo' name='$campo' disabled class='departamento'>";
                                foreach ($departamentos as $dep) {
                                    echo "<option value='$dep'" . ($datos_usuario[$campo] == $dep ? " selected" : "") . ">$dep</option>";
                                }
                                echo "</select>";
                                // Recuadro de solo lectura que muestra el valor seleccionado
                                echo "<input type='text' value='{$datos_usuario[$campo]}' readonly>";
                            } else {
                                // Para otros campos (correo, direccion, telefono), usar input tipo 'text' con 'disabled'
                                echo "<input type='text' id='$campo' name='$campo' value='{$datos_usuario[$campo]}' disabled>";
                            }
                        
                            // Botón para habilitar edición
                            echo "<button type='button' onclick='habilitarEdicion(\"$campo\")' class='editarBtn'>Editar</button><br>";
                        }
                    } else {
                        echo "No se encontró ningún usuario con el nombre de usuario proporcionado.";
                    }
                    $stmt_datos_usuario->close();
                    $conn->close();
                ?>
            </div>
        </div>
        <button type="submit" class="actualizarBtn">Actualizar perfil</button>
    </form>
    <script>

        function habilitarEdicion(campoId) {
            var campo = document.getElementById(campoId);
            campo.disabled = false;  // Habilitar el campo para edición
            campo.focus();  // Enfocar el campo para la edición
        }
    </script>
    <script>
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            var status = urlParams.get('status');
            var message = urlParams.get('message') || '';

            var notification = document.getElementById('notification');
            
            if (status === 'success') {
                notification.innerText = 'Datos agregados/actualizados exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al agregar/actualizar datos: ' + decodeURIComponent(message);
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            }
        };
    </script>
</body>
</html>