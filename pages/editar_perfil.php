<?php
require_once '../config/database.php';

// Verificar que el usuario esté logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Obtener datos actuales del usuario
if ($user_type === 'adoptante') {
    $sql = "SELECT u.username, u.email, a.nombre, a.apellidos, a.telefono, a.ciudad 
            FROM usuarios u 
            JOIN adoptantes a ON u.id = a.id 
            WHERE u.id = $user_id";
} else {
    $sql = "SELECT u.username, u.email, r.nombre_refugio, r.nombre_contacto, r.telefono, r.ciudad, r.descripcion 
            FROM usuarios u 
            JOIN refugios r ON u.id = r.id 
            WHERE u.id = $user_id";
}

$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
    $ciudad = $conn->real_escape_string($_POST['ciudad'] ?? '');
    
    // Verificar si el username o email ya existen (excluyendo el usuario actual)
    $check_sql = "SELECT id FROM usuarios WHERE (username = '$username' OR email = '$email') AND id != '$user_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error = "El nombre de usuario o email ya está en uso por otro usuario.";
    } else {
        // Actualizar tabla usuarios
        $update_usuario_sql = "UPDATE usuarios SET username = '$username', email = '$email' WHERE id = '$user_id'";
        
        if ($conn->query($update_usuario_sql)) {
            // Actualizar tabla específica según tipo
            if ($user_type === 'adoptante') {
                $nombre = $conn->real_escape_string($_POST['nombre']);
                $apellidos = $conn->real_escape_string($_POST['apellidos']);
                
                $update_detail_sql = "UPDATE adoptantes SET 
                                    nombre = '$nombre', 
                                    apellidos = '$apellidos', 
                                    telefono = '$telefono', 
                                    ciudad = '$ciudad' 
                                    WHERE id = '$user_id'";
            } else {
                $nombre_refugio = $conn->real_escape_string($_POST['nombre_refugio']);
                $nombre_contacto = $conn->real_escape_string($_POST['nombre_contacto']);
                $descripcion = $conn->real_escape_string($_POST['descripcion'] ?? '');
                
                $update_detail_sql = "UPDATE refugios SET 
                                    nombre_refugio = '$nombre_refugio', 
                                    nombre_contacto = '$nombre_contacto', 
                                    telefono = '$telefono', 
                                    ciudad = '$ciudad',
                                    descripcion = '$descripcion' 
                                    WHERE id = '$user_id'";
            }
            
            if ($conn->query($update_detail_sql)) {
                // Actualizar sesión si el username cambió
                $_SESSION['username'] = $username;
                
                header("Location: profile.php?success=perfil_actualizado");
                exit();
            } else {
                $error = "Error al actualizar los datos específicos: " . $conn->error;
            }
        } else {
            $error = "Error al actualizar los datos básicos: " . $conn->error;
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card sombra-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">✏️ Editar Perfil</h4>
                </div>
                <div class="card-body">
                    <!-- Mostrar mensajes de error -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="editar_perfil.php">
                        <!-- Información básica común -->
                        <h5 class="text-success mb-3">Información Básica</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre de usuario *</label>
                                <input type="text" class="form-control" name="username" 
                                       value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                        </div>

                        <!-- Información específica según tipo de usuario -->
                        <?php if ($user_type === 'adoptante'): ?>
                            <h5 class="text-success mb-3 mt-4">Información Personal</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre" 
                                           value="<?php echo htmlspecialchars($user_data['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellidos *</label>
                                    <input type="text" class="form-control" name="apellidos" 
                                           value="<?php echo htmlspecialchars($user_data['apellidos']); ?>" required>
                                </div>
                            </div>
                        <?php else: ?>
                            <h5 class="text-success mb-3 mt-4">Información del Refugio</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del refugio *</label>
                                    <input type="text" class="form-control" name="nombre_refugio" 
                                           value="<?php echo htmlspecialchars($user_data['nombre_refugio']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Persona de contacto *</label>
                                    <input type="text" class="form-control" name="nombre_contacto" 
                                           value="<?php echo htmlspecialchars($user_data['nombre_contacto']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción del refugio</label>
                                <textarea class="form-control" name="descripcion" rows="3" 
                                          placeholder="Describe tu refugio, misión, valores..."><?php echo htmlspecialchars($user_data['descripcion'] ?? ''); ?></textarea>
                            </div>
                        <?php endif; ?>

                        <!-- Información de contacto común -->
                        <h5 class="text-success mb-3 mt-4">Información de Contacto</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" 
                                       value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>" 
                                       placeholder="Ej: 612345678">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" 
                                       value="<?php echo htmlspecialchars($user_data['ciudad'] ?? ''); ?>" 
                                       placeholder="Ej: Madrid">
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">💾 Guardar Cambios</button>
                            <a href="profile.php" class="btn btn-outline-secondary">❌ Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
            
            
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>