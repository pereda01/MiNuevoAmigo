<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Buscar usuario por username o email
    $sql = "SELECT * FROM usuarios WHERE username = '$username' OR email = '$username'";
    $result = $conn->query($sql);

    $error = '';

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            // Iniciar sesión limpia
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            } else {
                // Limpiar sesión anterior si existe
                session_unset();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['tipo'];
            $_SESSION['logged_in'] = true;

            header("Location: ../pages/profile.php");
            exit();
        } else {
            $error = 'credenciales_incorrectas';
        }
    } else {
        $error = 'usuario_no_encontrado';
    }

    // Si falla el login, redirigir con mensaje y prefijar el username
    $redirectUrl = '../pages/login.php?error=' . urlencode($error);
    if (!empty($username)) {
        $redirectUrl .= '&username=' . urlencode($username);
    }
    header("Location: " . $redirectUrl);
    exit();
}

$conn->close();
?>