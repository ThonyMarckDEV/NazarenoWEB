<?php
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtener los datos del formulario
$username = $_POST['username'];
$pass = $_POST['password'];

// Consultar la base de datos para validar las credenciales
$sql = "SELECT idUsuario, status, rol, password FROM usuarios WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si las credenciales son correctas
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $idUser = $row['idUsuario'];
    $status = $row['status'];
    $rol = $row['rol'];
    $hashed_password = $row['password'];

    // Verificar si la contraseña es correcta
    if (password_verify($pass, $hashed_password)) {
        // Verificar si el usuario ya tiene una sesión activa
        if ($status === 'loggedOn') {
            // Redirigir a index.php con un mensaje de error
            $_SESSION['error'] = "Ya hay una sesión activa con este usuario.";
            header("Location: ../index.php");
            exit();
        } else {
            // Iniciar sesión y actualizar el estado del usuario
            $update_sql = "UPDATE usuarios SET status = 'loggedOn' WHERE idUsuario = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $idUser);
            $update_stmt->execute();
            
            // Establecer la sesión
            $_SESSION['user'] = $username;
            $_SESSION['rol'] = $rol;

            // Redirigir según el rol del usuario
            switch ($rol) {
                case 'ADMIN':
                    header("Location: UIAdmin.php");
                    break;
                case 'ESTUDIANTE':
                    header("Location: UIAlumno.php");
                    break;
                case 'MAESTRO':
                    header("Location: UIMaestro.php");
                    break;
                case 'APODERADO':
                    header("Location: UIApoderado.php");
                    break;
                default:
                    // Redirigir a una página de error o logout si no se reconoce el rol
                    $_SESSION['error'] = "Rol de usuario no válido.";
                    header("Location: ../index.php");
                    break;
            }
            exit();
        }
    } else {
        // Contraseña incorrecta
        echo "Usuario o contraseña incorrectos.";
    }
} else {
    // No se encontraron credenciales válidas
    echo "Usuario o contraseña incorrectos.";
}

$stmt->close();
$conn->close();
?>
