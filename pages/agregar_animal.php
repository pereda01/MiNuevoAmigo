<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Verificar que el usuario sea un refugio
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'refugio') {
    header("Location: login.php");
    exit();
}

$refugio_id = $_SESSION['user_id'];

// Mostrar error si existe
$errorMessage = '';
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}

// Prefill datos si vienen en GET (después de error)
$prefill = [
    'nombre' => isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '',
    'tipo' => isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : '',
    'edad_categoria' => isset($_GET['edad_categoria']) ? htmlspecialchars($_GET['edad_categoria']) : '',
    'sexo' => isset($_GET['sexo']) ? htmlspecialchars($_GET['sexo']) : '',
    'tamano' => isset($_GET['tamano']) ? htmlspecialchars($_GET['tamano']) : '',
    'raza' => isset($_GET['raza']) ? htmlspecialchars($_GET['raza']) : '',
    'peso' => isset($_GET['peso']) ? htmlspecialchars($_GET['peso']) : '',
    'vacunado' => isset($_GET['vacunado']) ? htmlspecialchars($_GET['vacunado']) : '',
    'vacunas' => isset($_GET['vacunas']) ? htmlspecialchars($_GET['vacunas']) : '',
    'esterilizado' => isset($_GET['esterilizado']) ? htmlspecialchars($_GET['esterilizado']) : '',
    'nivel_energia' => isset($_GET['nivel_energia']) ? htmlspecialchars($_GET['nivel_energia']) : '',
    'relacion_ninos' => isset($_GET['relacion_ninos']) ? htmlspecialchars($_GET['relacion_ninos']) : '',
    'relacion_otros_animales' => isset($_GET['relacion_otros_animales']) ? htmlspecialchars($_GET['relacion_otros_animales']) : '',
    'descripcion' => isset($_GET['descripcion']) ? htmlspecialchars($_GET['descripcion']) : '',
    'necesidades_especiales' => isset($_GET['necesidades_especiales']) ? htmlspecialchars($_GET['necesidades_especiales']) : ''
];
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card sombra-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">➕ Agregar Nuevo Animal</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertaError">
                            <?php echo $errorMessage; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../processes/animal_process.php" method="POST" enctype="multipart/form-data" id="formAgregar">
                        <input type="hidden" name="action" value="agregar">
                        <input type="hidden" name="refugio_id" value="<?php echo htmlspecialchars($refugio_id, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Información básica -->
                        <h5 class="text-success mb-3">Información Básica</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del animal *</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo $prefill['nombre']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="perro" <?php echo $prefill['tipo'] === 'perro' ? 'selected' : ''; ?>>Perro</option>
                                    <option value="gato" <?php echo $prefill['tipo'] === 'gato' ? 'selected' : ''; ?>>Gato</option>
                                    <option value="otro" <?php echo $prefill['tipo'] === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Edad *</label>
                                <select class="form-select" name="edad_categoria" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="cachorro" <?php echo $prefill['edad_categoria'] === 'cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                    <option value="joven" <?php echo $prefill['edad_categoria'] === 'joven' ? 'selected' : ''; ?>>Joven</option>
                                    <option value="adulto" <?php echo $prefill['edad_categoria'] === 'adulto' ? 'selected' : ''; ?>>Adulto</option>
                                    <option value="mayor" <?php echo $prefill['edad_categoria'] === 'mayor' ? 'selected' : ''; ?>>Mayor</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sexo *</label>
                                <select class="form-select" name="sexo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="macho" <?php echo $prefill['sexo'] === 'macho' ? 'selected' : ''; ?>>Macho</option>
                                    <option value="hembra" <?php echo $prefill['sexo'] === 'hembra' ? 'selected' : ''; ?>>Hembra</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tamaño *</label>
                                <select class="form-select" name="tamano" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="pequeño" <?php echo $prefill['tamano'] === 'pequeño' ? 'selected' : ''; ?>>Pequeño</option>
                                    <option value="mediano" <?php echo $prefill['tamano'] === 'mediano' ? 'selected' : ''; ?>>Mediano</option>
                                    <option value="grande" <?php echo $prefill['tamano'] === 'grande' ? 'selected' : ''; ?>>Grande</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Raza</label>
                                <input type="text" class="form-control" name="raza" value="<?php echo $prefill['raza']; ?>" placeholder="Ej: Mestizo, Labrador, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" name="peso" value="<?php echo $prefill['peso']; ?>" step="0.1" min="0">
                            </div>
                        </div>

                        <!-- Salud -->
                        <h5 class="text-success mb-3 mt-4">Salud</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="vacunado" value="1" <?php echo $prefill['vacunado'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">¿Está vacunado?</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="esterilizado" value="1" <?php echo $prefill['esterilizado'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">¿Está esterilizado?</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vacunas aplicadas</label>
                            <input type="text" class="form-control" name="vacunas" value="<?php echo $prefill['vacunas']; ?>"
                                   placeholder="Ej: Rabia, Polivalente, Moquillo...">
                        </div>

                        <!-- Personalidad -->
                        <h5 class="text-success mb-3 mt-4">Personalidad</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nivel de energía *</label>
                                <select class="form-select" name="nivel_energia" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="bajo" <?php echo $prefill['nivel_energia'] === 'bajo' ? 'selected' : ''; ?>>Bajo</option>
                                    <option value="medio" <?php echo $prefill['nivel_energia'] === 'medio' ? 'selected' : ''; ?>>Medio</option>
                                    <option value="alto" <?php echo $prefill['nivel_energia'] === 'alto' ? 'selected' : ''; ?>>Alto</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Relación con niños *</label>
                                <select class="form-select" name="relacion_ninos" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="excelente" <?php echo $prefill['relacion_ninos'] === 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="buena" <?php echo $prefill['relacion_ninos'] === 'buena' ? 'selected' : ''; ?>>Buena</option>
                                    <option value="regular" <?php echo $prefill['relacion_ninos'] === 'regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="mala" <?php echo $prefill['relacion_ninos'] === 'mala' ? 'selected' : ''; ?>>Mala</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Relación con otros animales *</label>
                                <select class="form-select" name="relacion_otros_animales" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="excelente" <?php echo $prefill['relacion_otros_animales'] === 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="buena" <?php echo $prefill['relacion_otros_animales'] === 'buena' ? 'selected' : ''; ?>>Buena</option>
                                    <option value="regular" <?php echo $prefill['relacion_otros_animales'] === 'regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="mala" <?php echo $prefill['relacion_otros_animales'] === 'mala' ? 'selected' : ''; ?>>Mala</option>
                                </select>
                            </div>
                        </div>

                        <!-- Descripción y necesidades -->
                        <div class="mb-3">
                            <label class="form-label">Descripción *</label>
                            <textarea class="form-control" name="descripcion" rows="4" minlength="10"
                                      placeholder="Describe al animal, su personalidad, historia, etc..." 
                                      required><?php echo $prefill['descripcion']; ?></textarea>
                            <small class="form-text text-muted">Mínimo 10 caracteres</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Necesidades especiales</label>
                            <textarea class="form-control" name="necesidades_especiales" rows="3" 
                                      placeholder="Alergias, medicación, cuidados especiales..."><?php echo $prefill['necesidades_especiales']; ?></textarea>
                        </div>

                        <!-- Fotos -->
                        <h5 class="text-success mb-3 mt-4">Fotos del Animal (máximo 4)</h5>
                        <div class="row mb-4" id="fotosContainer">
                            <?php 
                            // Mostrar 4 slots vacíos para agregar fotos
                            for ($i = 0; $i < 4; $i++): 
                            ?>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <div class="card h-100 position-relative foto-slot" data-slot="<?php echo $i; ?>">
                                    <!-- Slot vacío para foto nueva -->
                                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" style="height: 180px; background-color: #f8f9fa; cursor: pointer;" onclick="document.getElementById('foto-input-<?php echo $i; ?>').click()">
                                        <div class="text-center text-muted">
                                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📸</div>
                                            <small>Haz clic para subir foto</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Inputs file ocultos para fotos -->
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <input type="file" id="foto-input-<?php echo $i; ?>" class="d-none" name="fotos[]" accept="image/*" <?php echo $i === 0 ? 'required' : ''; ?>>
                        <?php endfor; ?>
                        
                        <small class="form-text text-muted d-block mb-3">Formatos permitidos: JPG, PNG, GIF (máximo 5MB cada una) - Mínimo 1 foto requerida</small>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Guardar Animal</button>
                            <a href="animals.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Al subir archivo en slot vacío - mostrar preview
document.querySelectorAll('input[type="file"][name="fotos[]"]').forEach((input, index) => {
    input.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            const slot = document.querySelector(`.foto-slot[data-slot="${index}"]`);
            
            // Mostrar preview de la foto seleccionada
            const reader = new FileReader();
            reader.onload = function(e) {
                slot.innerHTML = `
                    <img src="${e.target.result}" class="card-img-top" alt="Preview" style="height: 150px; object-fit: cover;">
                    <div class="card-body p-2 text-center text-muted">
                        <small>Foto seleccionada</small>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>