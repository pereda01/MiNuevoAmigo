// Validación del formulario de agregar/editar animal
function validarFormularioAnimal() {
    const form = document.getElementById('formAgregar') || document.getElementById('formEditar');
    const fotosInput = document.getElementById('fotosInput');
    const contadorFotos = document.getElementById('contadorFotos');
    const previewContainer = document.getElementById('previewContainer');
    const fotosPreview = document.getElementById('fotosPreview');

    // Contador de fotos en tiempo real y vista previa
    if (fotosInput) {
        fotosInput.addEventListener('change', function() {
            const files = this.files;
            const maxFotos = 4;

            // Limpiar vista previa anterior
            if (fotosPreview) {
                fotosPreview.innerHTML = '';
            }

            // Validar número de fotos
            if (files.length > maxFotos) {
                alert(`Máximo ${maxFotos} fotos permitidas. Seleccionaste ${files.length}`);
                this.value = '';
                contadorFotos.textContent = '0/4 fotos seleccionadas';
                if (previewContainer) previewContainer.style.display = 'none';
                return;
            }

            // Validar tipos de archivo
            let fotosValidas = 0;
            for (let file of files) {
                if (!file.type.startsWith('image/')) {
                    alert(`El archivo "${file.name}" no es una imagen. Solo se permiten imágenes.`);
                    this.value = '';
                    contadorFotos.textContent = '0/4 fotos seleccionadas';
                    if (previewContainer) previewContainer.style.display = 'none';
                    return;
                }

                // Validar tamaño (máximo 5MB por archivo)
                if (file.size > 5 * 1024 * 1024) {
                    alert(`El archivo "${file.name}" excede 5MB. Por favor selecciona imágenes más pequeñas.`);
                    this.value = '';
                    contadorFotos.textContent = '0/4 fotos seleccionadas';
                    if (previewContainer) previewContainer.style.display = 'none';
                    return;
                }

                // Crear vista previa
                if (fotosPreview) {
                    const reader = new FileReader();
                    reader.onload = (function(foto, index) {
                        return function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 mb-3';
                            const isPrincipal = index === 0 ? '<span class="badge bg-warning text-dark mb-2">Principal</span>' : '';
                            col.innerHTML = `
                                <div class="card">
                                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        ${isPrincipal}
                                        <small class="text-muted d-block text-truncate" title="${foto.name}">${foto.name}</small>
                                    </div>
                                </div>
                            `;
                            fotosPreview.appendChild(col);
                        };
                    })(file, fotosValidas);
                    reader.readAsDataURL(file);
                }

                fotosValidas++;
            }

            // Actualizar contador y mostrar/ocultar preview
            contadorFotos.textContent = `${fotosValidas}/4 fotos seleccionadas`;
            if (previewContainer && fotosValidas > 0) {
                previewContainer.style.display = 'block';
            } else if (previewContainer) {
                previewContainer.style.display = 'none';
            }
        });
    }

    // Validación del formulario al enviar
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validar que al menos un campo requerido esté lleno
            const nombre = document.querySelector('input[name="nombre"]');
            const tipo = document.querySelector('select[name="tipo"]');
            const edad = document.querySelector('select[name="edad_categoria"]');
            const descripcion = document.querySelector('textarea[name="descripcion"]');

            // Validar nombre
            if (!nombre.value.trim()) {
                e.preventDefault();
                alert('Por favor ingresa el nombre del animal');
                nombre.focus();
                return false;
            }

            // Validar longitud mínima del nombre
            if (nombre.value.trim().length < 2) {
                e.preventDefault();
                alert('El nombre debe tener al menos 2 caracteres');
                nombre.focus();
                return false;
            }

            // Validar tipo
            if (!tipo.value) {
                e.preventDefault();
                alert('Por favor selecciona el tipo de animal');
                tipo.focus();
                return false;
            }

            // Validar edad
            if (!edad.value) {
                e.preventDefault();
                alert('Por favor selecciona la edad del animal');
                edad.focus();
                return false;
            }

            // Validar descripción
            if (!descripcion.value.trim()) {
                e.preventDefault();
                alert('Por favor ingresa una descripción del animal');
                descripcion.focus();
                return false;
            }

            if (descripcion.value.trim().length < 10) {
                e.preventDefault();
                alert('La descripción debe tener al menos 10 caracteres');
                descripcion.focus();
                return false;
            }

            // En agregar_animal, validar que haya al menos una foto
            // En editar_animal, es opcional (puede mantener las existentes)
            const isEditar = document.getElementById('formEditar') !== null;
            if (!isEditar && fotosInput && fotosInput.files.length === 0) {
                e.preventDefault();
                alert('Por favor sube al menos una foto del animal');
                fotosInput.focus();
                return false;
            }

            // Si todo es válido, el formulario se envía
            console.log('Formulario válido. Enviando datos...');
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    validarFormularioAnimal();
    initRegisterForm();
});
