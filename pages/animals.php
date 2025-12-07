<?php
require_once '../config/database.php';

// Obtener parámetros de búsqueda
$tipo = $_GET['tipo'] ?? '';
$edad = $_GET['edad'] ?? '';
$tamano = $_GET['tamano'] ?? '';
$ciudad = $_GET['ciudad'] ?? '';

// Construir consulta con filtros
$sql = "SELECT a.*, 
               COALESCE(ad.ciudad, ref.ciudad) as ciudad_refugio,
               ref.nombre_refugio,
               (SELECT ruta_foto FROM fotos_animales WHERE id_animal = a.id ORDER BY id ASC LIMIT 1) as foto_principal
        FROM animales a 
        JOIN usuarios u ON a.id_refugio = u.id 
        LEFT JOIN refugios ref ON u.id = ref.id
        LEFT JOIN adoptantes ad ON u.id = ad.id
        WHERE a.estado = 'disponible'";

if (!empty($tipo)) {
    $sql .= " AND a.tipo = '$tipo'";
}
if (!empty($edad)) {
    $sql .= " AND a.edad_categoria = '$edad'";
}
if (!empty($tamano)) {
    $sql .= " AND a.tamano = '$tamano'";
}
if (!empty($ciudad)) {
    $sql .= " AND (ad.ciudad LIKE '%$ciudad%' OR ref.ciudad LIKE '%$ciudad%')";
}

$sql .= " ORDER BY a.id DESC";
$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Filtros -->
            <div class="card sombra-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🔍 Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="animals.php">
                        <!-- Tipo de animal -->
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <option value="">Todos</option>
                                <option value="perro" <?php echo $tipo == 'perro' ? 'selected' : ''; ?>>Perro</option>
                                <option value="gato" <?php echo $tipo == 'gato' ? 'selected' : ''; ?>>Gato</option>
                                <option value="otro" <?php echo $tipo == 'otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <!-- Edad -->
                        <div class="mb-3">
                            <label class="form-label">Edad</label>
                            <select class="form-select" name="edad">
                                <option value="">Todas</option>
                                <option value="cachorro" <?php echo $edad == 'cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                <option value="joven" <?php echo $edad == 'joven' ? 'selected' : ''; ?>>Joven</option>
                                <option value="adulto" <?php echo $edad == 'adulto' ? 'selected' : ''; ?>>Adulto</option>
                                <option value="mayor" <?php echo $edad == 'mayor' ? 'selected' : ''; ?>>Mayor</option>
                            </select>
                        </div>

                        <!-- Tamaño -->
                        <div class="mb-3">
                            <label class="form-label">Tamaño</label>
                            <select class="form-select" name="tamano">
                                <option value="">Todos</option>
                                <option value="pequeño" <?php echo $tamano == 'pequeño' ? 'selected' : ''; ?>>Pequeño</option>
                                <option value="mediano" <?php echo $tamano == 'mediano' ? 'selected' : ''; ?>>Mediano</option>
                                <option value="grande" <?php echo $tamano == 'grande' ? 'selected' : ''; ?>>Grande</option>
                            </select>
                        </div>

                        <!-- Ciudad -->
                        <div class="mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="ciudad" value="<?php echo $ciudad; ?>" placeholder="Ej: Madrid">
                        </div>

                        <button type="submit" class="btn btn-success w-100">Aplicar Filtros</button>
                        <a href="animals.php" class="btn btn-outline-secondary w-100 mt-2">Limpiar</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Resultados -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-success">
                    <?php 
                    if ($result->num_rows > 0) {
                        echo $result->num_rows . " animal(es) encontrado(s)";
                    } else {
                        echo "Animales en adopción";
                    }
                    ?>
                </h2>
                <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'refugio'): ?>
                    <a href="agregar_animal.php" class="btn btn-success">➕ Agregar Animal</a>
                <?php endif; ?>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="row">
                    <?php while($animal = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card sombra-card h-100">
                                <!-- Imagen del animal -->
                                <?php if (!empty($animal['foto_principal'])): ?>
                                    <img src="../uploads/animals/<?php echo $animal['foto_principal']; ?>" 
                                         class="card-img-top" alt="<?php echo $animal['nombre']; ?>" 
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <!-- Imagen por defecto si no hay foto -->
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                         style="height: 200px;">
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
                                    <h5 class="card-title"><?php echo $animal['nombre']; ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <?php 
                                            if ($animal['tipo'] == 'perro') echo '🐕';
                                            elseif ($animal['tipo'] == 'gato') echo '🐈';
                                            else echo '🐾';
                                            ?> 
                                            <?php echo ucfirst($animal['tipo']); ?> • 
                                            📍 <?php echo $animal['ciudad_refugio'] ?: 'Sin ubicación'; ?>
                                        </small>
                                    </p>
                                    
                                    <div class="animal-info mb-2">
                                        <span class="badge bg-success"><?php echo $animal['edad_categoria']; ?></span>
                                        <span class="badge bg-info"><?php echo $animal['tamano']; ?></span>
                                        <span class="badge bg-secondary"><?php echo $animal['sexo']; ?></span>
                                    </div>

                                    <?php if (!empty($animal['descripcion'])): ?>
                                        <p class="card-text small"><?php echo substr($animal['descripcion'], 0, 100); ?>...</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <a href="animal_detalle.php?id=<?php echo $animal['id']; ?>" class="btn btn-outline-success btn-sm w-100">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3" style="font-size: 4rem;">🐾</div>
                    <h4 class="text-muted">No se encontraron animales</h4>
                    <p class="text-muted">Intenta con otros filtros de búsqueda</p>
                    <a href="animals.php" class="btn btn-success">Ver todos los animales</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>