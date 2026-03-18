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

      header("Location: batallas.php");
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
<link rel="icon" type="image/x-icon" href="Img/Logo - LibertyMap.png">
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
    transition: background 0.3s ease;
  }

  body.dark-mode {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
  }

  /* Barra Superior */
  .navbar {
    background-color: white;
    padding: 1rem clamp(1rem, 3vw, 2.5rem);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    top: 0;
    z-index: 1000;
    transition: all 0.3s ease;
    flex-wrap: wrap;
    gap: 1rem;
  }

  body.dark-mode .navbar {
    background-color: #2d3748;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .logo-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
  }

  .logo-container a {
    text-decoration: none;
    display: flex;
    align-items: center;
  }

  .logo-container img {
    height: 60px;
    width: auto;
    object-fit: contain;
    display: block;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: clamp(0.5rem, 2vw, 1rem);
    flex-wrap: wrap;
  }

  .nav-text {
    color: #4a5568;
    font-size: clamp(0.813rem, 1.5vw, 0.938rem);
    transition: color 0.3s ease;
    white-space: nowrap;
  }

  body.dark-mode .nav-text {
    color: #a0aec0;
  }

  /* Botón de Modo Oscuro */
  .theme-toggle {
    background: #f7fafc;
    border: none;
    border-radius: 8px;
    width: clamp(36px, 5vw, 40px);
    height: clamp(36px, 5vw, 40px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #4a5568;
    flex-shrink: 0;
  }

  .theme-toggle:hover {
    background: #e2e8f0;
    color: #ff6b6b;
  }

  body.dark-mode .theme-toggle {
    background: #4a5568;
    color: #cbd5e0;
  }

  body.dark-mode .theme-toggle:hover {
    background: #718096;
    color: #feca57;
  }

  .theme-toggle svg {
    width: clamp(16px, 2.5vw, 20px);
    height: clamp(16px, 2.5vw, 20px);
  }

  .btn-signup {
    background-color: #ff6b6b;
    color: white;
    padding: clamp(0.5rem, 1.5vw, 0.625rem) clamp(1rem, 3vw, 1.5rem);
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: clamp(0.813rem, 1.5vw, 0.938rem);
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    white-space: nowrap;
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
    padding: clamp(1rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.25rem);
    overflow-y: auto;
  }

  .content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1200px;
    width: 100%;
    gap: clamp(2rem, 5vw, 3.75rem);
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
    max-width: min(400px, 80vw);
    margin-bottom: clamp(1.5rem, 3vw, 1.875rem);
  }

  .illustration svg {
    width: 100%;
    height: auto;
  }

  .welcome-text h1 {
    font-size: clamp(1.75rem, 5vw, 2.625rem);
    font-weight: 700;
    margin-bottom: clamp(0.75rem, 2vw, 1rem);
    text-align: center;
    line-height: 1.2;
  }

  .welcome-text p {
    font-size: clamp(0.938rem, 2vw, 1.125rem);
    opacity: 0.95;
    text-align: center;
    line-height: 1.6;
  }

  /* Contenedor de Formulario */
  .login-card {
    background: white;
    border-radius: clamp(12px, 2vw, 16px);
    padding: clamp(1.5rem, 5vw, 3rem);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
  }

  body.dark-mode .login-card {
    background: #2d3748;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  }

  .login-header {
    margin-bottom: clamp(1.5rem, 4vw, 2rem);
  }

  .login-header h2 {
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 700;
    color: #1a202c;
    margin-bottom: clamp(0.375rem, 1vw, 0.5rem);
    transition: color 0.3s ease;
  }

  body.dark-mode .login-header h2 {
    color: #e2e8f0;
  }

  .login-header p {
    color: #718096;
    font-size: clamp(0.875rem, 2vw, 1rem);
    transition: color 0.3s ease;
  }

  body.dark-mode .login-header p {
    color: #a0aec0;
  }

  /* Mensaje de Error */
  .error-message {
    background-color: #fed7d7;
    color: #c53030;
    padding: clamp(0.75rem, 2vw, 0.875rem) clamp(1rem, 2.5vw, 1.125rem);
    border-radius: 8px;
    margin-bottom: clamp(1rem, 3vw, 1.5rem);
    font-size: clamp(0.813rem, 1.5vw, 0.875rem);
    display: flex;
    align-items: center;
    gap: 0.625rem;
    border-left: 4px solid #c53030;
    transition: all 0.3s ease;
  }

  body.dark-mode .error-message {
    background-color: #742a2a;
    color: #feb2b2;
    border-left-color: #fc8181;
  }

  .error-message.fade-out {
    opacity: 0;
    transform: translateY(-10px);
  }

  /* Formulario */
  .form-group {
    margin-bottom: clamp(1rem, 3vw, 1.5rem);
  }

  .form-label {
    display: block;
    color: #2d3748;
    font-size: clamp(0.813rem, 1.5vw, 0.875rem);
    font-weight: 600;
    margin-bottom: clamp(0.375rem, 1vw, 0.5rem);
    transition: color 0.3s ease;
  }

  body.dark-mode .form-label {
    color: #e2e8f0;
  }

  .input-wrapper {
    position: relative;
  }

  .form-input {
    width: 100%;
    padding: clamp(0.75rem, 2vw, 0.875rem) clamp(0.875rem, 2vw, 1rem);
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: clamp(0.875rem, 1.5vw, 0.938rem);
    transition: all 0.3s ease;
    font-family: inherit;
    background-color: white;
    color: #1a202c;
  }

  body.dark-mode .form-input {
    border-color: #4a5568;
  }

  .form-input:focus {
    outline: none;
    border-color: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
  }

  body.dark-mode .form-input:focus {
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
  }

  .toggle-password {
    position: absolute;
    right: clamp(0.75rem, 2vw, 0.875rem);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    transition: color 0.3s;
  }

  .toggle-password:hover {
    color: #ff6b6b;
  }

  .svg {
    height: clamp(24px, 4vw, 30px);
    width: auto;
    object-fit: contain;
    display: block;
  }

  /* Checkbox */
  .checkbox-group {
    display: flex;
    align-items: center;
    gap: clamp(0.375rem, 1vw, 0.5rem);
    margin-bottom: clamp(1rem, 3vw, 1.5rem);
  }

  .checkbox-input {
    width: clamp(16px, 2.5vw, 18px);
    height: clamp(16px, 2.5vw, 18px);
    cursor: pointer;
    accent-color: #ff6b6b;
    flex-shrink: 0;
  }

  .checkbox-label {
    color: #4a5568;
    font-size: clamp(0.813rem, 1.5vw, 0.875rem);
    cursor: pointer;
    user-select: none;
    transition: color 0.3s ease;
  }

  body.dark-mode .checkbox-label {
    color: #a0aec0;
  }

  /* Botón */
  .btn-login {
    width: 100%;
    padding: clamp(0.75rem, 2vw, 0.875rem);
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: clamp(0.938rem, 2vw, 1rem);
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
    margin-top: clamp(1rem, 3vw, 1.5rem);
    color: #718096;
    font-size: clamp(0.813rem, 1.5vw, 0.875rem);
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

  /* Responsive Breakpoints */
  @media (max-width: 968px) {
    .content-wrapper {
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    .left-side {
      display: none;
    }

    .login-card {
      max-width: 100%;
    }
  }

  @media (max-width: 640px) {
    .navbar {
      padding: 0.75rem 1rem;
      justify-content: center;
    }

    .logo-container {
      width: 100%;
      justify-content: center;
    }

    .nav-right {
      width: 100%;
      justify-content: center;
    }

    .nav-text {
      display: none;
    }
  }

  @media (max-width: 380px) {
    .main-container {
      padding: 1rem 0.5rem;
    }

    .login-card {
      padding: 1.25rem 1rem;
      border-radius: 12px;
    }

    .checkbox-group {
      align-items: flex-start;
    }

    .checkbox-label {
      line-height: 1.4;
    }
  }

  /* Orientación horizontal en móviles */
  @media (max-height: 600px) and (orientation: landscape) {
    .main-container {
      padding: 1rem;
    }

    .navbar {
      position: relative;
    }

    .login-card {
      padding: 1.5rem;
    }

    .login-header {
      margin-bottom: 1rem;
    }

    .form-group {
      margin-bottom: 0.75rem;
    }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo-container">
    <a href="index.php">
      <img src="Img/Logo Horizontal - LibertyMap.png" alt="LibertyMap Logo">
    </a>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar tema">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    </button>
    <span class="nav-text">¿No tienes cuenta?</span>
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
              <img class="svg" src="Img/ojo-cerrado.svg" alt="Mostrar contraseña" id="eye-icon">
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

  // Función para alternar el tema
function toggleTheme() {
  const body = document.body;
  const btnTheme = document.querySelector('.theme-toggle');
  
  body.classList.toggle('dark-mode');
  
  if (body.classList.contains('dark-mode')) {
    localStorage.setItem('theme', 'dark');
    // Ícono de SOL para modo oscuro
    btnTheme.innerHTML = `
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
    // Ícono de LUNA para modo claro
    btnTheme.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    `;
  }
}

// Cargar el tema guardado al iniciar
window.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  const btnTheme = document.querySelector('.theme-toggle');
  
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    // Ícono de SOL para modo oscuro
    btnTheme.innerHTML = `
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
    // Ícono de LUNA para modo claro (por defecto)
    btnTheme.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    `;
  }

  // Efecto fade-out para mensaje de error
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