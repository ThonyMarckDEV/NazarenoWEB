<?php
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?: null;
    $rol = $_POST['rol'] ?: null;
    $nombres = $_POST['nombres'] ?: null;
    $apellidos = $_POST['apellidos'] ?: null;
    $dni = $_POST['dni'] ?: null;
    $correo = $_POST['correo'] ?: null;
    $edad = $_POST['edad'] ?: null;
    $nacimiento = $_POST['nacimiento'] ?: null;
    $sexo = $_POST['sexo'] ?: null;
    $direccion = $_POST['direccion'] ?: null;
    $telefono = $_POST['telefono'] ?: null;
    $departamento = $_POST['departamento'] ?: null;
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hashear la contraseña
    $status = 'loggedOff';
    
    // Manejar la subida de la imagen de perfil
    if (isset($_FILES['perfil']) && $_FILES['perfil']['error'] === 0) {
        $perfil = $_FILES['perfil']['name'];
        $target_dir = "uploads/perfiles/"; // Directorio donde se guardarán las imágenes
        $target_file = $target_dir . basename($perfil);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validar el tipo de archivo (solo imágenes permitidas)
        $check = getimagesize($_FILES['perfil']['tmp_name']);
        if ($check !== false) {
            // Validar tamaño del archivo (por ejemplo, máximo 5MB)
            if ($_FILES['perfil']['size'] <= 5000000) {
                // Validar tipo de archivo permitido (jpg, jpeg, png, gif)
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Mover el archivo subido al directorio de destino
                    if (move_uploaded_file($_FILES['perfil']['tmp_name'], $target_file)) {
                        $perfil = $target_file;
                    } else {
                        $perfil = null;
                    }
                } else {
                    $perfil = null;
                }
            } else {
                $perfil = null;
            }
        } else {
            $perfil = null;
        }
    } else {
        $perfil = null; // En caso de que no se suba una imagen, dejar perfil en null
    }

    // Insertar los datos en la base de datos
    $sql = "INSERT INTO usuarios (username, rol, nombres, apellidos, dni, correo, edad, nacimiento, sexo, direccion, telefono, departamento, password, status, perfil)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssissssisss", $username, $rol, $nombres, $apellidos, $dni, $correo, $edad, $nacimiento, $sexo, $direccion, $telefono, $departamento, $password, $status, $perfil);

    if ($stmt->execute()) {
        header("Location: agregar_usuario.php?status=success");
    } else {
        header("Location: agregar_usuario.php?status=error&message=" . urlencode($conn->error));
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - C.E.B.E</title>
    <link rel="stylesheet" href="../css/agregar_usuarioPC.css">
    <link rel="stylesheet" href="../css/agregar_usuarioMobile.css">
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
                <h2>Agregar Usuario</h2>
                <form action="agregar_usuario.php" method="POST" enctype="multipart/form-data">
                    <!-- Formulario de usuario -->
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                    <input type="text" name="rol" placeholder="Rol (ADMIN, ESTUDIANTE, MAESTRO, APODERADO)" required>
                    <input type="text" name="nombres" placeholder="Nombres" required>
                    <input type="text" name="apellidos" placeholder="Apellidos" required>
                    <input type="text" name="dni" placeholder="DNI" required>
                    <input type="email" name="correo" placeholder="Correo electrónico (OPCIONAL)">
                    <input type="number" name="edad" placeholder="Edad">
                    <input type="date" name="nacimiento" placeholder="Fecha de nacimiento (OPCIONAL)">
                    <input type="text" name="sexo" placeholder="Sexo (OPCIONAL)">
                    <input type="text" name="direccion" placeholder="Dirección (OPCIONAL)">
                    <input type="text" name="telefono" placeholder="Teléfono (OPCIONAL)">
                    <input type="text" name="departamento" placeholder="Departamento (OPCIONAL)">
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <label for="perfil">Foto de perfil:</label>
                    <input type="file" name="perfil" accept="image/*">
                    
                    <button type="submit">Agregar Usuario</button>
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
                notification.innerText = 'Usuario agregado exitosamente';
                notification.classList.add('show');
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            } else if (status === 'error') {
                notification.innerText = 'Error al agregar usuario: ' + decodeURIComponent(message);
                notification.classList.add('error', 'show');
                setTimeout(function() {
                    notification.classList.remove('show', 'error');
                }, 3000);
            }
        };
    </script>
</body>
</html>