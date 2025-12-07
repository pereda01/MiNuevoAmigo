<?php
require_once '../config/database.php';

// Verificar que el usuario esté logueado y sea adoptante
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'adoptante') {
    header("Location: login.php");
    exit();
}

$adoptante_id = $_SESSION['user_id'];

// Obtener solicitudes del adoptante
$sql = "SELECT sa.*, a.nombre as nombre_animal, a.tipo, 
               r.nombre_refugio, r.nombre_contacto,
               sa.fecha_solicitud, sa.estado, sa.fecha_resolucion,
               (SELECT ruta_foto FROM fotos_animales WHERE id_animal = a.id ORDER BY id ASC LIMIT 1) as foto_principal
        FROM solicitudes_adopcion sa
        JOIN animales a ON sa.id_animal = a.id
        JOIN usuarios u ON a.id_refugio = u.id
        JOIN refugios r ON u.id = r.id
        WHERE sa.id_adoptante = '$adoptante_id'
        ORDER BY sa.fecha_solicitud DESC";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-success">📋 Mis Solicitudes de Adopción</h2>
                <a href="animals.php" class="btn btn-outline-success">← Volver a Animales</a>
            </div>

            <!-- Mostrar mensajes de éxito/error -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['success']) {
                        case 'solicitud_enviada':
                            echo '✅ Solicitud enviada correctamente.';
                            break;
                        case 'solicitud_cancelada':
                            echo '✅ Solicitud cancelada correctamente.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['error']) {
                        case 'solicitud_existente':
                            echo '❌ Ya tienes una solicitud pendiente para este animal.';
                            break;
                        case 'animal_no_disponible':
                            echo '❌ Este animal ya no está disponible para adopción.';
                            break;
                        case 'solicitud_fallo':
                            echo '❌ Error al enviar la solicitud.';
                            break;
                        case 'cancelar_fallo':
                            echo '❌ Error al cancelar la solicitud.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <div class="row">
                    <?php while($solicitud = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card sombra-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo $solicitud['nombre_animal']; ?></h5>
                                    <span class="badge 
                                        <?php 
                                        switch($solicitud['estado']) {
                                            case 'pendiente': echo 'bg-warning'; break;
                                            case 'aceptada': echo 'bg-success'; break;
                                            case 'rechazada': echo 'bg-danger'; break;
                                            case 'cancelada': echo 'bg-secondary'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($solicitud['estado']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <p class="mb-2">
                                                <strong>Refugio:</strong> <?php echo $solicitud['nombre_refugio']; ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Contacto:</strong> <?php echo $solicitud['nombre_contacto']; ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Fecha solicitud:</strong> 
                                                <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                                            </p>
                                            <?php if ($solicitud['fecha_resolucion']): ?>
                                                <p class="mb-2">
                                                    <strong>Fecha respuesta:</strong> 
                                                    <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_resolucion'])); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($solicitud['mensaje_adoptante']): ?>
                                                <p class="mb-2">
                                                    <strong>Tu mensaje:</strong> 
                                                    <em>"<?php echo $solicitud['mensaje_adoptante']; ?>"</em>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="bg-light rounded p-3">
                                                <?php if (!empty($solicitud['foto_principal'])): ?>
                                                    <img src="../uploads/animals/<?php echo $solicitud['foto_principal']; ?>" 
                                                         class="img-fluid rounded" 
                                                         alt="<?php echo $solicitud['nombre_animal']; ?>"
                                                         style="max-height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <?php 
                                                    if ($solicitud['tipo'] == 'perro') echo '🐕';
                                                    elseif ($solicitud['tipo'] == 'gato') echo '🐈';
                                                    else echo '🐾';
                                                    ?>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?php echo ucfirst($solicitud['tipo']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2">
                                        <a href="animal_detalle.php?id=<?php echo $solicitud['id_animal']; ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            Ver Animal
                                        </a>
                                        <?php if ($solicitud['estado'] === 'pendiente'): ?>
                                            <form action="../processes/cancelar_solicitud.php" method="POST" class="d-inline">
                                                <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('¿Estás seguro de que quieres cancelar esta solicitud?')">
                                                    Cancelar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($solicitud['estado'] === 'aceptada'): ?>
                                            <span class="badge bg-success">¡Contacta con el refugio!</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3" style="font-size: 4rem;">📝</div>
                    <h4 class="text-muted">No tienes solicitudes de adopción</h4>
                    <p class="text-muted">Encuentra a tu compañero ideal y envía tu primera solicitud.</p>
                    <a href="animals.php" class="btn btn-success">Buscar Animales</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>