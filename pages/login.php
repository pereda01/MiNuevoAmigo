<?php
require_once '../includes/header.php';

// Mostrar mensajes de error pasados por GET
$errorMessage = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'usuario_no_encontrado') {
        $errorMessage = 'Usuario no encontrado. Por favor regístrate o verifica el usuario.';
    } elseif ($err === 'credenciales_incorrectas') {
        $errorMessage = 'Usuario o contraseña incorrectos. Verifica tus datos.';
    } else {
        $errorMessage = htmlspecialchars($err);
    }
}

// Prefill username si viene en GET
$prefillUsername = '';
if (isset($_GET['username'])) {
    $prefillUsername = htmlspecialchars($_GET['username']);
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card sombra-card">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">Iniciar Sesión</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger" role="alert" id="alertaError">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <form action="../processes/login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario o Email</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $prefillUsername; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">Entrar</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>¿No tienes cuenta? <a href="register.php" class="text-success">Regístrate aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>