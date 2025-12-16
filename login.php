<?php 
session_start();
include("Conexiones/conexion.php");

$usuario_guardado = isset($_COOKIE['usuario_recordado']) ? $_COOKIE['usuario_recordado'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $usuario = $_POST["usuario"];
  $contrasena = $_POST["contrasena"];

  $sql = "SELECT * FROM usuario WHERE usuario='$usuario' AND contrasena='$contrasena'";
  $resultado = $conn->query($sql);

  if ($resultado->num_rows > 0) {

      $_SESSION['usuario'] = $usuario;

      if(isset($_POST['recordar'])) {
          setcookie("usuario_recordado", $usuario, time() + (86400 * 30), "/");
      } else {
          setcookie("usuario_recordado", "", time() - 3600, "/");
      }

      header("Location: mapa.php");
      exit();

  } else {
      $error_message = true;
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibertyMap - Iniciar Sesión</title>
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

  /* Barra Superior */
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

  /* Estilo de Logo */
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

  .btn-signup {
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

  .btn-signup:hover {
    background-color: #ee5a52;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
  }

  /* Contenido Principal */
  .main-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  .content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1200px;
    width: 100%;
    gap: 60px;
    align-items: center;
  }

  /* Ilustración */
  .left-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .illustration {
    width: 100%;
    max-width: 400px;
    margin-bottom: 30px;
  }

  .illustration svg {
    width: 100%;
    height: auto;
  }

  .welcome-text h1 {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
  }

  .welcome-text p {
    font-size: 18px;
    opacity: 0.95;
    text-align: center;
    line-height: 1.6;
  }

  /* Contenedor de Formulario */
  .login-card {
    background: white;
    border-radius: 16px;
    padding: 48px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  }

  .login-header {
    margin-bottom: 32px;
  }

  .login-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
  }

  .login-header p {
    color: #718096;
    font-size: 16px;
  }

  /* Mensaje de Error */
  .error-message {
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
  }

  .error-message.fade-out {
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

  .form-input:focus {
    outline: none;
    border-color: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
  }

  .toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    font-size: 20px;
    padding: 4px;
    display: flex;
    align-items: center;
    transition: color 0.3s;
  }

  .toggle-password:hover {
    color: #ff6b6b;
  }

  .svg{
    height: 30px;
    width: auto;
    object-fit: contain;
    display: block;
  }

  /* Checkbox */
  .checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
  }

  .checkbox-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #ff6b6b;
  }

  .checkbox-label {
    color: #4a5568;
    font-size: 14px;
    cursor: pointer;
    user-select: none;
  }

  /* Botón */
  .btn-login {
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
  }

  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
  }

  .btn-login:active {
    transform: translateY(0);
  }

  /* Registrar link */
  .register-link {
    text-align: center;
    margin-top: 24px;
    color: #718096;
    font-size: 14px;
  }

  .register-link a {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
  }

  .register-link a:hover {
    color: #ee5a52;
    text-decoration: underline;
  }

  /* Responsive */
  @media (max-width: 968px) {
    .content-wrapper {
      grid-template-columns: 1fr;
      gap: 40px;
    }

    .left-side {
      display: none;
    }

    .login-card {
      padding: 32px 24px;
    }
  }

  @media (max-width: 480px) {
    .navbar {
      padding: 12px 20px;
    }

    .brand-name {
      font-size: 20px;
    }

    .nav-text {
      display: none;
    }

    .login-header h2 {
      font-size: 26px;
    }

    .login-card {
      padding: 28px 20px;
    }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo-container"><div class="logo-placeholder"><img src="Img/Logo Horizontal - LibertyMap.png"></div>
  </div>
  <div class="nav-right">
    <span class="nav-text">¿Aún no tienes cuenta?</span>
    <a href="registro.php" class="btn-signup">Registrarse</a>
  </div>
</nav>

<div class="main-container">
  <div class="content-wrapper">
    
    <div class="left-side">
      <div class="illustration">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="200" cy="150" r="120" fill="rgba(255,255,255,0.1)"/>
          <circle cx="200" cy="150" r="90" fill="rgba(255,255,255,0.15)"/>
          <path d="M150 120 L200 80 L250 120 L250 180 L200 220 L150 180 Z" fill="white" opacity="0.9"/>
          <circle cx="200" cy="150" r="30" fill="#ff6b6b"/>
          <path d="M190 150 L198 158 L215 141" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="welcome-text">
        <h1>Bienvenido a LibertyMap</h1>
        <p>Explora, descubre y navega por un mundo de posibilidades cartográficas</p>
      </div>
    </div>

    <div class="login-card">
      <div class="login-header">
        <h2>Iniciar Sesión</h2>
        <p>Ingresa tus credenciales para continuar</p>
      </div>

       <?php if (isset($error_message)): ?>
        <div class="error-message">
          <span>⚠️</span>
          <span>Usuario o contraseña incorrectos</span>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label" for="usuario">Usuario</label>
          <input 
            type="text" 
            id="usuario" 
            name="usuario" 
            class="form-input"
            value="<?php echo htmlspecialchars($usuario_guardado); ?>" 
            placeholder="Ingresa tu usuario"
            required>
        </div>

        <div class="form-group">
          <label class="form-label" for="contrasena">Contraseña</label>
          <div class="input-wrapper">
            <input 
              type="password" 
              id="contrasena" 
              name="contrasena" 
              class="form-input"
              placeholder="Ingresa tu contraseña"
              required>
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <img class="svg" src="Img/ojo-cerrado.svg" alt="Mostrar contraseña" id="eye-icon" class="eye-icon">
            </button>
          </div>
        </div>

            <div class="checkbox-group">
              <input 
                type="checkbox" 
                id="recordar" 
                name="recordar" 
                class="checkbox-input">
              <label for="recordar" class="checkbox-label">Recordar mi usuario</label>
            </div>

        <button type="submit" class="btn-login">Iniciar Sesión</button>
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

  window.addEventListener('DOMContentLoaded', function() {
    const errorMessage = document.querySelector('.error-message');
    if (errorMessage) {
      setTimeout(function() {
        errorMessage.classList.add('fade-out');
        setTimeout(function() {
          errorMessage.remove();
        }, 500);
      }, 5000);
    }
  });
</script>

</body>
</html>