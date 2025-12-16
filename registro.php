<?php
session_start();
include("Conexiones/conexion.php");

$errores = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $usuario = trim($_POST["usuario"]);
  $correo = trim($_POST["correo"]);
  $contrasena = $_POST["contrasena"];
  
  if (!preg_match("/^[a-zA-Z0-9_]{4,20}$/", $usuario)) {
      $errores['usuario'] = 'El usuario debe tener entre 4 y 20 caracteres (solo letras, números y guiones bajos).';
  }
  
  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
      $errores['correo'] = 'El correo electrónico no es válido.';
  }
  
  if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-])[A-Za-z\d@$!%*?&\-]{8,}$/", $contrasena)) {
      $errores['contrasena'] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo (@$!%*?&-).';
  }
  
  if (empty($errores)) {
      $stmt = $conn->prepare("SELECT * FROM usuario WHERE usuario = ? OR correo = ?");
      $stmt->bind_param("ss", $usuario, $correo);
      $stmt->execute();
      $resultado = $stmt->get_result();
      
      if ($resultado->num_rows > 0) {
          $row = $resultado->fetch_assoc();
          if ($row['usuario'] === $usuario) {
              $errores['usuario'] = 'Este nombre de usuario ya está registrado.';
          }
          if ($row['correo'] === $correo) {
              $errores['correo'] = 'Este correo ya está registrado.';
          }
          $stmt->close();
      }
      else {
          $stmt->close();
          
$foto_perfil = null;

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['foto_perfil']['name'];
    $filesize = $_FILES['foto_perfil']['size'];
    
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $errores['foto'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF";
    } elseif ($filesize > 5242880) {
        $errores['foto'] = "El archivo no debe superar los 5MB";
    } else {
        $upload_dir = 'uploads/fotos_perfil/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $nuevo_nombre = uniqid() . '_' . time() . '.' . $ext;
        $ruta_destino = $upload_dir . $nuevo_nombre;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
            $foto_perfil = $ruta_destino;
        } else {
            $errores['foto'] = "Error al subir la imagen";
        }
    }
}
          
          if (empty($errores)) {
              if ($foto_perfil) {
                  $stmt = $conn->prepare("INSERT INTO usuario (usuario, correo, contrasena, foto_perfil) VALUES (?, ?, ?, ?)");
                  $stmt->bind_param("ssss", $usuario, $correo, $contrasena, $foto_perfil);
              } else {
                  $stmt = $conn->prepare("INSERT INTO usuario (usuario, correo, contrasena) VALUES (?, ?, ?)");
                  $stmt->bind_param("sss", $usuario, $correo, $contrasena);
              }
              
              if ($stmt->execute()) {
                  $_SESSION['usuario'] = $usuario;
                  $_SESSION['usuario_id'] = $stmt->insert_id;
                  $_SESSION['foto_perfil'] = $foto_perfil;
                  
                  $stmt->close();
                  $conn->close();
                  
                  header("Location: mapa.php");
                  exit();
              } else {
                  $errores['general'] = 'Error al registrar el usuario. Intente nuevamente.';
                  $stmt->close();
              }
          }
      }
      $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibertyMap - Registro</title>
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* Barra superior */
  .navbar {
    background-color: white;
    padding: 16px 40px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .logo-container {
    padding-left: 70px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .logo-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 20px;
  }

  .logo-container img {
    height: 60px;
    width: auto;
    object-fit: contain;
    display: block;
  }

  .brand-name {
    font-size: 24px;
    font-weight: 600;
    color: #1a202c;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .nav-text {
    color: #4a5568;
    font-size: 15px;
  }

  .btn-login {
    background-color: #ff6b6b;
    color: white;
    padding: 10px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }

  .btn-login:hover {
    background-color: #ee5a52;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
  }

  /* Contenedor principal */
  .main-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  .content-wrapper {
    max-width: 600px;
    width: 100%;
  }

  /* Tarjeta de registro */
  .register-card {
    background: white;
    border-radius: 16px;
    padding: 48px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  }

  .register-header {
    margin-bottom: 32px;
    text-align: center;
  }

  .register-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
  }

  .register-header p {
    color: #718096;
    font-size: 16px;
  }

  /* Mensaje de error general */
  .error-general {
    background-color: #fed7d7;
    color: #c53030;
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid #c53030;
    transition: opacity 0.5s ease, transform 0.5s ease;
  }

  .error-general.fade-out {
    opacity: 0;
    transform: translateY(-10px);
  }

  /* Formulario */
  .form-group {
    margin-bottom: 24px;
  }

  .form-label {
    display: block;
    color: #2d3748;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .input-wrapper {
    position: relative;
  }

  .form-input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s ease;
    font-family: inherit;
  }

  .form-input.error-input {
    border-color: #fc8181;
    background-color: #fff5f5;
  }

  .form-input:focus {
    outline: none;
    border-color: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
  }

  .error-message {
    color: #c53030;
    font-size: 13px;
    margin-top: 6px;
    display: block;
    font-weight: 500;
  }

  /* Toggle password */
  .toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    transition: opacity 0.3s;
  }

  .toggle-password:hover {
    opacity: 0.7;
  }

  .eye-icon {
    width: 22px;
    height: 22px;
    display: block;
  }

  /* Sección de foto de perfil */
  .photo-section {
    margin: 32px 0;
    padding: 24px;
    background-color: #f7fafc;
    border-radius: 12px;
    border: 2px dashed #cbd5e0;
    text-align: center;
  }

  .photo-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 16px;
  }

   .svg{
    height: 30px;
    width: auto;
    object-fit: contain;
    display: block;
  }

  .file-input-wrapper {
    position: relative;
    display: inline-block;
  }

  .file-input-wrapper input[type="file"] {
    position: absolute;
    left: -9999px;
  }

  .file-input-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
  }

  .file-input-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 107, 107, 0.4);
  }

  .preview-container {
    margin-top: 20px;
    display: none;
  }

  #preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #ff6b6b;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    object-fit: cover;
  }

  .file-name {
    margin-top: 12px;
    font-size: 14px;
    color: #4a5568;
    font-weight: 500;
  }

  /* Botón de registro */
  .btn-register {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    margin-top: 8px;
  }

  .btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
  }

  .btn-register:active {
    transform: translateY(0);
  }

  /* Link de login */
  .login-link {
    text-align: center;
    margin-top: 24px;
    color: #718096;
    font-size: 14px;
  }

  .login-link a {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
  }

  .login-link a:hover {
    color: #ee5a52;
    text-decoration: underline;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .register-card {
      padding: 32px 24px;
    }

    .register-header h2 {
      font-size: 26px;
    }

    .navbar {
      padding: 12px 20px;
    }

    .logo-container {
      padding-left: 0;
    }

    .brand-name {
      font-size: 20px;
    }

    .nav-text {
      display: none;
    }
  }

  @media (max-width: 480px) {
    .register-card {
      padding: 28px 20px;
    }

    .photo-section {
      padding: 20px 16px;
    }

    #preview {
      width: 100px;
      height: 100px;
    }
  }
</style>
</head>
<body>

<!-- Barra de Navegación -->
<nav class="navbar">
  <div class="logo-container">
    <div class="logo-placeholder">
      <img src="Img/Logo Horizontal - LibertyMap.png" alt="LibertyMap">
    </div>
  </div>
  <div class="nav-right">
    <span class="nav-text">¿Ya tienes cuenta?</span>
    <a href="login.php" class="btn-login">Iniciar Sesión</a>
  </div>
</nav>

<!-- Contenedor Principal -->
<div class="main-container">
  <div class="content-wrapper">
    
    <!-- Tarjeta de Registro -->
    <div class="register-card">
      <div class="register-header">
        <h2>Crear Cuenta</h2>
        <p>Completa el formulario para registrarte</p>
      </div>

      <?php if (isset($errores['general'])): ?>
        <div class="error-general">
          <span>⚠️</span>
          <span><?php echo htmlspecialchars($errores['general']); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data">
        <!-- Usuario -->
        <div class="form-group">
          <label class="form-label" for="usuario">Usuario</label>
          <input 
            type="text" 
            id="usuario" 
            name="usuario" 
            class="form-input <?php echo isset($errores['usuario']) ? 'error-input' : ''; ?>"
            value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
            placeholder="Elige un nombre de usuario"
            required>
          <?php if (isset($errores['usuario'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errores['usuario']); ?></span>
          <?php endif; ?>
        </div>

        <!-- Correo -->
        <div class="form-group">
          <label class="form-label" for="correo">Correo Electrónico</label>
          <input 
            type="email" 
            id="correo" 
            name="correo" 
            class="form-input <?php echo isset($errores['correo']) ? 'error-input' : ''; ?>"
            value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
            placeholder="tu@email.com"
            required>
          <?php if (isset($errores['correo'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errores['correo']); ?></span>
          <?php endif; ?>
        </div>

        <!-- Contraseña -->
        <div class="form-group">
          <label class="form-label" for="contrasena">Contraseña</label>
          <div class="input-wrapper">
            <input 
              type="password" 
              id="contrasena" 
              name="contrasena" 
              class="form-input <?php echo isset($errores['contrasena']) ? 'error-input' : ''; ?>"
              placeholder="Crea una contraseña segura"
              required>
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <img class="svg" src="Img/ojo-cerrado.svg" alt="Mostrar contraseña" id="eye-icon" class="eye-icon">
            </button>
          </div>
          <?php if (isset($errores['contrasena'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errores['contrasena']); ?></span>
          <?php endif; ?>
        </div>

        <div class="photo-section">
          <center><div class="photo-section-title"><span><img class="svg" src="Img/usuario.svg"> Foto de Perfil </span></div></center>
          <div class="file-input-wrapper">
            <input 
              type="file" 
              name="foto_perfil" 
              id="foto_perfil" 
              accept="image/*" 
              onchange="previewImage(event)">
            <label for="foto_perfil" class="file-input-label">
              <span><img class="svg" src="Img/carpeta.svg"> </span>
              <span>Seleccionar Imagen</span>
            </label>
          </div>
          <?php if (isset($errores['foto'])): ?>
            <div class="error-message" style="text-align: center; margin-top: 10px;">
              <?php echo htmlspecialchars($errores['foto']); ?>
            </div>
          <?php endif; ?>
          <div class="preview-container" id="previewContainer">
            <img id="preview" src="" alt="Vista previa">
            <div class="file-name" id="fileName"></div>
          </div>
        </div>

        <button type="submit" class="btn-register">Crear Cuenta</button>
      </form>
    </div>

  </div>
</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById('contrasena');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.src = 'Img/ojo-abierto.svg';
      eyeIcon.alt = 'Ocultar contraseña';
    } else {
      passwordInput.type = 'password';
      eyeIcon.src = 'Img/ojo-cerrado.svg';
      eyeIcon.alt = 'Mostrar contraseña';
    }
  }

  function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
      if (file.size > 5242880) {
        alert('El archivo no debe superar los 5MB');
        event.target.value = '';
        return;
      }
      
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        alert('Solo se permiten archivos JPG, JPEG, PNG y GIF');
        event.target.value = '';
        return;
      }
      
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('preview').src = e.target.result;
        document.getElementById('previewContainer').style.display = 'block';
        document.getElementById('fileName').textContent = file.name;
      }
      reader.readAsDataURL(file);
    }
  }

  window.addEventListener('DOMContentLoaded', function() {
    const errorGeneral = document.querySelector('.error-general');
    if (errorGeneral) {
      setTimeout(function() {
        errorGeneral.classList.add('fade-out');
        setTimeout(function() {
          errorGeneral.remove();
        }, 500);
      }, 5000);
    }
  });
</script>

</body>
</html>