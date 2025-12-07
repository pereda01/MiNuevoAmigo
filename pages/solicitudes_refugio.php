<?php
require_once '../config/database.php';

// Verificar que el usuario esté logueado y sea refugio
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'refugio') {
    header("Location: login.php");
    exit();
}

$refugio_id = $_SESSION['user_id'];

// Procesar acciones (aceptar/rechazar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $solicitud_id = $conn->real_escape_string($_POST['solicitud_id']);
    $action = $_POST['action'];
    
    // Verificar que la solicitud pertenece a un animal del refugio
    $check_sql = "SELECT sa.id 
                  FROM solicitudes_adopcion sa 
                  JOIN animales a ON sa.id_animal = a.id 
                  WHERE sa.id = '$solicitud_id' AND a.id_refugio = '$refugio_id' AND sa.estado = 'pendiente'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 1) {
        if ($action === 'aceptar') {
            // Aceptar solicitud y marcar animal como adoptado
            $conn->query("UPDATE solicitudes_adopcion SET estado = 'aceptada', fecha_resolucion = NOW() WHERE id = '$solicitud_id'");
            $conn->query("UPDATE animales SET estado = 'adoptado' WHERE id = (SELECT id_animal FROM solicitudes_adopcion WHERE id = '$solicitud_id')");
            
            // Rechazar automáticamente las otras solicitudes pendientes para el mismo animal
            $animal_id = $conn->query("SELECT id_animal FROM solicitudes_adopcion WHERE id = '$solicitud_id'")->fetch_assoc()['id_animal'];
            $conn->query("UPDATE solicitudes_adopcion SET estado = 'rechazada', fecha_resolucion = NOW() WHERE id_animal = '$animal_id' AND estado = 'pendiente' AND id != '$solicitud_id'");
            
            header("Location: solicitudes_refugio.php?success=solicitud_aceptada");
        } elseif ($action === 'rechazar') {
            // Rechazar solicitud
            $conn->query("UPDATE solicitudes_adopcion SET estado = 'rechazada', fecha_resolucion = NOW() WHERE id = '$solicitud_id'");
            header("Location: solicitudes_refugio.php?success=solicitud_rechazada");
        }
    } else {
        header("Location: solicitudes_refugio.php?error=solicitud_no_valida");
    }
    exit();
}

// Obtener solicitudes del refugio
$estado = $_GET['estado'] ?? 'pendiente'; // Por defecto mostrar pendientes

$sql = "SELECT sa.*, a.nombre as nombre_animal, a.tipo, a.descripcion,
               u_adoptante.username as username_adoptante,
               ad.nombre as nombre_adoptante, ad.apellidos, ad.telefono, ad.ciudad as ciudad_adoptante,
               sa.fecha_solicitud, sa.estado, sa.fecha_resolucion,
               (SELECT ruta_foto FROM fotos_animales WHERE id_animal = a.id ORDER BY id ASC LIMIT 1) as foto_principal
        FROM solicitudes_adopcion sa
        JOIN animales a ON sa.id_animal = a.id
        JOIN usuarios u_adoptante ON sa.id_adoptante = u_adoptante.id
        LEFT JOIN adoptantes ad ON u_adoptante.id = ad.id
        WHERE a.id_refugio = '$refugio_id'";

// Filtrar por estado si se especifica
if ($estado !== 'todas') {
    $sql .= " AND sa.estado = '$estado'";
}

$sql .= " ORDER BY sa.fecha_solicitud DESC";
$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-success">📨 Solicitudes de Adopción</h2>
                <a href="dashboard.php" class="btn btn-outline-success">← Volver al Dashboard</a>
            </div>

            <!-- Filtros de estado -->
            <div class="card sombra-card mb-4">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="solicitudes_refugio.php?estado=pendiente" 
                           class="btn <?php echo $estado == 'pendiente' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            ⏳ Pendientes
                        </a>
                        <a href="solicitudes_refugio.php?estado=aceptada" 
                           class="btn <?php echo $estado == 'aceptada' ? 'btn-success' : 'btn-outline-success'; ?>">
                            ✅ Aceptadas
                        </a>
                        <a href="solicitudes_refugio.php?estado=rechazada" 
                           class="btn <?php echo $estado == 'rechazada' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            ❌ Rechazadas
                        </a>
                        <a href="solicitudes_refugio.php?estado=todas" 
                           class="btn <?php echo $estado == 'todas' ? 'btn-info' : 'btn-outline-info'; ?>">
                            📊 Todas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mostrar mensajes de éxito/error -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['success']) {
                        case 'solicitud_aceptada':
                            echo '✅ Solicitud aceptada correctamente. El animal ha sido marcado como adoptado.';
                            break;
                        case 'solicitud_rechazada':
                            echo '✅ Solicitud rechazada correctamente.';
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
                        case 'solicitud_no_valida':
                            echo '❌ La solicitud no es válida o no existe.';
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
                                            <!-- Información del adoptante -->
                                            <h6 class="text-success">👤 Solicitante:</h6>
                                            <p class="mb-2">
                                                <strong>Nombre:</strong> 
                                                <?php echo $solicitud['nombre_adoptante'] . ' ' . $solicitud['apellidos']; ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Usuario:</strong> <?php echo $solicitud['username_adoptante']; ?>
                                            </p>
                                            <?php if ($solicitud['telefono']): ?>
                                                <p class="mb-2">
                                                    <strong>Teléfono:</strong> <?php echo $solicitud['telefono']; ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($solicitud['ciudad_adoptante']): ?>
                                                <p class="mb-2">
                                                    <strong>Ciudad:</strong> <?php echo $solicitud['ciudad_adoptante']; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <!-- Información de la solicitud -->
                                            <h6 class="text-success mt-3">📝 Solicitud:</h6>
                                            <p class="mb-2">
                                                <strong>Fecha:</strong> 
                                                <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                                            </p>
                                            <?php if ($solicitud['mensaje_adoptante']): ?>
                                                <p class="mb-2">
                                                    <strong>Mensaje:</strong> 
                                                    <em>"<?php echo $solicitud['mensaje_adoptante']; ?>"</em>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($solicitud['fecha_resolucion']): ?>
                                                <p class="mb-2">
                                                    <strong>Fecha respuesta:</strong> 
                                                    <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_resolucion'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="bg-light rounded p-3">
                                                <?php if (!empty($solicitud['foto_principal'])): ?>
                                                    <img src="../uploads/animals/<?php echo $solicitud['foto_principal']; ?>" 
                                                         class="img-fluid rounded" 
                                                         alt="<?php echo $solicitud['nombre_animal']; ?>"
                                                         style="max-height: 100px; object-fit: cover;">
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
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="animal_detalle.php?id=<?php echo $solicitud['id_animal']; ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            Ver Animal
                                        </a>
                                        
                                        <?php if ($solicitud['estado'] === 'pendiente'): ?>
                                            <!-- Botones para aceptar/rechazar -->
                                            <form action="solicitudes_refugio.php" method="POST" class="d-inline">
                                                <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                                                <input type="hidden" name="action" value="aceptar">
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        onclick="return confirm('¿Estás seguro de ACEPTAR esta solicitud? El animal será marcado como adoptado y las demás solicitudes se rechazarán automáticamente.')">
                                                    ✅ Aceptar
                                                </button>
                                            </form>
                                            <form action="solicitudes_refugio.php" method="POST" class="d-inline">
                                                <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                                                <input type="hidden" name="action" value="rechazar">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('¿Estás seguro de RECHAZAR esta solicitud?')">
                                                    ❌ Rechazar
                                                </button>
                                            </form>
                                        <?php elseif ($solicitud['estado'] === 'aceptada'): ?>
                                            <span class="badge bg-success">✅ Adopción aceptada</span>
                                        <?php elseif ($solicitud['estado'] === 'rechazada'): ?>
                                            <span class="badge bg-danger">❌ Solicitud rechazada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3" style="font-size: 4rem;">
                        <?php 
                        switch($estado) {
                            case 'pendiente': echo '⏳'; break;
                            case 'aceptada': echo '✅'; break;
                            case 'rechazada': echo '❌'; break;
                            default: echo '📝';
                        }
                        ?>
                    </div>
                    <h4 class="text-muted">
                        <?php 
                        switch($estado) {
                            case 'pendiente': echo 'No hay solicitudes pendientes'; break;
                            case 'aceptada': echo 'No hay solicitudes aceptadas'; break;
                            case 'rechazada': echo 'No hay solicitudes rechazadas'; break;
                            default: echo 'No hay solicitudes';
                        }
                        ?>
                    </h4>
                    <p class="text-muted">
                        <?php if ($estado === 'pendiente'): ?>
                            Las solicitudes de adopción aparecerán aquí cuando los usuarios soliciten adoptar tus animales.
                        <?php endif; ?>
                    </p>
                    <?php if ($estado !== 'todas'): ?>
                        <a href="solicitudes_refugio.php?estado=todas" class="btn btn-outline-success">Ver todas las solicitudes</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>