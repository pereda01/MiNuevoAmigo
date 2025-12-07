<?php
require_once '../includes/header.php';
require_once '../config/database.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card sombra-card">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">Crear Cuenta</h3>
                </div>
                <div class="card-body p-4">
                    <form action="../processes/register_process.php" method="POST">
                        <!-- Tipo de usuario -->
                        <div class="mb-3">
                            <label class="form-label">¿Qué tipo de cuenta deseas?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="adoptante" value="adoptante" checked>
                                <label class="form-check-label" for="adoptante">
                                    🏠 Adoptante - Busco una mascota
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="refugio" value="refugio">
                                <label class="form-check-label" for="refugio">
                                    🐾 Refugio - Publico animales para adopción
                                </label>
                            </div>
                        </div>

                        <!-- Información básica -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Repetir Contraseña *</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                            <small id="password-match-error" class="text-danger d-none">Las contraseñas no coinciden</small>
                        </div>

                        <!-- Información específica para adoptantes -->
                        <div id="adoptante-info">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre">
                            </div>
                            <div class="mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos">
                            </div>
                        </div>

                        <!-- Información específica para refugios -->
                        <div id="refugio-info" style="display: none;">
                            <div class="mb-3">
                                <label for="nombre_refugio" class="form-label">Nombre del refugio *</label>
                                <input type="text" class="form-control" id="nombre_refugio" name="nombre_refugio">
                            </div>
                            <div class="mb-3">
                                <label for="nombre_contacto" class="form-label">Nombre de contacto *</label>
                                <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto">
                            </div>
                        </div>

                        <!-- Información común -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="number" class="form-control" id="telefono" name="telefono">
                        </div>

                        <div class="mb-3">
                            <label for="ciudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad">
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">Registrarse</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>¿Ya tienes cuenta? <a href="login.php" class="text-success">Inicia sesión aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campos según tipo de usuario
document.querySelectorAll('input[name="tipo"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'adoptante') {
            document.getElementById('adoptante-info').style.display = 'block';
            document.getElementById('refugio-info').style.display = 'none';
        } else {
            document.getElementById('adoptante-info').style.display = 'none';
            document.getElementById('refugio-info').style.display = 'block';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>