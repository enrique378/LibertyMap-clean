<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Mexico_City');

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
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

// ==================== ELIMINAR COMENTARIO ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_comentario'])) {
    $comentario_id = isset($_POST['comentario_id']) ? intval($_POST['comentario_id']) : 0;
    
    if ($comentario_id > 0) {
        // Verificar que el comentario pertenece al usuario actual
        $sql_verificar = "SELECT idComentario, usuario, imagen FROM comentarios WHERE idComentario = $comentario_id AND usuario = '" . $conn->real_escape_string($usuario_actual) . "'";
        $resultado_verificar = $conn->query($sql_verificar);
        
        if ($resultado_verificar && $resultado_verificar->num_rows > 0) {
            $comentario_data = $resultado_verificar->fetch_assoc();
            
            // Eliminar imagen asociada si existe
            if (!empty($comentario_data['imagen']) && file_exists($comentario_data['imagen'])) {
                unlink($comentario_data['imagen']);
            }
            
            // Eliminar primero las respuestas asociadas y sus imágenes
            $sql_obtener_respuestas = "SELECT idComentario, imagen FROM comentarios WHERE comentario_padre_id = $comentario_id";
            $resultado_respuestas = $conn->query($sql_obtener_respuestas);
            
            if ($resultado_respuestas) {
                while ($respuesta = $resultado_respuestas->fetch_assoc()) {
                    if (!empty($respuesta['imagen']) && file_exists($respuesta['imagen'])) {
                        unlink($respuesta['imagen']);
                    }
                }
            }
            
            // Eliminar respuestas
            $sql_eliminar_respuestas = "DELETE FROM comentarios WHERE comentario_padre_id = $comentario_id";
            $conn->query($sql_eliminar_respuestas);
            
            // Eliminar el comentario principal
            $sql_eliminar = "DELETE FROM comentarios WHERE idComentario = $comentario_id";
            
            if ($conn->query($sql_eliminar) === TRUE) {
                header("Location: comunidad.php?exito=eliminado");
                exit();
            } else {
                header("Location: comunidad.php?error=eliminar_fallo");
                exit();
            }
        } else {
            header("Location: comunidad.php?error=no_autorizado");
            exit();
        }
    } else {
        header("Location: comunidad.php?error=id_invalido");
        exit();
    }
}

// ==================== PUBLICAR COMENTARIO ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comentario'])) {
    $nombre = $_SESSION['usuario'];
    $comentario = trim($_POST['comentario']);
    $comentario_padre_id = isset($_POST['comentario_padre_id']) && !empty($_POST['comentario_padre_id']) 
                           ? intval($_POST['comentario_padre_id']) 
                           : NULL;
    
    // Procesar imagen si fue subida
    $imagen_url = null;
    if (isset($_FILES['imagen_comentario']) && $_FILES['imagen_comentario']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['imagen_comentario'];
        
        // Validar tipo de archivo
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, $extensiones_permitidas)) {
            // Validar tamaño (máximo 5MB)
            if ($archivo['size'] <= 5 * 1024 * 1024) {
                // Crear directorio si no existe
                $directorio_uploads = 'uploads/comentarios/';
                if (!file_exists($directorio_uploads)) {
                    mkdir($directorio_uploads, 0777, true);
                }
                
                // Generar nombre único
                $nombre_archivo = uniqid('img_') . '_' . time() . '.' . $extension;
                $ruta_destino = $directorio_uploads . $nombre_archivo;
                
                // Mover archivo
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $imagen_url = $ruta_destino;
                }
            } else {
                $params = http_build_query([
                    'error' => 'imagen_grande',
                    'comentario' => $comentario,
                    'comentario_padre_id' => $comentario_padre_id
                ]);
                header("Location: comunidad.php?{$params}");
                exit();
            }
        } else {
            $params = http_build_query([
                'error' => 'imagen_tipo',
                'comentario' => $comentario,
                'comentario_padre_id' => $comentario_padre_id
            ]);
            header("Location: comunidad.php?{$params}");
            exit();
        }
    }
    
    if (!empty($comentario) || $imagen_url !== null) {
        $comentario_aprobado = false;
        $info_rechazo = null;
        
        // Solo moderar si hay texto
        if (!empty($comentario)) {
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
        } else {
            // Si solo hay imagen, aprobar
            $comentario_aprobado = true;
        }
                
        if (!$comentario_aprobado && $info_rechazo !== null) {
            // Eliminar imagen si el comentario fue rechazado
            if ($imagen_url && file_exists($imagen_url)) {
                unlink($imagen_url);
            }
            
            $params = http_build_query([
                'error' => 'ia',
                'razon' => $info_rechazo['razon'],
                'comentario' => $info_rechazo['comentario'],
                'palabra' => $info_rechazo['palabra'],
                'comentario_padre_id' => $comentario_padre_id
            ]);
            
            header("Location: comunidad.php?{$params}");
            exit();
        }
        
        $nombre_escaped = $conn->real_escape_string($nombre);
        $comentario_escaped = $conn->real_escape_string($comentario);
        $imagen_escaped = $imagen_url ? $conn->real_escape_string($imagen_url) : NULL;
        
        // Insertar con o sin comentario_padre_id e imagen
        if ($comentario_padre_id !== NULL) {
            if ($imagen_escaped !== NULL) {
                $sql = "INSERT INTO comentarios (usuario, comentario, imagen, comentario_padre_id) 
                        VALUES ('$nombre_escaped', '$comentario_escaped', '$imagen_escaped', $comentario_padre_id)";
            } else {
                $sql = "INSERT INTO comentarios (usuario, comentario, comentario_padre_id) 
                        VALUES ('$nombre_escaped', '$comentario_escaped', $comentario_padre_id)";
            }
        } else {
            if ($imagen_escaped !== NULL) {
                $sql = "INSERT INTO comentarios (usuario, comentario, imagen) 
                        VALUES ('$nombre_escaped', '$comentario_escaped', '$imagen_escaped')";
            } else {
                $sql = "INSERT INTO comentarios (usuario, comentario) 
                        VALUES ('$nombre_escaped', '$comentario_escaped')";
            }
        }
        
        if ($conn->query($sql) === TRUE) {
            header("Location: comunidad.php?exito=1");
            exit();
        } else {
            // Eliminar imagen si falla la inserción
            if ($imagen_url && file_exists($imagen_url)) {
                unlink($imagen_url);
            }
            header("Location: comunidad.php?error=1");
            exit();
        }
        
    } else {
        header("Location: comunidad.php?error=2");
        exit();
    }
}

// ==================== MENSAJES DE ALERTA ====================
$mensaje = '';
$comentario_rechazado = '';
$comentario_padre_id_recuperado = '';

if (isset($_GET['exito'])) {
    if ($_GET['exito'] == 1) {
        $mensaje = '<div class="alert alert-success">✅ ¡Comentario publicado exitosamente!</div>';
    } elseif ($_GET['exito'] == 'eliminado') {
        $mensaje = '<div class="alert alert-success">🗑️ Comentario eliminado correctamente.</div>';
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        $mensaje = '<div class="alert alert-error">❌ Error al publicar el comentario.</div>';
    } elseif ($_GET['error'] == 2) {
        $mensaje = '<div class="alert alert-error">⚠️ Por favor escribe un comentario o sube una imagen.</div>';
    } elseif ($_GET['error'] == 'eliminar_fallo') {
        $mensaje = '<div class="alert alert-error">❌ Error al eliminar el comentario.</div>';
    } elseif ($_GET['error'] == 'no_autorizado') {
        $mensaje = '<div class="alert alert-error">🚫 No tienes permiso para eliminar este comentario.</div>';
    } elseif ($_GET['error'] == 'id_invalido') {
        $mensaje = '<div class="alert alert-error">⚠️ ID de comentario inválido.</div>';
    } elseif ($_GET['error'] == 'imagen_grande') {
        $mensaje = '<div class="alert alert-error">⚠️ La imagen es demasiado grande. Tamaño máximo: 5MB.</div>';
        $comentario_rechazado = isset($_GET['comentario']) ? htmlspecialchars(urldecode($_GET['comentario'])) : '';
        $comentario_padre_id_recuperado = isset($_GET['comentario_padre_id']) ? intval($_GET['comentario_padre_id']) : '';
    } elseif ($_GET['error'] == 'imagen_tipo') {
        $mensaje = '<div class="alert alert-error">⚠️ Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WEBP.</div>';
        $comentario_rechazado = isset($_GET['comentario']) ? htmlspecialchars(urldecode($_GET['comentario'])) : '';
        $comentario_padre_id_recuperado = isset($_GET['comentario_padre_id']) ? intval($_GET['comentario_padre_id']) : '';
    } elseif ($_GET['error'] == 'ia') {
        $razon = isset($_GET['razon']) ? htmlspecialchars(urldecode($_GET['razon'])) : 'contenido inapropiado';
        $comentario_rechazado = isset($_GET['comentario']) ? htmlspecialchars(urldecode($_GET['comentario'])) : '';
        $palabra_detectada = isset($_GET['palabra']) ? htmlspecialchars(urldecode($_GET['palabra'])) : '';
        $comentario_padre_id_recuperado = isset($_GET['comentario_padre_id']) ? intval($_GET['comentario_padre_id']) : '';
        
        $mensaje = '<div class="alert alert-error">
            <strong>🚫 Comentario bloqueado por moderación</strong><br>
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

// ==================== OBTENER COMENTARIOS ====================
// Obtener solo comentarios principales (sin padre)
$sql = "SELECT c.*, u.foto_perfil,
        (SELECT COUNT(*) FROM comentarios WHERE comentario_padre_id = c.idComentario) as total_respuestas
        FROM comentarios c 
        LEFT JOIN usuario u ON c.usuario = u.usuario 
        WHERE c.comentario_padre_id IS NULL
        ORDER BY c.fecha_creacion DESC";
$resultado = $conn->query($sql);
$comentarios = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $comentarios[] = $fila;
        
        // Obtener respuestas de este comentario
        $id_comentario = $fila['idComentario'];
        $sql_respuestas = "SELECT c.*, u.foto_perfil 
                          FROM comentarios c 
                          LEFT JOIN usuario u ON c.usuario = u.usuario 
                          WHERE c.comentario_padre_id = $id_comentario 
                          ORDER BY c.fecha_creacion ASC";
        $resultado_respuestas = $conn->query($sql_respuestas);
        
        $respuestas = [];
        if ($resultado_respuestas) {
            while ($respuesta = $resultado_respuestas->fetch_assoc()) {
                $respuestas[] = $respuesta;
            }
        }
        $comentarios[count($comentarios) - 1]['respuestas'] = $respuestas;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibertyMap - Comunidad</title>
    <link rel="icon" type="image/x-icon" href="Img/Logo - LibertyMap.png">
    <link rel="stylesheet" href="Styles/comunidad-style.css">
    <style>
        /* Estilos adicionales para las imágenes */
        .image-upload-container {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .dark-mode .image-upload-container {
            background: #2a2a2a;
            border-color: #444;
        }

        .image-upload-container:hover {
            border-color: #0066cc;
            background: #e7f3ff;
        }

        .dark-mode .image-upload-container:hover {
            border-color: #0066cc;
            background: #1a3a52;
        }

        .image-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 10px;
        }

        .dark-mode .image-upload-label {
            color: #aaa;
        }

        .image-upload-label:hover {
            color: #0066cc;
        }

        .image-upload-label svg {
            width: 24px;
            height: 24px;
        }

        #imagen_comentario {
            display: none;
        }

        .image-preview {
            margin-top: 15px;
            position: relative;
            display: none;
        }

        .image-preview.active {
            display: block;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .image-preview-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }

        .image-preview-remove:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .comment-image {
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
        }

        .comment-image img {
            width: 100%;
            max-width: 600px;
            height: auto;
            display: block;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .comment-image img:hover {
            transform: scale(1.02);
        }

        /* Modal para ver imagen en grande */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s;
        }

        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-modal-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            animation: zoomIn 0.3s;
        }

        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .image-modal-close:hover {
            color: #bbb;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { transform: scale(0.5); }
            to { transform: scale(1); }
        }

        .image-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dark-mode .image-info {
            color: #999;
        }

        /* Estilos para el botón de eliminar comentario */
        .btn-eliminar {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 6px 12px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-eliminar:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #c82333;
        }

        .btn-eliminar svg {
            width: 16px;
            height: 16px;
        }

        /* Modal de confirmación para eliminar */
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .delete-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
        }

        .dark-mode .delete-modal-content {
            background: #2a2a2a;
            color: #fff;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delete-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .delete-modal-header svg {
            width: 48px;
            height: 48px;
            color: #dc3545;
        }

        .delete-modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .dark-mode .delete-modal-header h3 {
            color: #fff;
        }

        .delete-modal-body {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.6;
        }

        .dark-mode .delete-modal-body {
            color: #ccc;
        }

        .delete-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-cancel {
            background: #e9ecef;
            color: #495057;
        }

        .btn-modal-cancel:hover {
            background: #dee2e6;
        }

        .dark-mode .btn-modal-cancel {
            background: #444;
            color: #fff;
        }

        .dark-mode .btn-modal-cancel:hover {
            background: #555;
        }

        .btn-modal-delete {
            background: #dc3545;
            color: white;
        }

        .btn-modal-delete:hover {
            background: #c82333;
        }

        .comment-owner-badge {
            background: #e7f3ff;
            color: #0066cc;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 500;
            margin-left: 8px;
        }

        .dark-mode .comment-owner-badge {
            background: #1a3a52;
            color: #5eb3ff;
        }
    </style>
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
    <a href="batallas.php" class="nav-item">
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
  <!-- Barra Superior -->
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
      <div class="comments-counter">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span><?php echo count($comentarios); ?></span>
      </div>
      <button class="btn-theme" id="btnTheme" onclick="toggleTheme()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
    </div>
  </div>

  <!-- Contenido de Comunidad -->
  <div class="community-container">
    
    <!-- Form para Nuevo Comentario -->
    <div class="new-comment-card">
      <div class="card-header">
        <h2>Nuevo Comentario</h2>
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
      
      <form method="POST" action="" id="formComentario" enctype="multipart/form-data">
        <input type="hidden" name="comentario_padre_id" id="comentario_padre_id" value="<?php echo $comentario_padre_id_recuperado; ?>">
        
        <div class="form-group">
          <!-- Indicador de respuesta -->
          <div id="respondiendo-a" class="respondiendo-indicador" style="display: <?php echo !empty($comentario_padre_id_recuperado) ? 'flex' : 'none'; ?>;">
            <span>Respondiendo a <strong id="usuario-respuesta"></strong></span>
            <button type="button" onclick="cancelarRespuesta()" class="btn-cancelar-respuesta">✕</button>
          </div>
          
          <textarea id="comentario" name="comentario" placeholder="Comparte tu opinión, pregunta o comentario sobre LibertyMap..." rows="4"><?php echo $comentario_rechazado; ?></textarea>
          
          <!-- Contenedor para subir imagen -->
          <div class="image-upload-container">
            <label for="imagen_comentario" class="image-upload-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              <span id="image-upload-text">Agregar imagen (opcional)</span>
            </label>
            <input type="file" id="imagen_comentario" name="imagen_comentario" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(this)">
            <div class="image-info">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              <span>Formatos: JPG, PNG, GIF, WEBP • Tamaño máximo: 5MB</span>
            </div>
          </div>
          
          <!-- Vista previa de imagen -->
          <div class="image-preview" id="imagePreview">
            <img id="previewImg" src="" alt="Vista previa">
            <button type="button" class="image-preview-remove" onclick="removeImage()">×</button>
          </div>
        </div>
        
        <button type="submit" name="enviar_comentario" class="btn-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
          <span id="texto-boton"><?php echo !empty($comentario_padre_id_recuperado) ? 'Publicar Respuesta' : 'Publicar Comentario'; ?></span>
        </button>
      </form>
    </div>
    
    <!-- Lista de Comentarios -->
    <div class="comments-section">
      <div class="section-header">
        <h2>Conversaciones</h2>
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
            <div class="comment-card" id="comentario-<?php echo $com['idComentario']; ?>">
              <div class="comment-header">
                <img src="<?php echo !empty($com['foto_perfil']) ? htmlspecialchars($com['foto_perfil']) : 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($com['usuario']); ?>" 
                     class="comment-avatar"
                     onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
                <div class="comment-user-info">
                  <div style="display: flex; align-items: center;">
                    <span class="comment-username"><?php echo htmlspecialchars($com['usuario']); ?></span>
                    <?php if ($com['usuario'] === $usuario_actual): ?>
                      <span class="comment-owner-badge">Tú</span>
                    <?php endif; ?>
                  </div>
                  <span class="comment-date"><?php 
                    $fecha = new DateTime($com['fecha_creacion']);
                    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                    echo $fecha->format('j') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y') . ' a las ' . $fecha->format('H:i');
                  ?></span>
                </div>
              </div>
              
              <div class="comment-body">
                <?php if (!empty($com['comentario'])): ?>
                  <?php echo nl2br(htmlspecialchars($com['comentario'])); ?>
                <?php endif; ?>
                
                <?php if (!empty($com['imagen'])): ?>
                  <div class="comment-image">
                    <img src="<?php echo htmlspecialchars($com['imagen']); ?>" 
                         alt="Imagen del comentario" 
                         onclick="openImageModal('<?php echo htmlspecialchars($com['imagen']); ?>')">
                  </div>
                <?php endif; ?>
              </div>
              
              <!-- Botones de acción -->
              <div class="comment-actions">
                <button onclick="responderComentario(<?php echo $com['idComentario']; ?>, '<?php echo htmlspecialchars($com['usuario'], ENT_QUOTES); ?>')" 
                        class="btn-responder">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 10 4 15 9 20"></polyline>
                    <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                  </svg>
                  Responder
                </button>
                
                <?php if ($com['usuario'] === $usuario_actual): ?>
                  <button onclick="confirmarEliminar(<?php echo $com['idComentario']; ?>)" class="btn-eliminar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      <line x1="10" y1="11" x2="10" y2="17"></line>
                      <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                    Eliminar
                  </button>
                <?php endif; ?>
                
                <?php if ($com['total_respuestas'] > 0): ?>
                  <span class="respuestas-count">
                    <?php echo $com['total_respuestas']; ?> 
                    <?php echo $com['total_respuestas'] == 1 ? 'respuesta' : 'respuestas'; ?>
                  </span>
                <?php endif; ?>
              </div>
              
              <!-- Respuestas -->
              <?php if (!empty($com['respuestas'])): ?>
                <div class="respuestas-container">
                  <?php foreach ($com['respuestas'] as $respuesta): ?>
                    <div class="comment-card respuesta">
                      <div class="comment-header">
                        <img src="<?php echo !empty($respuesta['foto_perfil']) ? htmlspecialchars($respuesta['foto_perfil']) : 'https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($respuesta['usuario']); ?>" 
                             class="comment-avatar"
                             onerror="this.src='https://i.pinimg.com/736x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg'">
                        <div class="comment-user-info">
                          <div style="display: flex; align-items: center;">
                            <span class="comment-username"><?php echo htmlspecialchars($respuesta['usuario']); ?></span>
                            <?php if ($respuesta['usuario'] === $usuario_actual): ?>
                              <span class="comment-owner-badge">Tú</span>
                            <?php endif; ?>
                          </div>
                          <span class="comment-date"><?php 
                            $fecha = new DateTime($respuesta['fecha_creacion']);
                            echo $fecha->format('j') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y') . ' a las ' . $fecha->format('H:i');
                          ?></span>
                        </div>
                      </div>
                      
                      <div class="comment-body">
                        <?php if (!empty($respuesta['comentario'])): ?>
                          <?php echo nl2br(htmlspecialchars($respuesta['comentario'])); ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($respuesta['imagen'])): ?>
                          <div class="comment-image">
                            <img src="<?php echo htmlspecialchars($respuesta['imagen']); ?>" 
                                 alt="Imagen de la respuesta" 
                                 onclick="openImageModal('<?php echo htmlspecialchars($respuesta['imagen']); ?>')">
                          </div>
                        <?php endif; ?>
                      </div>
                      
                      <!-- Botón de eliminar para respuestas propias -->
                      <?php if ($respuesta['usuario'] === $usuario_actual): ?>
                        <div class="comment-actions" style="margin-top: 10px;">
                          <button onclick="confirmarEliminar(<?php echo $respuesta['idComentario']; ?>)" class="btn-eliminar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            Eliminar
                          </button>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal para ver imagen en grande -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
  <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
  <img class="image-modal-content" id="modalImage">
</div>

<!-- Modal de confirmación para eliminar comentario -->
<div id="deleteModal" class="delete-modal">
  <div class="delete-modal-content" onclick="event.stopPropagation();">
    <div class="delete-modal-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <h3>¿Eliminar comentario?</h3>
    </div>
    <div class="delete-modal-body">
      <p>Esta acción no se puede deshacer. El comentario y todas sus respuestas serán eliminados permanentemente.</p>
    </div>
    <div class="delete-modal-actions">
      <button class="btn-modal btn-modal-cancel" onclick="cerrarModalEliminar()">Cancelar</button>
      <form method="POST" action="" id="formEliminar" style="display: inline;">
        <input type="hidden" name="comentario_id" id="comentario_id_eliminar" value="">
        <button type="submit" name="eliminar_comentario" class="btn-modal btn-modal-delete">Eliminar</button>
      </form>
    </div>
  </div>
</div>

<script>
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

  function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const uploadText = document.getElementById('image-upload-text');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        preview.classList.add('active');
        uploadText.textContent = '✅ Imagen seleccionada: ' + input.files[0].name;
      };
      
      reader.readAsDataURL(input.files[0]);
    }
  }

  function removeImage() {
    const input = document.getElementById('imagen_comentario');
    const preview = document.getElementById('imagePreview');
    const uploadText = document.getElementById('image-upload-text');
    
    input.value = '';
    preview.classList.remove('active');
    uploadText.textContent = '📷 Agregar imagen (opcional)';
  }

  function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.classList.add('active');
    modalImg.src = imageSrc;
  }

  function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
  }

  function confirmarEliminar(comentarioId) {
    const modal = document.getElementById('deleteModal');
    document.getElementById('comentario_id_eliminar').value = comentarioId;
    modal.classList.add('active');
  }

  function cerrarModalEliminar() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
    document.getElementById('comentario_id_eliminar').value = '';
  }

  function responderComentario(comentarioId, nombreUsuario) {
    document.getElementById('comentario_padre_id').value = comentarioId;

    document.getElementById('respondiendo-a').style.display = 'flex';
    document.getElementById('usuario-respuesta').textContent = nombreUsuario;
    
    document.getElementById('texto-boton').textContent = 'Publicar Respuesta';
    
    document.getElementById('comentario').placeholder = `Responder a ${nombreUsuario}...`;
    
    document.getElementById('formComentario').scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function() {
      document.getElementById('comentario').focus();
    }, 500);
  }

  function cancelarRespuesta() {
    document.getElementById('comentario_padre_id').value = '';
    
    document.getElementById('respondiendo-a').style.display = 'none';
    
    document.getElementById('texto-boton').textContent = 'Publicar Comentario';
    
    document.getElementById('comentario').placeholder = 'Comparte tu opinión, pregunta o comentario sobre LibertyMap...';
    
    const url = new URL(window.location);
    url.searchParams.delete('comentario_padre_id');
    window.history.replaceState({}, document.title, url);
  }

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
    
    const urlParams = new URLSearchParams(window.location.search);
    const comentarioPadreId = urlParams.get('comentario_padre_id');
    
    if (comentarioPadreId && comentarioPadreId !== '') {
      const comentarioCard = document.getElementById('comentario-' + comentarioPadreId);
      if (comentarioCard) {
        const nombreUsuario = comentarioCard.querySelector('.comment-username').textContent;
        responderComentario(parseInt(comentarioPadreId), nombreUsuario);
      }
    }
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

  // Cerrar modales con tecla Escape
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeImageModal();
      cerrarModalEliminar();
    }
  });

  // Cerrar modal de eliminar al hacer click fuera
  document.getElementById('deleteModal').addEventListener('click', function(event) {
    if (event.target === this) {
      cerrarModalEliminar();
    }
  });
</script>

</body>
</html>

<?php
$conn->close();
?>
