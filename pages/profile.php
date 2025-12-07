<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Verificar que el usuario esté logueado (ya se hizo en header.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Obtener datos del usuario según su tipo - Una sola consulta
if ($user_type === 'adoptante') {
    $stmt = $conn->prepare("SELECT u.username, u.email, a.nombre, a.apellidos, a.telefono, a.ciudad 
                            FROM usuarios u 
                            JOIN adoptantes a ON u.id = a.id 
                            WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Obtener estadísticas en una sola consulta
    $stmt = $conn->prepare("SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN estado = 'aceptada' THEN 1 ELSE 0 END) as aceptadas
                            FROM solicitudes_adopcion 
                            WHERE id_adoptante = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $total_solicitudes = $stats['total'] ?? 0;
    $solicitudes_aceptadas = $stats['aceptadas'] ?? 0;
} else {
    $stmt = $conn->prepare("SELECT u.username, u.email, r.nombre_refugio, r.nombre_contacto, r.telefono, r.ciudad 
                            FROM usuarios u 
                            JOIN refugios r ON u.id = r.id 
                            WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Obtener estadísticas en una sola consulta
    $stmt = $conn->prepare("SELECT 
                            (SELECT COUNT(*) FROM animales WHERE id_refugio = ? AND estado = 'disponible') as animales_activos,
                            (SELECT COUNT(*) FROM solicitudes_adopcion sa JOIN animales a ON sa.id_animal = a.id WHERE a.id_refugio = ? AND sa.estado = 'pendiente') as solicitudes_pendientes,
                            (SELECT COUNT(*) FROM solicitudes_adopcion sa JOIN animales a ON sa.id_animal = a.id WHERE a.id_refugio = ? AND sa.estado = 'aceptada') as adopciones_exitosas");
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $animales_activos = $stats['animales_activos'] ?? 0;
    $solicitudes_pendientes = $stats['solicitudes_pendientes'] ?? 0;
    $adopciones_exitosas = $stats['adopciones_exitosas'] ?? 0;
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <!-- Panel lateral -->
            <div class="card sombra-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Mi Perfil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <span class="text-white fs-2">
                                <?php echo $user_type === 'adoptante' ? '👤' : '🏠'; ?>
                            </span>
                        </div>
                    </div>
                    <h5><?php echo $user_data['username']; ?></h5>
                    <p class="text-muted">
                        <?php echo $user_type === 'adoptante' ? 'Adoptante' : 'Refugio'; ?>
                    </p>
                </div>
            </div>

            <!-- Menú de navegación -->
            <div class="card sombra-card">
                <div class="card-body">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
                                📝 Mi información
                            </a>
                        </li>
                        <?php if ($user_type === 'adoptante'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="solicitudes.php">
                                    📋 Mis solicitudes
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    🐾 Mis animales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="solicitudes_refugio.php">
                                    📨 Solicitudes
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Información del perfil -->
            <div class="card sombra-card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Información Personal</h5>
                    <a href="editar_perfil.php" class="btn btn-light btn-sm">✏️ Editar</a>
                </div>
                <div class="card-body">
                    <?php if ($user_type === 'adoptante'): ?>
                        <!-- Perfil de adoptante -->
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nombre:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellidos'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php else: ?>
                        <!-- Perfil de refugio -->
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Refugio:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($user_data['nombre_refugio'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Contacto:</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($user_data['nombre_contacto'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <!-- La tabla `refugios` no tiene columna 'descripcion' en la nueva estructura -->
                    <?php endif; ?>

                    <!-- Información común -->
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Usuario:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_data['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Email:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_data['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <?php if (!empty($user_data['telefono'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Teléfono:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_data['telefono'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($user_data['ciudad'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Ciudad:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_data['ciudad'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row mt-4">
                <?php if ($user_type === 'adoptante'): ?>
                    <div class="col-md-6">
                        <div class="card sombra-card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $total_solicitudes; ?></h3>
                                <p class="text-muted">Solicitudes enviadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card sombra-card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $solicitudes_aceptadas; ?></h3>
                                <p class="text-muted">Adopciones aceptadas</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-4">
                        <div class="card sombra-card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $animales_activos; ?></h3>
                                <p class="text-muted">Animales activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card sombra-card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $solicitudes_pendientes; ?></h3>
                                <p class="text-muted">Solicitudes pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card sombra-card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $adopciones_exitosas; ?></h3>
                                <p class="text-muted">Adopciones exitosas</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>