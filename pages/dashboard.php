<?php
require_once '../config/database.php';

// Verificar que el usuario esté logueado y sea refugio
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'refugio') {
    header("Location: login.php");
    exit();
}

$refugio_id = $_SESSION['user_id'];

// Obtener estadísticas del refugio
$animales_sql = "SELECT COUNT(*) as total FROM animales WHERE id_refugio = '$refugio_id'";
$animales_result = $conn->query($animales_sql);
$total_animales = $animales_result->fetch_assoc()['total'];

$animales_activos_sql = "SELECT COUNT(*) as activos FROM animales WHERE id_refugio = '$refugio_id' AND estado = 'disponible'";
$animales_activos_result = $conn->query($animales_activos_sql);
$animales_activos = $animales_activos_result->fetch_assoc()['activos'];

$solicitudes_pendientes_sql = "SELECT COUNT(*) as pendientes 
                              FROM solicitudes_adopcion sa 
                              JOIN animales a ON sa.id_animal = a.id 
                              WHERE a.id_refugio = '$refugio_id' AND sa.estado = 'pendiente'";
$solicitudes_pendientes_result = $conn->query($solicitudes_pendientes_sql);
$solicitudes_pendientes = $solicitudes_pendientes_result->fetch_assoc()['pendientes'];

$adopciones_exitosas_sql = "SELECT COUNT(*) as exitosas 
                           FROM solicitudes_adopcion sa 
                           JOIN animales a ON sa.id_animal = a.id 
                           WHERE a.id_refugio = '$refugio_id' AND sa.estado = 'aceptada'";
$adopciones_exitosas_result = $conn->query($adopciones_exitosas_sql);
$adopciones_exitosas = $adopciones_exitosas_result->fetch_assoc()['exitosas'];

// Obtener animales recientes del refugio
$animales_recientes_sql = "SELECT a.*, 
                                  (SELECT ruta_foto FROM fotos_animales WHERE id_animal = a.id AND es_principal = 1 LIMIT 1) as foto_principal
                           FROM animales a 
                           WHERE a.id_refugio = '$refugio_id' 
                           ORDER BY a.id DESC 
                           LIMIT 6";
$animales_recientes_result = $conn->query($animales_recientes_sql);

// Obtener solicitudes recientes
$solicitudes_recientes_sql = "SELECT sa.*, a.nombre as nombre_animal, a.tipo,
                                     sa.fecha_solicitud, sa.estado
                              FROM solicitudes_adopcion sa
                              JOIN animales a ON sa.id_animal = a.id
                              WHERE a.id_refugio = '$refugio_id'
                              ORDER BY sa.fecha_solicitud DESC
                              LIMIT 5";
$solicitudes_recientes_result = $conn->query($solicitudes_recientes_sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Header del Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-success">🏠 Dashboard del Refugio</h1>
        <a href="agregar_animal.php" class="btn btn-success">➕ Agregar Animal</a>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card sombra-card text-center bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $total_animales; ?></h3>
                    <p class="mb-0">Total Animales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card sombra-card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $animales_activos; ?></h3>
                    <p class="mb-0">Disponibles</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card sombra-card text-center bg-warning text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $solicitudes_pendientes; ?></h3>
                    <p class="mb-0">Solicitudes Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card sombra-card text-center bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $adopciones_exitosas; ?></h3>
                    <p class="mb-0">Adopciones Exitosas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda: Animales Recientes -->
        <div class="col-md-8">
            <div class="card sombra-card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🐾 Mis Animales Recientes</h5>
                    <a href="animals.php?mis_animales=1" class="btn btn-light btn-sm">Ver Todos</a>
                </div>
                <div class="card-body">
                    <?php if ($animales_recientes_result->num_rows > 0): ?>
                        <div class="row">
                            <?php while($animal = $animales_recientes_result->fetch_assoc()): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <?php if (!empty($animal['foto_principal'])): ?>
                                            <img src="../uploads/animals/<?php echo $animal['foto_principal']; ?>" 
                                                 class="card-img-top" alt="<?php echo $animal['nombre']; ?>" 
                                                 style="height: 120px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 120px;">
                                                <span class="text-muted fs-1">
                                                    <?php 
                                                    if ($animal['tipo'] == 'perro') echo '🐕';
                                                    elseif ($animal['tipo'] == 'gato') echo '🐈';
                                                    else echo '🐾';
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo $animal['nombre']; ?></h6>
                                            <p class="card-text small mb-1">
                                                <span class="badge bg-<?php echo $animal['estado'] == 'disponible' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($animal['estado']); ?>
                                                </span>
                                            </p>
                                            <p class="card-text small text-muted">
                                                <?php echo ucfirst($animal['tipo']); ?> • 
                                                <?php echo ucfirst($animal['edad_categoria']); ?>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-transparent p-2">
                                            <a href="animal_detalle.php?id=<?php echo $animal['id']; ?>" 
                                               class="btn btn-outline-success btn-sm w-100">
                                                Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="text-muted mb-2" style="font-size: 3rem;">🐾</div>
                            <p class="text-muted">No tienes animales registrados</p>
                            <a href="agregar_animal.php" class="btn btn-success">Agregar Primer Animal</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Solicitudes Recientes y Acciones Rápidas -->
        <div class="col-md-4">
            <!-- Solicitudes Recientes -->
            <div class="card sombra-card mb-4">
                <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📨 Solicitudes Recientes</h5>
                    <a href="solicitudes_refugio.php" class="btn btn-light btn-sm">Ver Todas</a>
                </div>
                <div class="card-body">
                    <?php if ($solicitudes_recientes_result->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while($solicitud = $solicitudes_recientes_result->fetch_assoc()): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo $solicitud['nombre_animal']; ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <?php echo date('d/m/Y', strtotime($solicitud['fecha_solicitud'])); ?>
                                            </p>
                                        </div>
                                        <span class="badge 
                                            <?php 
                                            switch($solicitud['estado']) {
                                                case 'pendiente': echo 'bg-warning'; break;
                                                case 'aceptada': echo 'bg-success'; break;
                                                case 'rechazada': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?php echo ucfirst($solicitud['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <div class="text-muted mb-2" style="font-size: 2rem;">📝</div>
                            <p class="text-muted small">No hay solicitudes recientes</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card sombra-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">⚡ Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="agregar_animal.php" class="btn btn-outline-success">
                            ➕ Agregar Animal
                        </a>
                        <a href="solicitudes_refugio.php" class="btn btn-outline-warning">
                            📨 Gestionar Solicitudes
                        </a>
                        <a href="animals.php?mis_animales=1" class="btn btn-outline-primary">
                            🐾 Ver Mis Animales
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            👤 Mi Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>