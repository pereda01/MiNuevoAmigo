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

// Obtener datos actuales del usuario con prepared statement
if ($user_type === 'adoptante') {
    $stmt = $conn->prepare("SELECT u.username, u.email, a.nombre, a.apellidos, a.telefono, a.ciudad 
                           FROM usuarios u 
                           JOIN adoptantes a ON u.id = a.id 
                           WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT u.username, u.email, r.nombre_refugio, r.nombre_contacto, r.telefono, r.ciudad 
                           FROM usuarios u 
                           JOIN refugios r ON u.id = r.id 
                           WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = trim($_POST['telefono'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $error = '';
    
    // Validar teléfono: si se proporcionó, verificar que no esté en uso por otro usuario
    if (!empty($telefono)) {
        if ($user_type === 'adoptante') {
            $check_stmt = $conn->prepare("SELECT id FROM adoptantes WHERE telefono = ? AND id != ?");
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM refugios WHERE telefono = ? AND id != ?");
        }
        
        $check_stmt->bind_param("si", $telefono, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Este número de teléfono ya está registrado por otro usuario.";
        }
        $check_stmt->close();
    }
    
    if (empty($error)) {
        // Actualizar tabla específica según tipo
        if ($user_type === 'adoptante') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            
            $update_detail_stmt = $conn->prepare("UPDATE adoptantes SET 
                                                 nombre = ?, 
                                                 apellidos = ?, 
                                                 telefono = ?, 
                                                 ciudad = ? 
                                                 WHERE id = ?");
            $update_detail_stmt->bind_param("ssssi", $nombre, $apellidos, $telefono, $ciudad, $user_id);
        } else {
            $nombre_refugio = trim($_POST['nombre_refugio'] ?? '');
            $nombre_contacto = trim($_POST['nombre_contacto'] ?? '');
            
            $update_detail_stmt = $conn->prepare("UPDATE refugios SET 
                                                 nombre_refugio = ?, 
                                                 nombre_contacto = ?, 
                                                 telefono = ?, 
                                                 ciudad = ? 
                                                 WHERE id = ?");
            $update_detail_stmt->bind_param("ssssi", $nombre_refugio, $nombre_contacto, $telefono, $ciudad, $user_id);
        }
        
        if ($update_detail_stmt->execute()) {
            $update_detail_stmt->close();
            header("Location: profile.php?success=perfil_actualizado");
            exit();
        } else {
            $error = "Error al actualizar los datos: " . $update_detail_stmt->error;
            $update_detail_stmt->close();
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
                                <label class="form-label">Nombre de usuario</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                                <small class="text-muted">No se puede cambiar</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                                <small class="text-muted">No se puede cambiar</small>
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
                        <?php endif; ?>

                        <!-- Información de contacto común -->
                        <h5 class="text-success mb-3 mt-4">Información de Contacto</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" 
                                       value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>" 
                                       placeholder="Ej: 612345678" pattern="[0-9]*" inputmode="numeric">
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