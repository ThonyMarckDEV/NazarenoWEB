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
        if ($status === 'loggedOn') {
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
                    header("Location: ADMINPHP/UIAdmin.php");
                    break;
                case 'ESTUDIANTE':
                    header("Location: ALUMNOPHP/UIAlumno.php");
                    break;
                case 'DOCENTE':
                    header("Location: DOCENTEPHP/UIMaestro.php");
                    break;
                case 'APODERADO':
                    header("Location: APODERADOPHP/UIApoderado.php");
                    break;
                default:
                    header("Location: index.php?status=error");
                    exit();
            }
            exit();
        }
    } else {
        $_SESSION['error'] = "Usuario y/o contraseña incorrectos.";
        header("Location: ../index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Usuario y/o contraseña incorrectos.";
    header("Location: ../index.php");
    exit();
}

$stmt->close();
$conn->close();
?>
