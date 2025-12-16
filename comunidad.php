<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'Conexiones/conexion.php';
require_once 'Conexiones/ia.php';

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

$moderador = new ModeradorIA('openai');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comentario'])) {
    $nombre = $_SESSION['usuario'];
    $comentario = trim($_POST['comentario']);
    
    if (!empty($comentario)) {
        $comentario_aprobado = false;
        $info_rechazo = null;
        
        try {
            $analisis = $moderador->analizarComentario($comentario);
            
            if ($analisis['apropiado']) {
                $comentario_aprobado = true;
            } else {
                $comentario_aprobado = false;
                $info_rechazo = [
                    'razon' => $analisis['razon'],
                    'comentario' => $comentario,
                    'palabra' => $analisis['palabra_detectada'] ?? ''
                ];
            }
            
        } catch (Exception $e) {
            try {
                $moderador_fallback = new ModeradorIA('patrones');
                $analisis = $moderador_fallback->analizarComentario($comentario);
                
                if (!$analisis['apropiado']) {
                    $comentario_aprobado = false;
                    $info_rechazo = [
                        'razon' => $analisis['razon'],
                        'comentario' => $comentario,
                        'palabra' => $analisis['palabra_detectada'] ?? ''
                    ];
                } else {
                    $comentario_aprobado = true;
                }
                
            } catch (Exception $e2) {
                $comentario_aprobado = false;
                $info_rechazo = [
                    'razon' => 'Error en sistema de moderación - Intenta nuevamente',
                    'comentario' => $comentario,
                    'palabra' => ''
                ];
            }
        }
                
        if (!$comentario_aprobado && $info_rechazo !== null) {
            $params = http_build_query([
                'error' => 'ia',
                'razon' => $info_rechazo['razon'],
                'comentario' => $info_rechazo['comentario'],
                'palabra' => $info_rechazo['palabra']
            ]);
            
            header("Location: comunidad.php?{$params}");
            exit();
        }
        
        $nombre_escaped = $conn->real_escape_string($nombre);
        $comentario_escaped = $conn->real_escape_string($comentario);
        
        $sql = "INSERT INTO comentarios (usuario, comentario) VALUES ('$nombre_escaped', '$comentario_escaped')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: comunidad.php?exito=1");
            exit();
        } else {
            header("Location: comunidad.php?error=1");
            exit();
        }
        
    } else {
        header("Location: comunidad.php?error=2");
        exit();
    }
}

$mensaje = '';
$comentario_rechazado = '';

if (isset($_GET['exito']) && $_GET['exito'] == 1) {
    $mensaje = '<div class="alert alert-success">✅ ¡Comentario publicado exitosamente!</div>';
    
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        $mensaje = '<div class="alert alert-error">❌ Error al publicar el comentario.</div>';
    } elseif ($_GET['error'] == 2) {
        $mensaje = '<div class="alert alert-error">⚠️ Por favor escribe un comentario.</div>';
    } elseif ($_GET['error'] == 'ia') {
        $razon = isset($_GET['razon']) ? htmlspecialchars(urldecode($_GET['razon'])) : 'contenido inapropiado';
        $comentario_rechazado = isset($_GET['comentario']) ? htmlspecialchars(urldecode($_GET['comentario'])) : '';
        $palabra_detectada = isset($_GET['palabra']) ? htmlspecialchars(urldecode($_GET['palabra'])) : '';
        
        $mensaje = '<div class="alert alert-error">
            <strong>🛡️ Comentario bloqueado por moderación</strong><br>
            <div class="alert-detail">
                <strong>Razón:</strong> ' . $razon;
                        
        if (!empty($palabra_detectada)) {
            $mensaje .= '<br><small class="word-detected">
                Palabra detectada: "<span>' . $palabra_detectada . '</span>"
            </small>';
        }
        
        $mensaje .= '</div>
            <small class="alert-info">
                ℹ️ El sistema analiza el <strong>contexto completo</strong> de tu mensaje.
                Por favor, revisa tu comentario y asegúrate de que sea respetuoso.
            </small>
        </div>';
    }
}

$sql = "SELECT c.*, u.foto_perfil 
        FROM comentarios c 
        LEFT JOIN usuario u ON c.usuario = u.usuario 
        ORDER BY c.fecha_creacion DESC";
$resultado = $conn->query($sql);
$comentarios = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $comentarios[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibertyMap - Comunidad</title>
    <link rel="stylesheet" href="Styles/comunidad-style.css">
</head>
<body>

<!-- Menú -->
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
    <a href="personajes.php" class="nav-item">
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
    <a href="comunidad.php" class="nav-item active">
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
  <!-- Barra SUperior -->
  <div class="top-bar">
    <button class="btn-toggle-sidebar" id="toggleSidebar">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    <h1 class="page-title">Comunidad</h1>
    <div class="top-bar-actions">
      <button class="btn-theme" id="btnTheme" onclick="toggleTheme()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
      <div class="comments-counter">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span><?php echo count($comentarios); ?></span>
      </div>
    </div>
  </div>

  <!-- Contenido de Comunidad -->
  <div class="community-container">
    
    <!-- Form para Nuevo Comentario -->
    <div class="new-comment-card">
      <div class="card-header">
        <h2> Nuevo Comentario</h2>
      </div>
      
      <div class="current-user">
        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" 
             alt="Tu foto" 
             class="current-user-avatar"
             onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
        <div class="current-user-info">
          <span class="current-user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
          <span class="current-user-badge">Comentando como</span>
        </div>
      </div>
      
      <?php if (!empty($mensaje)): ?>
        <div id="mensajeAlerta" class="alert-container">
          <?php echo $mensaje; ?>
          <button type="button" onclick="cerrarMensaje()" class="alert-close">×</button>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <div class="form-group">
          <textarea 
            id="comentario" 
            name="comentario" 
            required 
            placeholder="Comparte tu opinión, pregunta o comentario sobre LibertyMap..."
            rows="4"
          ><?php echo $comentario_rechazado; ?></textarea>
        </div>
        
        <button type="submit" name="enviar_comentario" class="btn-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
          Publicar Comentario
        </button>
      </form>
    </div>
    
    <!-- Lista de Comentarios -->
    <div class="comments-section">
      <div class="section-header">
        <h2> Conversaciones</h2>
        <span class="comment-count"><?php echo count($comentarios); ?> comentarios</span>
      </div>
      
      <?php if (empty($comentarios)): ?>
        <div class="no-comments">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          <p>¡Sé el primero en comentar!</p>
          <span>Inicia la conversación y comparte tus ideas</span>
        </div>
      <?php else: ?>
        <div class="comments-list">
          <?php foreach ($comentarios as $com): ?>
            <div class="comment-card">
              <div class="comment-header">
                <img src="<?php echo !empty($com['foto_perfil']) ? htmlspecialchars($com['foto_perfil']) : 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($com['usuario']); ?>" 
                     class="comment-avatar"
                     onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
                <div class="comment-user-info">
                  <span class="comment-username"><?php echo htmlspecialchars($com['usuario']); ?></span>
                  <span class="comment-date"><?php 
                    $fecha = new DateTime($com['fecha_creacion']);
                    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                    echo $fecha->format('j') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y') . ' a las ' . $fecha->format('H:i');
                  ?></span>
                </div>
              </div>
              <div class="comment-body">
                <?php echo nl2br(htmlspecialchars($com['comentario'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // Cambio de Barra Lateral
  document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
  });

  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      if (window.innerWidth <= 1024) {
        document.getElementById('sidebar').classList.remove('active');
      }
    });
  });

  // Cambio de Tema
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

  // Cargar  Guardado
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
    
    if (document.getElementById('mensajeAlerta')) {
      setTimeout(cerrarMensaje, 10000);
    }
    
    resaltarPalabraOfensiva();
  });

  function cerrarMensaje() {
    const alerta = document.getElementById('mensajeAlerta');
    if (alerta) {
      alerta.style.opacity = '0';
      alerta.style.transition = 'opacity 0.3s';
      setTimeout(function() {
        alerta.remove();
        const url = new URL(window.location);
        url.search = '';
        window.history.replaceState({}, document.title, url);
      }, 300);
    }
  }

  function resaltarPalabraOfensiva() {
    const urlParams = new URLSearchParams(window.location.search);
    const palabraDetectada = urlParams.get('palabra');
    const textarea = document.getElementById('comentario');
    
    if (palabraDetectada && textarea && textarea.value) {
      textarea.classList.add('textarea-error');
      textarea.focus();
      
      const textoLower = textarea.value.toLowerCase();
      const palabraLower = palabraDetectada.toLowerCase();
      const indice = textoLower.indexOf(palabraLower);
      
      if (indice !== -1) {
        textarea.setSelectionRange(indice, indice + palabraDetectada.length);
        textarea.scrollTop = 0;
      }
      
      setTimeout(function() {
        textarea.classList.remove('textarea-error');
      }, 3000);
    }
  }
</script>

</body>
</html>

<?php
$conn->close();
?>