<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibertyMap - Explora la Historia de México</title>
<link rel="icon" type="image/x-icon" href="Img/Logo - LibertyMap.png">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: white;
    color: #1a202c;
    overflow-x: hidden;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  body.dark-mode {
    background: #1a202c;
    color: #e2e8f0;
  }

  /* Barra Superior */
  .navbar {
    background-color: white;
    padding: 16px 40px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  body.dark-mode .navbar {
    background-color: #2d3748;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .logo-container {
    padding-left: 70px;
    display: flex;
    align-items: center;
    gap: 12px;
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
    transition: color 0.3s ease;
  }

  body.dark-mode .brand-name {
    color: #e2e8f0;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  /* Botón de Modo Oscuro */
  .theme-toggle {
    background: #f7fafc;
    border: none;
    border-radius: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #4a5568;
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

  .theme-icon {
    width: 20px;
    height: 20px;
    transition: transform 0.3s ease;
  }

  .btn-nav {
    padding: 10px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }

  .btn-login {
    background-color: transparent;
    color: #4a5568;
    border: 2px solid #e2e8f0;
  }

  body.dark-mode .btn-login {
    background-color: transparent;
    color: #ffffffff;
  }

  .btn-login:hover,
  body.dark-mode .btn-login:hover {
    border-color: #ff6b6b;
    color: #ff6b6b;
  }

  .btn-signup {
    background-color: #ff6b6b;
    color: white;
  }

  .btn-signup:hover {
    background-color: #ee5a52;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
  }

  /* Hero Section */
  .hero {
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    padding: 100px 40px;
    text-align: center;
    color: white;
  }

  .hero h1 {
    font-size: 56px;
    font-weight: 700;
    margin-bottom: 24px;
    line-height: 1.2;
  }

  .hero p {
    font-size: 22px;
    margin-bottom: 40px;
    opacity: 0.95;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
  }

  .hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-hero {
    padding: 16px 40px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 18px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-block;
  }

  .btn-primary {
    background-color: white;
    color: #ff6b6b;
  }

  .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  }

  .btn-secondary {
    background-color: transparent;
    color: white;
    border: 3px solid white;
  }

  .btn-secondary:hover {
    background-color: white;
    color: #ff6b6b;
  }

  /* Secciones de Contenido */
  .content-section {
    padding: 80px 40px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .section-title {
    font-size: 42px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    text-align: center;
  }

  body.dark-mode .section-title {
    color: #e2e8f0;
  }

  .section-subtitle {
  font-size: 20px;
  color: #4a5568;
  margin-bottom: 50px;
  text-align: center;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
  transition: color 0.3s ease;
}

body.dark-mode .section-subtitle {
  color: #cbd5e0;
}

  /* Misión Section */
  .mission-content {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    padding: 50px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }

  body.dark-mode .mission-content {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  }

  .mission-content p {
    font-size: 18px;
    line-height: 1.8;
    color: #2d3748;
    margin-bottom: 20px;
    transition: color 0.3s ease;
  }

  body.dark-mode .mission-content p {
    color: #cbd5e0;
  }

  /* Features Grid */
  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 50px;
  }

  .feature-card {
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
  }

  body.dark-mode .feature-card {
    background: #2d3748;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  }

  .feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(255, 107, 107, 0.2);
    border-color: #ff6b6b;
  }

  body.dark-mode .feature-card:hover {
    box-shadow: 0 12px 40px rgba(255, 107, 107, 0.4);
  }

  .feature-icon {
    font-size: 48px;
    margin-bottom: 20px;
    display: block;
  }

  .feature-title {
    font-size: 24px;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 15px;
    transition: color 0.3s ease;
  }

  body.dark-mode .feature-title {
    color: #e2e8f0;
  }

  .feature-description {
    font-size: 16px;
    color: #4a5568;
    line-height: 1.6;
    transition: color 0.3s ease;
  }

  body.dark-mode .feature-description {
    color: #a0aec0;
  }

  .feature-list {
    list-style: none;
    padding-left: 0;
    margin-top: 15px;
  }

  .feature-list li {
    padding: 8px 0;
    color: #4a5568;
    font-size: 16px;
    line-height: 1.6;
    transition: color 0.3s ease;
  }

  body.dark-mode .feature-list li {
    color: #a0aec0;
  }

  .feature-list li:before {
    content: "✓ ";
    color: #ff6b6b;
    font-weight: bold;
    margin-right: 8px;
  }

  /* Highlight Section */
  .highlight-section {
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    color: white;
    padding: 80px 40px;
    text-align: center;
  }

  .highlight-section .section-title {
    color: white;
  }

  .highlight-section .section-subtitle {
    color: rgba(255, 255, 255, 0.95);
  }

  .highlight-content {
    max-width: 900px;
    margin: 0 auto;
    font-size: 18px;
    line-height: 1.8;
  }

  /* Technology Section */
  .tech-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
  }

  .tech-badge {
    background: white;
    color: #ff6b6b;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }

  body.dark-mode .tech-badge {
    background: #1a202c;
    color: #feca57;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  }

  /* CTA Section */
  .cta-section {
    background: #1a202c;
    color: white;
    padding: 80px 40px;
    text-align: center;
    transition: background-color 0.3s ease;
  }

  body.dark-mode .cta-section {
    background: #0f1419;
  }

  .cta-section .section-title {
    color: white;
    margin-bottom: 30px;
  }

  .cta-section p {
    font-size: 20px;
    margin-bottom: 40px;
    opacity: 0.9;
  }

  /* Footer */
  .footer {
    background: #0f1419;
    color: #a0aec0;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
  }

  body.dark-mode .footer {
    background: #000000;
  }

  .footer p {
    margin-bottom: 10px;
  }

  /* Responsive */
  @media (max-width: 968px) {
    .hero h1 {
      font-size: 42px;
    }

    .hero p {
      font-size: 18px;
    }

    .section-title {
      font-size: 32px;
    }

    .content-section {
      padding: 60px 20px;
    }

    .mission-content {
      padding: 30px;
    }

    .features-grid {
      grid-template-columns: 1fr;
    }

    .logo-container {
      padding-left: 0;
    }
  }

  @media (max-width: 480px) {
    .navbar {
      padding: 12px 20px;
      flex-direction: column;
      gap: 15px;
    }

    .hero {
      padding: 60px 20px;
    }

    .hero h1 {
      font-size: 32px;
    }

    .hero p {
      font-size: 16px;
    }

    .btn-hero {
      padding: 14px 30px;
      font-size: 16px;
    }

    .feature-card {
      padding: 30px 20px;
    }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo-container">
    <div class="logo-placeholder">
      <img src="Img/Logo Horizontal - LibertyMap.png" alt="LibertyMap Logo">
    </div>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar tema">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    </button>
    <a href="login.php" class="btn-nav btn-login">Iniciar Sesión</a>
    <a href="registro.php" class="btn-nav btn-signup">Registrarse</a>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero" id="hero">
  <h1>Liberty Map</h1>
  <p>Explora la historia de México a través de un mapa interactivo que muestra las 50 batallas más importantes del territorio nacional</p>
  <div class="hero-buttons">
    <a href="registro.php" class="btn-hero btn-primary">Comenzar Ahora</a>
    <a href="#mision" class="btn-hero btn-secondary">Conocer Más</a>
  </div>
</section>

<!-- Misión Section -->
<section class="content-section" id="mision">
  <h2 class="section-title">Nuestra Misión: Acercar el Pasado al Presente</h2>
  <div class="mission-content">
    <p>
      El objetivo principal de este proyecto es ambicioso: crear una herramienta digital poderosa que permita a cualquier usuario conocer y entender los conflictos armados más importantes que han marcado la historia de México. Buscamos transformar la forma en que se aprende historia, utilizando la tecnología para hacerla dinámica, visualmente atractiva y verdaderamente significativa.
    </p>
    <p>
      Actualmente, el problema no es la falta de datos, sino cómo se presenta la información histórica, que a menudo se encuentra dispersa, poco visual y desmotivante. Liberty Map ofrece una solución real y práctica al problema del desconocimiento histórico, convirtiendo la geografía en una herramienta didáctica.
    </p>
  </div>
</section>

<!-- Features Section -->
<section class="content-section">
  <h2 class="section-title">Explore la Historia en el Mapa Interactivo</h2>
  <p class="section-subtitle">
    Al acceder a la plataforma, podrá explorar un mapa histórico interactivo que muestra la localización exacta de las 50 batallas más importantes ocurridas en el territorio mexicano.
  </p>
  
  <div class="features-grid">
    <div class="feature-card">
      <div style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 33px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); border: 3px solid white; cursor: pointer;">⚔️</div>
      <h3 class="feature-title">Cápsulas de Historia</h3>
      <p class="feature-description">
        Cada marcador en el mapa es una "cápsula de historia" que proporciona información detallada del enfrentamiento, incluyendo su ubicación, fecha, contexto, personajes destacados y el impacto real que tuvo en la historia nacional.
      </p>
    </div>

    <div class="feature-card">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <h3 class="feature-title">Protagonistas y Consecuencias</h3>
      <p class="feature-description">
        Podrá identificar a los protagonistas de los conflictos, entendiendo las complejas causas y viendo las consecuencias directas que esos eventos tuvieron.
      </p>
    </div>

    <div class="feature-card">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
      </svg>
      <h3 class="feature-title">Aprendizaje Contextualizado</h3>
      <p class="feature-description">
        Este formato permite que usted deje de solo leer fechas para viajar mentalmente a los lugares exactos de los conflictos, conectando los hechos históricos con los lugares geográficos donde sucedieron.
      </p>
    </div>
  </div>
</section>

<!-- Contextualización Section -->
<section class="highlight-section">
  <h2 class="section-title">Contextualización Territorial y Evolución del País</h2>
  <p class="section-subtitle">
    Para una comprensión más profunda, nuestra aplicación incluye un recurso de valor inigualable
  </p>
  
  <div class="highlight-content">
    <ul class="feature-list" style="text-align: left; max-width: 700px; margin: 0 auto;">
      <li style="color: white;"><strong>Mapa Comparativo:</strong> Incorporamos un apartado extra que compara el territorio mexicano entre 1821 y 1848.</li>
      <li style="color: white;"><strong>Comprensión Geopolítica:</strong> Este contraste es esencial, ya que permite contextualizar cada conflicto dentro de la evolución política y territorial del país, ayudando al usuario a entender mejor la pérdida territorial, las tensiones internacionales y la formación del México moderno.</li>
    </ul>
  </div>
</section>

<!-- Valor Educativo Section -->
<section class="content-section">
  <h2 class="section-title">Valor Educativo y Tecnológico</h2>
  <p class="section-subtitle">
    Desarrollado con tecnologías de código abierto para garantizar una interfaz clara, manejable y adaptable a cualquier dispositivo
  </p>

  <div class="mission-content">
    <p>
      Liberty Map fue desarrollado con tecnologías de código abierto (HTML, CSS, JavaScript, PHP y MySQL) para garantizar una interfaz clara, manejable y adaptable a cualquier dispositivo. Al presentar la información de esta manera visual, organizada y accesible, se busca que usted se involucre más fácilmente con los temas históricos, logrando una comprensión más profunda de la complejidad de los hechos.
    </p>
    <p>
      Nuestro propósito es fomentar el aprendizaje histórico de una manera dinámica, visual y accesible, incentivando el interés genuino por comprender los hechos que, a punta de lucha, le dieron forma al México que somos hoy.
    </p>

    <div class="tech-badges">
      <span class="tech-badge">HTML</span>
      <span class="tech-badge">CSS</span>
      <span class="tech-badge">JavaScript</span>
      <span class="tech-badge">PHP</span>
      <span class="tech-badge">MySQL</span>
    </div>
  </div>
</section>

<!-- CTA Final -->
<section class="cta-section">
  <h2 class="section-title">¿Listo para Explorar la Historia?</h2>
  <p>Únete a Liberty Map y descubre los conflictos que forjaron a México</p>
  <a href="registro.php" class="btn-hero btn-primary">Comenzar Ahora</a>
</section>

<!-- Footer -->
<footer class="footer">
  <p>&copy; 2025   LibertyMap.</p>
  <p>Desarrollado con tecnologías de código abierto</p>
</footer>

<script>
// Función para alternar el tema
function toggleTheme() {
  const body = document.body;
  const btnTheme = document.querySelector('.theme-toggle');
  
  body.classList.toggle('dark-mode');
  
  if (body.classList.contains('dark-mode')) {
    localStorage.setItem('theme', 'dark');
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
    btnTheme.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    `;
  }
}

// Cargar el tema guardado al cargar la página
window.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  const btnTheme = document.querySelector('.theme-toggle');
  
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
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
  }
});
</script>

</body>
</html>