<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $refugio_id = intval($_POST['refugio_id'] ?? 0);

    // Validar que el usuario sea refugio y que el refugio_id coincida
    $session_user_id = intval($_SESSION['user_id'] ?? 0);
    $session_user_type = $_SESSION['user_type'] ?? '';
    if (!usuarioLogueado() || $session_user_type !== 'refugio' || $session_user_id !== $refugio_id) {
        header("Location: ../pages/agregar_animal.php?error=no_autorizado");
        exit();
    }

    if ($action === 'agregar') {
        // Recoger y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $edad_categoria = trim($_POST['edad_categoria'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        // Corregir valor de tamaño para que coincida con el enum de la base de datos
        $tamano = trim($_POST['tamano'] ?? '');
        if ($tamano === 'pequeno') $tamano = 'pequeño';
        $raza = trim($_POST['raza'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $vacunado = isset($_POST['vacunado']) ? 1 : 0;
        $vacunas = trim($_POST['vacunas'] ?? '');
        $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
        $nivel_energia = trim($_POST['nivel_energia'] ?? '');
        $relacion_ninos = trim($_POST['relacion_ninos'] ?? '');
        $relacion_otros_animales = trim($_POST['relacion_otros_animales'] ?? '');
        $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
        $necesidades_especiales = trim($_POST['necesidades_especiales'] ?? '');

        // Validación de campos requeridos
        $errores = [];
        if (strlen($nombre) < 2) $errores[] = 'Nombre inválido';
        if (empty($tipo)) $errores[] = 'Tipo requerido';
        if (empty($edad_categoria)) $errores[] = 'Edad requerida';
        if (empty($sexo)) $errores[] = 'Sexo requerido';
        if (empty($tamano)) $errores[] = 'Tamaño requerido';
        if (strlen($descripcion) < 10) $errores[] = 'Descripción muy corta';
        // Comprobar si hay al menos una foto entre los inputs
        $hayFoto = false;
        if (!empty($_FILES['fotos']['name']) && is_array($_FILES['fotos']['name'])) {
            foreach ($_FILES['fotos']['name'] as $fname) {
                if (!empty($fname)) { $hayFoto = true; break; }
            }
        }
        if (!$hayFoto) $errores[] = 'Al menos una foto requerida';

        // Validar fotos (recorrer cada entrada y validar las que tengan nombre)
        $totalFotos = 0;
        if (!empty($_FILES['fotos']['name']) && is_array($_FILES['fotos']['name'])) {
            foreach ($_FILES['fotos']['name'] as $key => $fname) {
                if (empty($fname)) continue; // input vacío
                $totalFotos++;
                // comprobar errores y datos
                if (!isset($_FILES['fotos']['error'][$key]) || $_FILES['fotos']['error'][$key] !== 0) {
                    $errores[] = 'Error en subida de archivo';
                    continue;
                }
                $file_size = $_FILES['fotos']['size'][$key];
                $tmp_name = $_FILES['fotos']['tmp_name'][$key];
                $file_type = mime_content_type($tmp_name);
                if ($file_size > 5 * 1024 * 1024) {
                    $errores[] = 'Una o más fotos exceden 5MB';
                }
                if (strpos($file_type, 'image/') === false) {
                    $errores[] = 'Solo se permiten imágenes';
                }
            }
            if ($totalFotos > 4) $errores[] = 'Máximo 4 fotos permitidas';
        }

        if (!empty($errores)) {
            header("Location: ../pages/agregar_animal.php?error=" . urlencode(implode(' | ', $errores)));
            exit();
        }

        // Insertar animal con prepared statement
        $stmt = $conn->prepare("INSERT INTO animales (nombre, tipo, edad_categoria, sexo, raza, tamano, descripcion, 
                                                     vacunado, vacunas, esterilizado, nivel_energia, relacion_ninos, 
                                                     relacion_otros_animales, peso, necesidades_especiales, id_refugio, estado) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'disponible')");

        // Tipos: nombre(s), tipo(s), edad_categoria(s), sexo(s), raza(s), tamano(s), descripcion(s),
        // vacunado(i), vacunas(s), esterilizado(i), nivel_energia(s), relacion_ninos(s),
        // relacion_otros_animales(s), peso(d), necesidades_especiales(s), id_refugio(i)
        $stmt->bind_param("sssssssisisssdsi", 
            $nombre, $tipo, $edad_categoria, $sexo, $raza, $tamano, $descripcion,
            $vacunado, $vacunas, $esterilizado, $nivel_energia, $relacion_ninos,
            $relacion_otros_animales, $peso, $necesidades_especiales, $refugio_id);

        if ($stmt->execute()) {
            $animal_id = $conn->insert_id;
            $stmt->close();
            // Procesar fotos si se subieron
            if (!empty($_FILES['fotos']['name']) && is_array($_FILES['fotos']['name'])) {
                // comprobar si hay al menos una foto no vacía antes de llamar a procesarFotos
                $hayFoto = false;
                foreach ($_FILES['fotos']['name'] as $fn) { if (!empty($fn)) { $hayFoto = true; break; } }
                if ($hayFoto) {
                    if (!procesarFotos($animal_id, $conn)) {
                        header("Location: ../pages/agregar_animal.php?error=fallo_fotos");
                        exit();
                    }
                }
            }

            header("Location: ../pages/animals.php?success=animal_agregado");
        } else {
            header("Location: ../pages/agregar_animal.php?error=" . urlencode('guardar_fallo: ' . $stmt->error));
        }
        exit();
    } elseif ($action === 'editar') {
        // Acción para editar animal
        $animal_id = intval($_POST['animal_id'] ?? 0);
        
        // Verificar que el animal existe y pertenece al refugio
        $stmt = $conn->prepare("SELECT id FROM animales WHERE id = ? AND id_refugio = ?");
        $stmt->bind_param("ii", $animal_id, $refugio_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            header("Location: ../pages/editar_animal.php?id=$animal_id&error=no_autorizado");
            exit();
        }
        $stmt->close();

        // Recoger y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $edad_categoria = trim($_POST['edad_categoria'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        $tamano = trim($_POST['tamano'] ?? '');
        $raza = trim($_POST['raza'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $vacunado = isset($_POST['vacunado']) ? 1 : 0;
        $vacunas = trim($_POST['vacunas'] ?? '');
        $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
        $nivel_energia = trim($_POST['nivel_energia'] ?? '');
        $relacion_ninos = trim($_POST['relacion_ninos'] ?? '');
        $relacion_otros_animales = trim($_POST['relacion_otros_animales'] ?? '');
        $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
        $necesidades_especiales = trim($_POST['necesidades_especiales'] ?? '');

        // Validación de campos requeridos
        $errores = [];
        if (strlen($nombre) < 2) $errores[] = 'Nombre inválido';
        if (empty($tipo)) $errores[] = 'Tipo requerido';
        if (empty($edad_categoria)) $errores[] = 'Edad requerida';
        if (empty($sexo)) $errores[] = 'Sexo requerido';
        if (empty($tamano)) $errores[] = 'Tamaño requerido';
        if (strlen($descripcion) < 10) $errores[] = 'Descripción muy corta';

        // Validar nuevas fotos si se subieron
        if (!empty($_FILES['fotos']['name'][0])) {
            if (count($_FILES['fotos']['name']) > 4) {
                $errores[] = 'Máximo 4 fotos permitidas en total';
            }
            foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['fotos']['error'][$key] === 0) {
                    $file_size = $_FILES['fotos']['size'][$key];
                    $file_type = mime_content_type($tmp_name);
                    
                    if ($file_size > 5 * 1024 * 1024) {
                        $errores[] = 'Una o más fotos exceden 5MB';
                    }
                    if (strpos($file_type, 'image/') === false) {
                        $errores[] = 'Solo se permiten imágenes';
                    }
                }
            }
        }

        if (!empty($errores)) {
            header("Location: ../pages/editar_animal.php?id=$animal_id&error=" . urlencode(implode(', ', $errores)));
            exit();
        }

        // Actualizar animal con prepared statement
        $stmt = $conn->prepare("UPDATE animales SET nombre = ?, tipo = ?, edad_categoria = ?, sexo = ?, raza = ?, 
                               tamano = ?, descripcion = ?, vacunado = ?, vacunas = ?, esterilizado = ?, 
                               nivel_energia = ?, relacion_ninos = ?, relacion_otros_animales = ?, peso = ?, 
                               necesidades_especiales = ? WHERE id = ? AND id_refugio = ?");

        $stmt->bind_param("sssssssisisisidii", 
            $nombre, $tipo, $edad_categoria, $sexo, $raza, $tamano, $descripcion,
            $vacunado, $vacunas, $esterilizado, $nivel_energia, $relacion_ninos,
            $relacion_otros_animales, $peso, $necesidades_especiales, $animal_id, $refugio_id);

        if ($stmt->execute()) {
            $stmt->close();
            
            // Eliminar fotos si se solicita
            if (!empty($_POST['fotos_eliminar'])) {
                $fotos_a_eliminar = $_POST['fotos_eliminar'];
                foreach ($fotos_a_eliminar as $foto_id) {
                    $foto_id = intval($foto_id);
                    $stmt = $conn->prepare("SELECT ruta_foto FROM fotos_animales WHERE id = ? AND id_animal = ?");
                    $stmt->bind_param("ii", $foto_id, $animal_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $foto = $result->fetch_assoc();
                        $file_path = '../uploads/animals/' . $foto['ruta_foto'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        $del_stmt = $conn->prepare("DELETE FROM fotos_animales WHERE id = ?");
                        $del_stmt->bind_param("i", $foto_id);
                        $del_stmt->execute();
                        $del_stmt->close();
                    }
                    $stmt->close();
                }
            }
            
            // Procesar nuevas fotos si se subieron
            if (!empty($_FILES['fotos']['name'][0])) {
                procesarFotos($animal_id, $conn);
            }

            header("Location: ../pages/dashboard.php?success=animal_actualizado");
        } else {
            header("Location: ../pages/editar_animal.php?id=$animal_id&error=fallo_actualizar");
        }
        exit();
    }
}

function procesarFotos($animal_id, $conn) {
    $upload_dir = '../uploads/animals/';
    
    // Crear directorio si no existe
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            return false;
        }
    }

    $fotos_guardadas = 0;
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['fotos']['error'][$key] === 0) {
            // Sanitizar nombre de archivo
            $original_name = pathinfo($_FILES['fotos']['name'][$key], PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION));
            $file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $original_name) . '.' . $extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO fotos_animales (id_animal, ruta_foto) VALUES (?, ?)");
                $stmt->bind_param("is", $animal_id, $file_name);
                if ($stmt->execute()) {
                    $fotos_guardadas++;
                }
                $stmt->close();
            }
        }
    }

    return $fotos_guardadas > 0;

}

// Acción para eliminar foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar_foto') {
    $foto_id = intval($_POST['foto_id'] ?? 0);
    $animal_id = intval($_POST['animal_id'] ?? 0);
    $session_user_id = intval($_SESSION['user_id'] ?? 0);
    
    if ($foto_id === 0 || $animal_id === 0) {
        header("Location: ../pages/editar_animal.php?id=$animal_id&error=id_invalido");
        exit();
    }

    // Verificar que la foto pertenece a un animal del refugio
    $stmt = $conn->prepare("SELECT fa.id, fa.ruta_foto FROM fotos_animales fa 
                           JOIN animales a ON fa.id_animal = a.id 
                           WHERE fa.id = ? AND fa.id_animal = ? AND a.id_refugio = ?");
    $stmt->bind_param("iii", $foto_id, $animal_id, $session_user_id);
    $stmt->execute();
    $foto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$foto) {
        header("Location: ../pages/editar_animal.php?id=$animal_id&error=no_autorizado");
        exit();
    }

    // Eliminar archivo del servidor
    $upload_dir = '../uploads/animals/';
    $file_path = $upload_dir . $foto['ruta_foto'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Eliminar registro de foto de la BD
    $stmt = $conn->prepare("DELETE FROM fotos_animales WHERE id = ? AND id_animal = ?");
    $stmt->bind_param("ii", $foto_id, $animal_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../pages/editar_animal.php?id=$animal_id&success=foto_eliminada");
    } else {
        header("Location: ../pages/editar_animal.php?id=$animal_id&error=fallo_eliminar");
    }
    exit();
}

$conn->close();
?>