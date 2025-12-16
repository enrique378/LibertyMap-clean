<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
} else {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['cambiar_foto_url'])) {
        $nueva_url = trim($_POST['url_foto']);
        
        if (!empty($nueva_url)) {
            if (filter_var($nueva_url, FILTER_VALIDATE_URL)) {
                $nueva_url_escapada = $conn->real_escape_string($nueva_url);
                $sql_update = "UPDATE usuario SET foto_perfil = '$nueva_url_escapada' WHERE usuario = '" . $conn->real_escape_string($usuario_actual) . "'";
                
                if ($conn->query($sql_update)) {
                    $mensaje = '✅ Foto de perfil actualizada exitosamente';
                    $tipo_mensaje = 'success';
                    $foto_perfil = $nueva_url;
                } else {
                    $mensaje = '❌ Error al actualizar la foto de perfil';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = '⚠️ La URL proporcionada no es válida';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = '⚠️ Por favor ingresa una URL válida';
            $tipo_mensaje = 'error';
        }
    }
    
    if (isset($_POST['subir_foto']) && isset($_FILES['archivo_foto'])) {
        $archivo = $_FILES['archivo_foto'];
        
        if ($archivo['error'] === UPLOAD_ERR_OK) {
            $permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $tipo_archivo = $archivo['type'];
            
            if (in_array($tipo_archivo, $permitidos)) {
                if ($archivo['size'] <= 5 * 1024 * 1024) {
                    $carpeta_uploads = 'uploads/perfiles/';
                    if (!file_exists($carpeta_uploads)) {
                        mkdir($carpeta_uploads, 0777, true);
                    }
                    
                    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = $usuario_actual . '_' . time() . '.' . $extension;
                    $ruta_destino = $carpeta_uploads . $nombre_archivo;
                    
                    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                        if (!empty($datos_usuario['foto_perfil']) && 
                            strpos($datos_usuario['foto_perfil'], 'uploads/perfiles/') !== false &&
                            file_exists($datos_usuario['foto_perfil'])) {
                            unlink($datos_usuario['foto_perfil']);
                        }
                        
                        $ruta_escapada = $conn->real_escape_string($ruta_destino);
                        $sql_update = "UPDATE usuario SET foto_perfil = '$ruta_escapada' WHERE usuario = '" . $conn->real_escape_string($usuario_actual) . "'";
                        
                        if ($conn->query($sql_update)) {
                            $mensaje = '✅ Foto de perfil subida exitosamente';
                            $tipo_mensaje = 'success';
                            $foto_perfil = $ruta_destino;
                        } else {
                            $mensaje = '❌ Error al guardar la foto en la base de datos';
                            $tipo_mensaje = 'error';
                        }
                    } else {
                        $mensaje = '❌ Error al subir el archivo';
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = '⚠️ El archivo es demasiado grande (máximo 5MB)';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = '⚠️ Tipo de archivo no permitido. Solo JPG, PNG, GIF o WEBP';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = '❌ Error al subir el archivo';
            $tipo_mensaje = 'error';
        }
    }
    
    if (isset($_POST['borrar_foto'])) {
        if (!empty($datos_usuario['foto_perfil']) && 
            strpos($datos_usuario['foto_perfil'], 'uploads/perfiles/') !== false &&
            file_exists($datos_usuario['foto_perfil'])) {
            unlink($datos_usuario['foto_perfil']);
        }
        
        $sql_update = "UPDATE usuario SET foto_perfil = NULL WHERE usuario = '" . $conn->real_escape_string($usuario_actual) . "'";
        
        if ($conn->query($sql_update)) {
            $mensaje = '✅ Foto de perfil eliminada. Se usará la imagen por defecto';
            $tipo_mensaje = 'success';
            $foto_perfil = 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';
        } else {
            $mensaje = '❌ Error al eliminar la foto de perfil';
            $tipo_mensaje = 'error';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibertyMap - Mi Perfil</title>
    <link rel="stylesheet" href="Styles/perfil-style.css">
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
    <a href="comunidad.php" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <span>Comunidad</span>
    </a>
    <a href="perfil.php" class="nav-item active">
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
    <h1 class="page-title">Mi Perfil</h1>
    <div class="top-bar-actions">
      <button class="btn-theme" id="btnTheme" onclick="toggleTheme()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
    </div>
  </div>

  <!-- Contenido de Perfil -->
  <div class="profile-container">
    
    <!-- Encabezado de Perfil -->
    <div class="profile-header-card">
      <div class="profile-avatar-section">
        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" 
             alt="Foto de perfil" 
             class="profile-avatar-large"
             onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
        <div class="avatar-badge">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
      </div>
      <div class="profile-header-info">
        <h2><?php echo htmlspecialchars($usuario_actual); ?></h2>
        <p><?php echo htmlspecialchars($correo_usuario); ?></p>
      </div>
    </div>

    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-<?php echo $tipo_mensaje; ?>" id="alertMessage">
        <?php echo $mensaje; ?>
        <button type="button" onclick="cerrarAlerta()" class="alert-close">×</button>
      </div>
    <?php endif; ?>

    <!-- Opciones de Foto -->
    <div class="profile-section">
      <div class="section-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
          <circle cx="12" cy="13" r="4"></circle>
        </svg>
      </div>
      <h3>Cambiar foto con URL</h3>
      <form method="POST" action="">
        <div class="form-group">
          <label for="url_foto">URL de la imagen</label>
          <input type="text" id="url_foto" name="url_foto" placeholder="https://ejemplo.com/mi-foto.jpg" required>
          <span class="form-help">Ingresa la URL directa de una imagen</span>
        </div>
        <button type="submit" name="cambiar_foto_url" class="btn-primary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          Actualizar foto
        </button>
      </form>
    </div>

    <div class="divider">
      <span>O</span>
    </div>

    <div class="profile-section">
      <div class="section-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
      </div>
      <h3>Subir foto desde tu dispositivo</h3>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
          <label for="archivo_foto">Seleccionar archivo</label>
          <div class="file-input-wrapper">
            <input type="file" id="archivo_foto" name="archivo_foto" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
            <label for="archivo_foto" class="file-input-label">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              <span id="fileName">Elegir archivo</span>
            </label>
          </div>
          <span class="form-help">JPG, PNG, GIF o WEBP (máx. 5MB)</span>
        </div>
        <button type="submit" name="subir_foto" class="btn-primary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          Subir foto
        </button>
      </form>
    </div>

    <div class="divider">
      <span>O</span>
    </div>

    <div class="profile-section danger-zone">
      <div class="section-icon danger">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
      </div>
      <h3>Eliminar foto de perfil</h3>
      <p class="warning-text">Si eliminas tu foto, se usará la imagen por defecto del sistema.</p>
      <form method="POST" action="" onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?');">
        <button type="submit" name="borrar_foto" class="btn-danger">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Eliminar foto actual
        </button>
      </form>
    </div>

  </div>
</div>

<script>
  // Toggle Sidebar
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

  // File input name display
  document.getElementById('archivo_foto').addEventListener('change', function() {
    const fileName = this.files[0]?.name || 'Elegir archivo';
    document.getElementById('fileName').textContent = fileName;
  });

  // Cerrar alerta
  function cerrarAlerta() {
    const alerta = document.getElementById('alertMessage');
    if (alerta) {
      alerta.style.opacity = '0';
      setTimeout(() => alerta.remove(), 300);
    }
  }

  // Auto-cerrar alerta
  if (document.getElementById('alertMessage')) {
    setTimeout(cerrarAlerta, 5000);
  }

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