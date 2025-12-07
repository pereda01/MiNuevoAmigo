<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Verificar que el usuario sea un refugio
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'refugio') {
    header("Location: login.php");
    exit();
}

$refugio_id = $_SESSION['user_id'];
$animal_id = intval($_GET['id'] ?? 0);

if ($animal_id === 0) {
    header("Location: dashboard.php?error=id_invalido");
    exit();
}

// Obtener datos del animal con prepared statement
$stmt = $conn->prepare("SELECT * FROM animales WHERE id = ? AND id_refugio = ?");
$stmt->bind_param("ii", $animal_id, $refugio_id);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$animal) {
    header("Location: dashboard.php?error=animal_no_encontrado");
    exit();
}

// Obtener fotos del animal (orden por id)
$stmt = $conn->prepare("SELECT * FROM fotos_animales WHERE id_animal = ? ORDER BY id ASC");
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$fotos_result = $stmt->get_result();
$fotos = $fotos_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card sombra-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">✏️ Editar Animal: <?php echo htmlspecialchars($animal['nombre'], ENT_QUOTES, 'UTF-8'); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../processes/animal_process.php" method="POST" enctype="multipart/form-data" id="formEditar" novalidate>
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="animal_id" value="<?php echo htmlspecialchars($animal_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="refugio_id" value="<?php echo htmlspecialchars($refugio_id, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Información básica -->
                        <h5 class="text-success mb-3">Información Básica</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del animal *</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($animal['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="perro" <?php echo $animal['tipo'] === 'perro' ? 'selected' : ''; ?>>Perro</option>
                                    <option value="gato" <?php echo $animal['tipo'] === 'gato' ? 'selected' : ''; ?>>Gato</option>
                                    <option value="otro" <?php echo $animal['tipo'] === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Edad *</label>
                                <select class="form-select" name="edad_categoria" required>
                                    <option value="cachorro" <?php echo $animal['edad_categoria'] === 'cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                    <option value="joven" <?php echo $animal['edad_categoria'] === 'joven' ? 'selected' : ''; ?>>Joven</option>
                                    <option value="adulto" <?php echo $animal['edad_categoria'] === 'adulto' ? 'selected' : ''; ?>>Adulto</option>
                                    <option value="mayor" <?php echo $animal['edad_categoria'] === 'mayor' ? 'selected' : ''; ?>>Mayor</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sexo *</label>
                                <select class="form-select" name="sexo" required>
                                    <option value="macho" <?php echo $animal['sexo'] === 'macho' ? 'selected' : ''; ?>>Macho</option>
                                    <option value="hembra" <?php echo $animal['sexo'] === 'hembra' ? 'selected' : ''; ?>>Hembra</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tamaño *</label>
                                <select class="form-select" name="tamano" required>
                                    <option value="pequeno" <?php echo $animal['tamano'] === 'pequeno' ? 'selected' : ''; ?>>Pequeño</option>
                                    <option value="mediano" <?php echo $animal['tamano'] === 'mediano' ? 'selected' : ''; ?>>Mediano</option>
                                    <option value="grande" <?php echo $animal['tamano'] === 'grande' ? 'selected' : ''; ?>>Grande</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Raza</label>
                                <input type="text" class="form-control" name="raza" value="<?php echo htmlspecialchars($animal['raza'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: Mestizo, Labrador, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" name="peso" step="0.1" min="0" value="<?php echo htmlspecialchars($animal['peso'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <!-- Salud -->
                        <h5 class="text-success mb-3 mt-4">Salud</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="vacunado" value="1" <?php echo $animal['vacunado'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">¿Está vacunado?</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="esterilizado" value="1" <?php echo $animal['esterilizado'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">¿Está esterilizado?</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vacunas aplicadas</label>
                            <input type="text" class="form-control" name="vacunas" placeholder="Ej: Rabia, Polivalente, Moquillo..." value="<?php echo htmlspecialchars($animal['vacunas'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <!-- Personalidad -->
                        <h5 class="text-success mb-3 mt-4">Personalidad</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nivel de energía *</label>
                                <select class="form-select" name="nivel_energia" required>
                                    <option value="bajo" <?php echo $animal['nivel_energia'] === 'bajo' ? 'selected' : ''; ?>>Bajo</option>
                                    <option value="medio" <?php echo $animal['nivel_energia'] === 'medio' ? 'selected' : ''; ?>>Medio</option>
                                    <option value="alto" <?php echo $animal['nivel_energia'] === 'alto' ? 'selected' : ''; ?>>Alto</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Relación con niños *</label>
                                <select class="form-select" name="relacion_ninos" required>
                                    <option value="excelente" <?php echo $animal['relacion_ninos'] === 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="buena" <?php echo $animal['relacion_ninos'] === 'buena' ? 'selected' : ''; ?>>Buena</option>
                                    <option value="regular" <?php echo $animal['relacion_ninos'] === 'regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="mala" <?php echo $animal['relacion_ninos'] === 'mala' ? 'selected' : ''; ?>>Mala</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Relación con otros animales *</label>
                                <select class="form-select" name="relacion_otros_animales" required>
                                    <option value="excelente" <?php echo $animal['relacion_otros_animales'] === 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="buena" <?php echo $animal['relacion_otros_animales'] === 'buena' ? 'selected' : ''; ?>>Buena</option>
                                    <option value="regular" <?php echo $animal['relacion_otros_animales'] === 'regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="mala" <?php echo $animal['relacion_otros_animales'] === 'mala' ? 'selected' : ''; ?>>Mala</option>
                                </select>
                            </div>
                        </div>

                        <!-- Descripción y necesidades -->
                        <div class="mb-3">
                            <label class="form-label">Descripción *</label>
                            <textarea class="form-control" name="descripcion" rows="4" placeholder="Describe al animal, su personalidad, historia, etc..." required><?php echo htmlspecialchars($animal['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Necesidades especiales</label>
                            <textarea class="form-control" name="necesidades_especiales" rows="3" placeholder="Alergias, medicación, cuidados especiales..."><?php echo htmlspecialchars($animal['necesidades_especiales'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- Fotos actuales -->
                        <?php if (!empty($fotos)): ?>
                        <h5 class="text-success mb-3 mt-4">Fotos Actuales</h5>
                        <div class="row mb-3">
                            <?php foreach ($fotos as $index => $foto): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="../uploads/animals/<?php echo htmlspecialchars($foto['ruta_foto'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="Foto animal" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2">
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-success mb-2">Principal</span>
                                            <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminarFoto(<?php echo $foto['id']; ?>)">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                                <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Agregar nuevas fotos -->
                        <h5 class="text-success mb-3 mt-4">Agregar Nuevas Fotos</h5>
                        <div class="mb-3">
                            <label class="form-label">Subir fotos del animal (máximo 4)</label>
                            <input type="file" class="form-control" name="fotos[]" multiple accept="image/*" id="fotosInput">
                            <div class="form-text">Puedes seleccionar hasta 4 fotos nuevas. Si subes fotos nuevas, se agregarán a las existentes.</div>
                            <div id="contadorFotos" class="form-text text-muted">0/4 fotos seleccionadas</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Guardar Cambios</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function confirmarEliminarFoto(fotoId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta foto?')) {
        // Crear un formulario temporal para enviar la solicitud
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../processes/animal_process.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'eliminar_foto';
        
        const fotoIdInput = document.createElement('input');
        fotoIdInput.type = 'hidden';
        fotoIdInput.name = 'foto_id';
        fotoIdInput.value = fotoId;
        
        const animalIdInput = document.createElement('input');
        animalIdInput.type = 'hidden';
        animalIdInput.name = 'animal_id';
        animalIdInput.value = <?php echo $animal_id; ?>;
        
        form.appendChild(actionInput);
        form.appendChild(fotoIdInput);
        form.appendChild(animalIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Contador de fotos
document.getElementById('fotosInput')?.addEventListener('change', function() {
    const files = this.files;
    const maxFotos = 4;
    
    if (files.length > maxFotos) {
        alert(`Máximo ${maxFotos} fotos permitidas. Seleccionaste ${files.length}`);
        this.value = '';
        document.getElementById('contadorFotos').textContent = '0/4 fotos seleccionadas';
        return;
    }
    
    let fotosValidas = 0;
    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            alert(`El archivo "${file.name}" no es una imagen.`);
            this.value = '';
            document.getElementById('contadorFotos').textContent = '0/4 fotos seleccionadas';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert(`El archivo "${file.name}" excede 5MB.`);
            this.value = '';
            document.getElementById('contadorFotos').textContent = '0/4 fotos seleccionadas';
            return;
        }
        
        fotosValidas++;
    }
    
    document.getElementById('contadorFotos').textContent = `${fotosValidas}/4 fotos seleccionadas`;
});
</script>
