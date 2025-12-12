<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Obtener ID del animal desde la URL
$animal_id = $_GET['id'] ?? 0;

if (!$animal_id) {
    header("Location: animals.php");
    exit();
}

// Obtener información del animal
$sql = "SELECT a.*, 
               COALESCE(ad.ciudad, ref.ciudad) as ciudad_refugio,
               ref.nombre_refugio,
               ref.nombre_contacto,
               ref.telefono as telefono_refugio
        FROM animales a 
        JOIN usuarios u ON a.id_refugio = u.id 
        LEFT JOIN refugios ref ON u.id = ref.id
        LEFT JOIN adoptantes ad ON u.id = ad.id
        WHERE a.id = $animal_id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header("Location: animals.php");
    exit();
}

$animal = $result->fetch_assoc();

// Obtener fotos del animal
$fotos_sql = "SELECT * FROM fotos_animales WHERE id_animal = $animal_id ORDER BY id ASC";
$fotos_result = $conn->query($fotos_sql);
?>


<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Galería de imágenes -->
            <div class="card sombra-card mb-4">
                <div class="card-body p-0">
                    <?php if ($fotos_result->num_rows > 0): ?>
                        <div id="carouselAnimal" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php $first = true; ?>
                                <?php while($foto = $fotos_result->fetch_assoc()): ?>
                                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                                        <img src="../uploads/animals/<?php echo $foto['ruta_foto']; ?>" 
                                             class="d-block w-100 carousel-img" 
                                             alt="<?php echo $animal['nombre']; ?>">
                                    </div>
                                    <?php $first = false; ?>
                                <?php endwhile; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselAnimal" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselAnimal" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Imagen por defecto si no hay fotos -->
                        <img src="https://via.placeholder.com/800x550/28a745/ffffff?text=<?php echo urlencode($animal['nombre']); ?>" 
                             class="card-img-top carousel-placeholder" 
                             alt="<?php echo $animal['nombre']; ?>">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información detallada -->
            <div class="card sombra-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Sobre <?php echo $animal['nombre']; ?></h4>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br($animal['descripcion']); ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Información básica</h5>
                            <ul class="list-unstyled">
                                <li><strong>Edad:</strong> <?php echo $animal['edad_categoria']; ?></li>
                                <li><strong>Sexo:</strong> <?php echo $animal['sexo']; ?></li>
                                <li><strong>Tamaño:</strong> <?php echo $animal['tamano']; ?></li>
                                <li><strong>Raza:</strong> <?php echo $animal['raza'] ?: 'Mestizo'; ?></li>
                                <?php if ($animal['peso']): ?>
                                    <li><strong>Peso:</strong> <?php echo $animal['peso']; ?> kg</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Salud y cuidados</h5>
                            <ul class="list-unstyled">
                                <li><strong>Vacunado:</strong> <?php echo $animal['vacunado'] ? '✅ Sí' : '❌ No'; ?></li>
                                <li><strong>Esterilizado:</strong> <?php echo $animal['esterilizado'] ? '✅ Sí' : '❌ No'; ?></li>
                                <?php if ($animal['vacunas']): ?>
                                    <li><strong>Vacunas:</strong> <?php echo $animal['vacunas']; ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Personalidad</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-info">Energía: <?php echo $animal['nivel_energia']; ?></span>
                                <span class="badge bg-warning">Con niños: <?php echo $animal['relacion_ninos']; ?></span>
                                <span class="badge bg-secondary">Con otros animales: <?php echo $animal['relacion_otros_animales']; ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($animal['necesidades_especiales']): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Necesidades especiales</h5>
                            <p class="text-muted"><?php echo $animal['necesidades_especiales']; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Información del refugio -->
            <div class="card sombra-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🏠 Refugio</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo $animal['nombre_refugio']; ?></h6>
                    <p class="mb-1"><strong>Contacto:</strong> <?php echo $animal['nombre_contacto']; ?></p>
                    <?php if ($animal['telefono_refugio']): ?>
                        <p class="mb-1"><strong>Teléfono:</strong> <?php echo $animal['telefono_refugio']; ?></p>
                    <?php endif; ?>
                    <?php if ($animal['ciudad_refugio']): ?>
                        <p class="mb-0"><strong>Ubicación:</strong> <?php echo $animal['ciudad_refugio']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card sombra-card">
                <div class="card-body text-center">
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'adoptante'): ?>
                        <!-- Botón de solicitud para adoptantes -->
                        <button class="btn btn-success btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalSolicitud">
                            🐾 Solicitar Adopción
                        </button>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <!-- Mensaje para usuarios no logueados -->
                        <p class="text-muted">Inicia sesión para solicitar la adopción</p>
                        <a href="login.php" class="btn btn-outline-success w-100">Iniciar Sesión</a>
                    <?php else: ?>
                        <!-- Mensaje para refugios -->
                        <p class="text-muted">Eres un refugio, no puedes adoptar</p>
                    <?php endif; ?>
                    
                    <a href="animals.php" class="btn btn-outline-secondary w-100 mt-2">← Volver a animales</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de solicitud de adopción -->
<div class="modal fade" id="modalSolicitud" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Solicitar adopción de <?php echo $animal['nombre']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../processes/solicitud_process.php" method="POST">
                    <input type="hidden" name="animal_id" value="<?php echo $animal['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Cuéntanos por qué quieres adoptar a <?php echo $animal['nombre']; ?><br>Recuerda cuantos más detalles des, mejor será la valoración de tu solicitud.</label>
                        <textarea class="form-control" name="mensaje" rows="4" 
                                  placeholder="Comparte información sobre tu hogar, experiencia con mascotas, etc..." 
                                  required></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Enviar Solicitud</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>