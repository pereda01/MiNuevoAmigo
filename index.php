<?php
require_once 'includes/header.php';
?>

  <!-- Sección principal -->
  <header class="hero-section">
    <div class="container">
      <h1 class="display-4 fw-bold">¡Bienvenido a MiNuevoAmigo!</h1>
      <p class="lead mb-4">Conecta con tu compañero ideal y ofrece un hogar a quienes más lo necesitan.</p>
      <a href="pages/animals.php" class="btn btn-light btn-lg m-2">Explorar Animales</a>
      <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="pages/register.php" class="btn btn-outline-light btn-lg m-2">Regístrate</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Sección de información -->
  <section class="container my-5">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="images/cachorro1.jpg" 
             alt="Adopta una mascota" class="img-fluid rounded">
      </div>
      <div class="col-md-6">
        <h2 class="fw-bold text-success">¿Por qué adoptar?</h2>
        <p class="fs-5">Adoptar salva vidas. Hay miles de animales esperando una segunda oportunidad. Con nuestra plataforma, puedes buscar fácilmente una mascota que se adapte a tu estilo de vida, contactar con refugios y hacer el proceso de adopción más humano y accesible.</p>
      </div>
    </div>
  </section>

  <!-- Sección de Valores -->
  <section class="container my-5 bg-light p-4 rounded">
    <div class="text-center mb-4">
      <h2 class="fw-bold text-success">Nuestros Valores</h2>
      <p class="text-muted fs-5">Conoce lo que hace especial a MiNuevoAmigo</p>
    </div>

    <div class="row text-center">
      <!-- Card 1 -->
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/refugio.jpg" alt="Buscar" class="img-fluid card-image mb-3">
          <h4>Refugios Verificados</h4>
          <p>Trabajamos solo con refugios y dueños responsables para asegurar el bienestar de los animales.</p>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/gatoCorazon.jpg" alt="Buscar" class="img-fluid card-image mb-3">
          <h4>Adopciones Responsables</h4>
          <p>Fomentamos la adopción consciente para crear lazos duraderos entre personas y mascotas.</p>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/animales_bonitos.png" alt="Buscar" class="img-fluid card-image mb-3">
          <h4>Búsqueda Personalizada</h4>
          <p>Encuentra a tu compañero ideal con filtros de tipo, edad y tamaño para una mejor compatibilidad.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección Cómo funciona -->
  <section class="container my-5 bg-light p-4 rounded">
    <div class="text-center mb-4">
      <h2 class="fw-bold text-success">¿Cómo funciona?</h2>
      <p class="text-muted fs-5">Tres pasos sencillos para encontrar a tu compañero ideal.</p>
    </div>

      <!-- Card 1 -->
    <div class="row text-center">
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/buscar.jpeg" alt="Buscar" class="img-fluid card-image mb-3">
          <h4>Buscar</h4>
          <p>Explora perfiles filtrando por tipo, edad y tamaño para encontrar mascotas compatibles.</p>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/contactar.jpg" alt="Contactar" class="img-fluid card-image mb-3">
          <h4>Contactar</h4>
          <p>Envía una solicitud o mensaje al refugio para coordinar una visita o conocer más.</p>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="col-md-4 mb-4">
        <div class="sombra-card p-4 h-100">
          <img src="images/adoptar.jpg" alt="Adoptar" class="img-fluid card-image mb-3">
          <h4>Adoptar</h4>
          <p>Completa el proceso con el refugio y brinda un hogar responsable y cariñoso.</p>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="pages/animals.php" class="btn btn-success btn-lg">Ver animales disponibles</a>
    </div>
  </section>

<?php
require_once 'includes/footer.php';
?>