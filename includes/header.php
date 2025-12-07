<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validar que si existe user_id, el usuario realmente existe
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/database.php';
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT id FROM usuarios WHERE id = '$user_id' LIMIT 1");
    if ($check->num_rows === 0) {
        // Usuario no existe, destruir sesión
        $_SESSION = array();
        session_destroy();
        header("Location: " . (strpos($_SERVER['REQUEST_URI'], 'pages/') !== false ? '../index.php' : 'index.php'));
        exit();
    }
}

// Determinar la ruta base según la ubicación actual
$current_dir = dirname($_SERVER['PHP_SELF']);
$is_in_pages = strpos($current_dir, 'pages') !== false;
$base_path = $is_in_pages ? '../' : '';

// Determinar rutas para navegación
$index_path = $base_path . 'index.php';
$animals_path = $base_path . 'pages/animals.php';
$login_path = $base_path . 'pages/login.php';
$register_path = $base_path . 'pages/register.php';
$profile_path = $base_path . 'pages/profile.php';
$logout_path = $base_path . 'pages/logout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>MiNuevoAmigo - Adopta una Mascota</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css?v=<?php echo @filemtime(__DIR__ . '/../css/style.css'); ?>" />
</head>
<body>

  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
      <a class="fw-bold d-flex align-items-center" href="<?php echo $index_path; ?>">
        <img src="<?php echo $base_path; ?>images/logo.png" alt="MiNuevoAmigo" class="navbar-logo">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav ms-auto fs-5">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $index_path; ?>"><span class="nav-emoji" aria-hidden="true">🏠</span> Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $animals_path; ?>"><span class="nav-emoji" aria-hidden="true">🐾</span> Animales</a>
          </li>
          <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $profile_path; ?>"><span class="nav-emoji" aria-hidden="true">👤</span> Mi Perfil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $logout_path; ?>"><span class="nav-emoji" aria-hidden="true">🔒</span> Cerrar Sesión</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $login_path; ?>"><span class="nav-emoji" aria-hidden="true">🔑</span> Iniciar sesión</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $register_path; ?>"><span class="nav-emoji" aria-hidden="true">✍️</span> Registrarse</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>