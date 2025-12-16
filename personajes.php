<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'Conexiones/conexion.php';

$usuario_actual = $_SESSION['usuario'];
$sql_usuario = "SELECT usuario, correo, foto_perfil FROM usuario WHERE usuario = '" . $conn->real_escape_string($usuario_actual) . "'";
$resultado_usuario = $conn->query($sql_usuario);

$correo_usuario = '';
$foto_perfil = 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';

if ($resultado_usuario && $resultado_usuario->num_rows > 0) {
    $datos_usuario = $resultado_usuario->fetch_assoc();
    $correo_usuario = $datos_usuario['correo'];
    
    if (!empty($datos_usuario['foto_perfil'])) {
        $foto_perfil = $datos_usuario['foto_perfil'];
    }
}

// Función para formatear fechas
function formatearFecha($fecha) {
   if (empty($fecha) || $fecha === '0000-00-00') {
        return "Fecha desconocida";
    }
    
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    try {
        $fechaObj = new DateTime($fecha);
        $dia = $fechaObj->format('j');
        $mes = $meses[(int)$fechaObj->format('n')];
        $anio = $fechaObj->format('Y');
        
        return "$dia de $mes de $anio";
    } catch (Exception $e) {
        return "Fecha desconocida";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibertyMap - Personajes</title>
<link rel="stylesheet" href="Styles/personajes-style.css">
</head>

<body>

<!-- Barra Lateral -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="logo-container">
      <img src="Img/Logo Horizontal - LibertyMap.png" alt="LibertyMap" class="logo">
    </div>
  </div>

  <div class="user-profile">
    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" 
         alt="Foto de perfil" 
         class="user-avatar"
         onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
    <div class="user-info">
      <div class="user-name"><?php echo htmlspecialchars($usuario_actual); ?></div>
      <div class="user-email"><?php echo htmlspecialchars($correo_usuario); ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a href="mapa.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
      </svg>
      <span>Batallas</span>
    </a>
    <a href="personajes.php" class="nav-item active">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <span>Personajes</span>
    </a>
    <a href="territorios.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"></rect>
        <rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect>
        <rect x="3" y="14" width="7" height="7"></rect>
      </svg>
      <span>Territorios</span>
    </a>
    <a href="comunidad.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <span>Comunidad</span>
    </a>
    <a href="perfil.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <span>Mi Perfil</span>
    </a>
    <a href="contacto.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
        <polyline points="22,6 12,13 2,6"></polyline>
      </svg>
      <span>Contacto</span>
    </a>
    <a href="Manual de Usuario - LibertyMap.pdf" target="_blank" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
      </svg>
      <span>Ayuda</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php" class="nav-item logout">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      <span>Cerrar Sesión</span>
    </a>
  </div>
</div>

<!-- Contenido Principal -->
<div class="main-content">
  <!-- Barra Superior -->
  <div class="top-bar">
    <button class="btn-toggle-sidebar" id="toggleSidebar">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    <h1 class="page-title">Personajes Históricos</h1>
    <div class="top-bar-actions">
      <button class="btn-theme" id="btnTheme" onclick="toggleTheme()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
    </div>
  </div>

  <!-- Buscador -->
  <div class="search-container">
    <div class="search-box">
      <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      <input type="text" id="inputBuscador" class="search-input" placeholder="Buscar personaje por nombre..." autocomplete="off">
      <button class="btn-clear" id="btnLimpiar" onclick="limpiarBuscador()">×</button>
    </div>
    <div class="search-results" id="contadorResultados"></div>
  </div>

  <!-- Personajes -->
  <div class="characters-container">
    <div class="no-results" id="noResultados">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      <p>No se encontraron personajes</p>
    </div>

    <div class="characters-grid">
      <?php
      if ($conn->connect_error) {
          echo "<div class='error-message'>Error de conexión: " . htmlspecialchars($conn->connect_error) . "</div>";
      } else {
          $sql = "SELECT idPersonaje, nombre, aPat, aMat, fechaNac, fechaMuerte, descripcion, img, fuente FROM personaje ORDER BY nombre ASC";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) {
              $nombreCompleto = htmlspecialchars($row["nombre"]) . " " . 
                                htmlspecialchars($row["aPat"]) . " " . 
                                htmlspecialchars($row["aMat"]);
              
              // Formatear fechas
              $fechaNac = formatearFecha($row["fechaNac"]);
              $fechaMuerte = formatearFecha($row["fechaMuerte"]);
              $fuente = empty($row["fuente"]) ? "#" : htmlspecialchars($row["fuente"]);
              
              $imgSrc = !empty($row["img"]) ? htmlspecialchars($row["img"]) : 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';
              
              echo "
              <div class='character-card' data-nombre='{$nombreCompleto}'>
                <div class='character-image'>
                  <img src='{$imgSrc}' alt='{$nombreCompleto}' onerror=\"this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'\">
                  <div class='character-overlay'>
                    <div class='character-info'>
                      <p><strong>Nacimiento:</strong> {$fechaNac}</p>
                      <p><strong>Fallecimiento:</strong> {$fechaMuerte}</p>
                      <p class='character-description'><strong>Descripción:</strong><br>" . htmlspecialchars($row["descripcion"]) . "</p>
                      " . ($fuente !== "#" ? "<a href='{$fuente}' target='_blank' class='source-link'>Ver fuente →</a>" : "") . "
                    </div>
                  </div>
                </div>
                <div class='character-name'>
                  <h3>{$nombreCompleto}</h3>
                </div>
              </div>
              ";
            }
          } else {
            echo "<div class='error-message'>No se encontraron personajes en la base de datos.</div>";
          }
      }

      $conn->close();
      ?>
    </div>
  </div>
</div>

<script>
  // Toggle Sidebar
  document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
  });

  // Cerrar sidebar al hacer clic en un enlace (móvil)
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      if (window.innerWidth <= 1024) {
        document.getElementById('sidebar').classList.remove('active');
      }
    });
  });

  // Animación de carga
  window.addEventListener('load', () => {
    const personajes = document.querySelectorAll('.character-card');
    personajes.forEach((personaje, index) => {
      setTimeout(() => {
        personaje.style.opacity = '1';
        personaje.style.transform = 'translateY(0)';
      }, index * 50);
    });
    actualizarContador();
  });

  // Búsqueda
  const inputBuscador = document.getElementById('inputBuscador');
  const btnLimpiar = document.getElementById('btnLimpiar');
  const contadorResultados = document.getElementById('contadorResultados');
  const noResultados = document.getElementById('noResultados');

  inputBuscador.addEventListener('input', function() {
    const termino = this.value.toLowerCase().trim();
    btnLimpiar.style.display = termino ? 'flex' : 'none';
    filtrarPersonajes(termino);
  });

  function filtrarPersonajes(termino) {
    const personajes = document.querySelectorAll('.character-card');
    let visibles = 0;

    personajes.forEach(personaje => {
      const nombre = personaje.getAttribute('data-nombre').toLowerCase();
      
      if (nombre.includes(termino)) {
        personaje.classList.remove('oculto');
        visibles++;
      } else {
        personaje.classList.add('oculto');
      }
    });

    if (visibles === 0 && termino !== '') {
      noResultados.style.display = 'flex';
    } else {
      noResultados.style.display = 'none';
    }

    actualizarContador(visibles, personajes.length, termino);
  }

  function actualizarContador(visibles, total, termino) {
    if (termino === undefined) {
      termino = inputBuscador.value.toLowerCase().trim();
    }
    if (visibles === undefined) {
      const personajes = document.querySelectorAll('.character-card');
      total = personajes.length;
      visibles = total;
    }

    if (termino) {
      contadorResultados.textContent = `${visibles} de ${total} personajes`;
    } else {
      contadorResultados.textContent = `${total} personajes`;
    }
  }

  function limpiarBuscador() {
    inputBuscador.value = '';
    btnLimpiar.style.display = 'none';
    filtrarPersonajes('');
    inputBuscador.focus();
  }

  inputBuscador.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      limpiarBuscador();
    }
  });

  // Toggle Tema
  function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    
    if (document.body.classList.contains('dark-mode')) {
      localStorage.setItem('theme', 'dark');
      document.getElementById('btnTheme').innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
      `;
    } else {
      localStorage.setItem('theme', 'light');
      document.getElementById('btnTheme').innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      `;
    }
  }

  // Cargar tema guardado
  document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
      document.body.classList.add('dark-mode');
      document.getElementById('btnTheme').innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
      `;
    }
  });
</script>

</body>
</html>