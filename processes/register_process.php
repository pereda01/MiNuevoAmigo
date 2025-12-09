<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y limpiar datos
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
    $ciudad = $conn->real_escape_string($_POST['ciudad'] ?? '');

    // Construir URL para redirigir con datos en caso de error
    $redirectUrl = '../pages/register.php?tipo=' . urlencode($tipo) .
                   '&username=' . urlencode($username) .
                   '&email=' . urlencode($email) .
                   '&telefono=' . urlencode($telefono) .
                   '&ciudad=' . urlencode($ciudad);

    // Datos específicos según tipo
    if ($tipo === 'adoptante') {
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $apellidos = $conn->real_escape_string($_POST['apellidos']);
        $redirectUrl .= '&nombre=' . urlencode($nombre) .
                       '&apellidos=' . urlencode($apellidos);
    } else {
        $nombre_refugio = $conn->real_escape_string($_POST['nombre_refugio']);
        $nombre_contacto = $conn->real_escape_string($_POST['nombre_contacto']);
        $redirectUrl .= '&nombre_refugio=' . urlencode($nombre_refugio) .
                       '&nombre_contacto=' . urlencode($nombre_contacto);
    }

    // VALIDAR: contraseñas coincidan
    if ($password !== $password_confirm) {
        header("Location: " . $redirectUrl . "&error=" . urlencode('Las contraseñas no coinciden'));
        exit();
    }

    // VALIDAR: username único
    $check_username = "SELECT id FROM usuarios WHERE username = '$username'";
    $result = $conn->query($check_username);
    if ($result->num_rows > 0) {
        header("Location: " . $redirectUrl . "&error=" . urlencode('El nombre de usuario ya está en uso'));
        exit();
    }

    // VALIDAR: email único
    $check_email = "SELECT id FROM usuarios WHERE email = '$email'";
    $result = $conn->query($check_email);
    if ($result->num_rows > 0) {
        header("Location: " . $redirectUrl . "&error=" . urlencode('El email ya está registrado'));
        exit();
    }

    // VALIDAR: teléfono único (si se proporcionó)
    if (!empty($telefono)) {
        $check_telefono = "SELECT id FROM adoptantes WHERE telefono = '$telefono' UNION SELECT id FROM refugios WHERE telefono = '$telefono'";
        $result = $conn->query($check_telefono);
        if ($result->num_rows > 0) {
            header("Location: " . $redirectUrl . "&error=" . urlencode('El teléfono ya está registrado'));
            exit();
        }
    }

    // VALIDAR: nombre refugio único (si es refugio)
    if ($tipo === 'refugio') {
        $check_refugio = "SELECT id FROM refugios WHERE nombre_refugio = '$nombre_refugio'";
        $result = $conn->query($check_refugio);
        if ($result->num_rows > 0) {
            header("Location: " . $redirectUrl . "&error=" . urlencode('El nombre del refugio ya está registrado'));
            exit();
        }
    }

    // Hash de contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar en tabla usuarios
    $sql_user = "INSERT INTO usuarios (username, email, password, tipo) 
                 VALUES ('$username', '$email', '$password_hash', '$tipo')";

    if ($conn->query($sql_user) === TRUE) {
        $user_id = $conn->insert_id;

        // Insertar datos específicos según tipo
        if ($tipo === 'adoptante') {
            $sql_detail = "INSERT INTO adoptantes (id, nombre, apellidos, telefono, ciudad) 
                          VALUES ('$user_id', '$nombre', '$apellidos', '$telefono', '$ciudad')";
        } else {
            $sql_detail = "INSERT INTO refugios (id, nombre_refugio, nombre_contacto, telefono, ciudad) 
                          VALUES ('$user_id', '$nombre_refugio', '$nombre_contacto', '$telefono', '$ciudad')";
        }

        if ($conn->query($sql_detail) === TRUE) {
            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $tipo;
            $_SESSION['logged_in'] = true;

            header("Location: ../pages/profile.php?success=registro_ok");
        } else {
            header("Location: " . $redirectUrl . "&error=" . urlencode('Error al guardar datos del perfil'));
        }
    } else {
        header("Location: " . $redirectUrl . "&error=" . urlencode('Error al registrarse. Intenta de nuevo'));
    }

    $conn->close();
}
?>