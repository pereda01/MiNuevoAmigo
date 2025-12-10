// Para formularios de editar animal que puedan no tener fotos nuevas
function validarFormularioAnimal() {
    const formEditar = document.getElementById('formEditar');
    
    // Solo validar fotos en el formulario de edición
    if (formEditar) {
        const fotosInputs = document.querySelectorAll('input[type="file"][name="fotos[]"]');
        
        formEditar.addEventListener('submit', function(e) {
            // En edición, validar solo que los archivos sean válidos si se suben
            for (let input of fotosInputs) {
                if (input.files && input.files.length > 0) {
                    const file = input.files[0];
                    if (!file.type.startsWith('image/')) {
                        e.preventDefault();
                        alert(`El archivo "${file.name}" no es una imagen. Solo se permiten imágenes.`);
                        return false;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        e.preventDefault();
                        alert(`El archivo "${file.name}" excede 5MB. Selecciona imágenes más pequeñas.`);
                        return false;
                    }
                }
            }
            
            // Validar que haya al menos 1 foto (existente o nueva)
            let totalFotos = 0;
            
            // Contar fotos existentes (data-foto-id no vacío)
            document.querySelectorAll('.foto-slot').forEach((slot) => {
                if (slot.dataset.fotoId && slot.dataset.fotoId.trim() !== '') {
                    totalFotos++;
                }
            });
            
            // Contar fotos nuevas seleccionadas
            document.querySelectorAll('input[type="file"][name="fotos[]"]').forEach((inp) => {
                if (inp.files && inp.files.length > 0) {
                    totalFotos++;
                }
            });
            
            if (totalFotos === 0) {
                e.preventDefault();
                alert('El animal debe tener al menos una foto. Por favor, sube una foto.');
                return false;
            }
        });
    }
}

// Validación del formulario de registro
function initRegisterForm() {
    const formRegistro = document.getElementById('formRegistro');
    if (!formRegistro) return;

    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const adoptanteRadio = document.getElementById('adoptante');
    const refugioRadio = document.getElementById('refugio');
    const adoptanteInfo = document.getElementById('adoptante-info');
    const refugioInfo = document.getElementById('refugio-info');

    // Mostrar/ocultar información según tipo de cuenta
    if (adoptanteRadio && refugioRadio) {
        adoptanteRadio.addEventListener('change', function() {
            adoptanteInfo.style.display = 'block';
            refugioInfo.style.display = 'none';
        });
        refugioRadio.addEventListener('change', function() {
            adoptanteInfo.style.display = 'none';
            refugioInfo.style.display = 'block';
        });
    }

    // Validar al enviar el formulario
    formRegistro.addEventListener('submit', function(e) {
        // Validar que las contraseñas coincidan
        if (passwordInput.value !== passwordConfirmInput.value) {
            e.preventDefault();
            // Redirigir con error
            window.location.href = '../pages/register.php?error=' + encodeURIComponent('Las contraseñas no coinciden');
            return false;
        }

        // Si todo es válido, el formulario se envía
        return true;
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    validarFormularioAnimal();
    initRegisterForm();
});