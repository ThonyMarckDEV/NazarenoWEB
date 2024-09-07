<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión iniciada
    exit();
}

// Incluir la conexión a la base de datos
include 'conexion.php'; // Asegúrate de que la ruta es correcta

// Incluir los archivos de PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        header("Location: UIAdmin.php"); // Redirige a la interfaz de administrador
        exit();
    case 'DOCENTE':
        header("Location: UIMaestro.php"); // Redirige a la interfaz de maestro
        exit();
    case 'APODERADO':
        header("Location: UIApoderado.php"); // Redirige a la interfaz de apoderado
        exit();
}

// Incluir la función para enviar el correo con PHPMailer
function enviarCorreo($email, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'cebepiura@gmail.com';  // Tu correo de Gmail
        $mail->Password = 'kobj rkvf aitt cwhu';  // La contraseña de tu cuenta de Gmail o clave de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatarios
        $mail->setFrom('cebepiura@gmail.com', 'C.E.B.E');
        $mail->addAddress($email);

        // Adjuntar la imagen
        $mail->addEmbeddedImage('../img/correo.png', 'decoracion'); // Reemplaza con la ruta de tu imagen

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Notificacion de actualizacion de cuenta';

        // Mensaje con imagen adjunta
        $mail->Body = '<p><img src="cid:decoracion" alt="Decoración" style="display: block; margin: 0 auto;"/></p>'
                      . '<p>' . $mensaje . '</p>';

        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "El mensaje no pudo ser enviado. Error de Mailer: {$mail->ErrorInfo}";
        return false;
    }
}

// Verificar si el formulario ha sido enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Iniciar una transacción
    $conn->begin_transaction();

    try {
        $correo_modificado = false;

        // Array para almacenar las columnas y sus nuevos valores
        $fields_to_update = [];
        $types = ""; // Para almacenar los tipos de parámetros en bind_param
        $values = []; // Para almacenar los valores correspondientes

        // Función para verificar si un campo ha sido modificado
        function addIfModified($conn, $field_name, $field_value, &$fields_to_update, &$types, &$values, $username) {
            global $correo_modificado;

            // Obtener el valor actual del campo desde la base de datos
            $stmt = $conn->prepare("SELECT $field_name FROM usuarios WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($current_value);
            $stmt->fetch();
            $stmt->close();

            // Solo agregar al array si el valor ha cambiado
            if ($field_value !== $current_value) {
                $fields_to_update[] = "$field_name = ?";
                $types .= "s"; // 's' porque todos los campos son strings
                $values[] = $field_value;

                // Marcar que el correo ha sido modificado
                if ($field_name === 'correo') {
                    $correo_modificado = true;
                }
            }
        }

        // Verificar y agregar los campos modificados
        if (isset($_POST['correo']) && !empty($_POST['correo'])) {
            // Validar el correo electrónico
            if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Correo electrónico no válido.');
            }
            addIfModified($conn, 'correo', $_POST['correo'], $fields_to_update, $types, $values, $username);
        }

        if (isset($_POST['nacimiento']) && !empty($_POST['nacimiento'])) {
            addIfModified($conn, 'nacimiento', $_POST['nacimiento'], $fields_to_update, $types, $values, $username);
        }

        if (isset($_POST['sexo']) && !empty($_POST['sexo'])) {
            addIfModified($conn, 'sexo', $_POST['sexo'], $fields_to_update, $types, $values, $username);
        }

        if (isset($_POST['direccion']) && !empty($_POST['direccion'])) {
            addIfModified($conn, 'direccion', $_POST['direccion'], $fields_to_update, $types, $values, $username);
        }

        if (isset($_POST['telefono']) && !empty($_POST['telefono'])) {
            addIfModified($conn, 'telefono', $_POST['telefono'], $fields_to_update, $types, $values, $username);
        }

        if (isset($_POST['departamento']) && !empty($_POST['departamento'])) {
            addIfModified($conn, 'departamento', $_POST['departamento'], $fields_to_update, $types, $values, $username);
        }

        // Solo realizar la actualización si hay campos modificados
        if (count($fields_to_update) > 0) {
            $sql_update = "UPDATE usuarios SET " . implode(", ", $fields_to_update) . " WHERE username = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update === false) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }

            // Agregar el nombre de usuario al final del array de valores
            $types .= "s";
            $values[] = $username;

            // Hacer bind de los parámetros
            $stmt_update->bind_param($types, ...$values);

            // Ejecutar la actualización
            $stmt_update->execute();
            $stmt_update->close();
        }

        // Si el correo ha sido modificado, enviar una notificación
        if ($correo_modificado) {
            $nuevoCorreo = $_POST['correo'];
            $mensaje = "Has actualizado tu correo electrónico a $nuevoCorreo en la plataforma C.E.B.E.";
            
            if (!enviarCorreo($nuevoCorreo, $mensaje)) {
                throw new Exception("Error al enviar el correo de confirmación.");
            }
        }

        // Manejar la subida de la foto de perfil si se ha proporcionado una nueva
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $foto_tmp_name = $_FILES['foto']['tmp_name'];
            $foto_size = $_FILES['foto']['size'];
            $foto_type = $_FILES['foto']['type'];

            if ($foto_size > 2 * 1024 * 1024) {
                throw new Exception("El archivo es demasiado grande. Tamaño máximo: 2MB.");
            }

            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($foto_type, $allowed_types)) {
                throw new Exception("Tipo de archivo no permitido. Solo se permiten imágenes JPEG y PNG.");
            }

            $foto_content = file_get_contents($foto_tmp_name);

            $sql_update_foto = "UPDATE usuarios SET perfil = ? WHERE username = ?";
            $stmt_update_foto = $conn->prepare($sql_update_foto);
            if ($stmt_update_foto === false) {
                throw new Exception("Error en la preparación de la consulta para la foto: " . $conn->error);
            }

            $null = NULL;
            $stmt_update_foto->bind_param("bs", $null, $username);
            $stmt_update_foto->send_long_data(0, $foto_content);
            $stmt_update_foto->execute();
            $stmt_update_foto->close();
        }

        $conn->commit();
        $conn->close();
        header("Location: perfilAlumno.php?status=success");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        header("Location: perfilAlumno.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
