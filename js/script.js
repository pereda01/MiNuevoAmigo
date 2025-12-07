// Validación del formulario de agregar/editar animal (4 inputs separados)
function validarFormularioAnimal() {
    const form = document.getElementById('formAgregar') || document.getElementById('formEditar');
    const fotosInputs = document.querySelectorAll('input[type="file"][name="fotos[]"]');

    // Mostrar errores en el bloque de alerta visual
    function mostrarError(msg) {
        const alertaError = document.getElementById('alertaError');
        const mensajeError = document.getElementById('mensajeError');
        if (alertaError && mensajeError) {
            mensajeError.textContent = msg;
            alertaError.classList.add('show');
            alertaError.classList.remove('fade');
            alertaError.style.display = 'block';
        } else {
            alert(msg);
        }
    }

    function validarFotos() {
        let fotosSeleccionadas = 0;
        for (let input of fotosInputs) {
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                // Validar tipo
                if (!file.type.startsWith('image/')) {
                    mostrarError(`El archivo "${file.name}" no es una imagen. Solo se permiten imágenes.`);
                    input.value = '';
                    return false;
                }
                // Validar tamaño (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    mostrarError(`El archivo "${file.name}" excede 5MB. Selecciona imágenes más pequeñas.`);
                    input.value = '';
                    return false;
                }
                fotosSeleccionadas++;
            }
        }
        if (fotosSeleccionadas === 0) {
            mostrarError('Por favor sube al menos una foto del animal');
            if (fotosInputs[0]) fotosInputs[0].focus();
            return false;
        }
        if (fotosSeleccionadas > 4) {
            mostrarError('Máximo 4 fotos permitidas');
            return false;
        }
        return true;
    }

    // Validación del formulario al enviar
    if (form) {
        form.addEventListener('submit', function(e) {
            const nombre = document.querySelector('input[name="nombre"]');
            const tipo = document.querySelector('select[name="tipo"]');
            const edad = document.querySelector('select[name="edad_categoria"]');
            const descripcion = document.querySelector('textarea[name="descripcion"]');

            // Validar nombre
            if (!nombre.value.trim()) {
                e.preventDefault();
                mostrarError('Por favor ingresa el nombre del animal');
                nombre.focus();
                return false;
            }
            if (nombre.value.trim().length < 2) {
                e.preventDefault();
                mostrarError('El nombre debe tener al menos 2 caracteres');
                nombre.focus();
                return false;
            }
            // Validar tipo
            if (!tipo.value) {
                e.preventDefault();
                mostrarError('Por favor selecciona el tipo de animal');
                tipo.focus();
                return false;
            }
            // Validar edad
            if (!edad.value) {
                e.preventDefault();
                mostrarError('Por favor selecciona la edad del animal');
                edad.focus();
                return false;
            }
            // Validar descripción
            if (!descripcion.value.trim()) {
                e.preventDefault();
                mostrarError('Por favor ingresa una descripción del animal');
                descripcion.focus();
                return false;
            }
            if (descripcion.value.trim().length < 10) {
                e.preventDefault();
                mostrarError('La descripción debe tener al menos 10 caracteres');
                descripcion.focus();
                return false;
            }
            // Validar fotos
            const isEditar = document.getElementById('formEditar') !== null;
            if (!isEditar && !validarFotos()) {
                e.preventDefault();
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