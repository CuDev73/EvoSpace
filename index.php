<?php
session_start();
require_once 'config/db.php';

// Redirigir si ya está logueado
if (isset($_SESSION['id_usuario'])) {
    $stmt = $pdo->prepare("SELECT r.nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $rol = $stmt->fetchColumn();
    redirigirSegunRol($rol);
    exit;
}

$error = isset($_GET['error']) ? 'Usuario o contraseña incorrectos.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleccion = trim($_POST['eleccion']);
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT u.*, r.nombre AS rol_nombre 
            FROM usuarios u 
            JOIN roles r ON u.id_rol = r.id_rol 
            WHERE u.usuario = ? OR u.email = ? OR u.cedula = ? 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$eleccion, $eleccion, $eleccion]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $usuario['activo'] && password_verify($contrasena, $usuario['password_hash'])) {
        session_regenerate_id(true);
        
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['usuario']    = $usuario['usuario'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol']        = $usuario['rol_nombre'];
        $_SESSION['nombre_completo'] = $usuario['nombre_completo'] ?? $usuario['usuario'];
        
        // Cargar permisos
        $_SESSION['permisos'] = obtenerPermisos($pdo, $usuario['id_rol']);
        
        redirigirSegunRol($usuario['rol_nombre']);
        exit;
    } else {
        header('Location: index.php?error=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EvoSpace - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/x-icon" href="evo.ico">
</head>
<body class="bg-light">
    <div class="container vh-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-5">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="img/evolucionarte-removebg-preview.ico" alt="Logo" width="100" class="mb-3">
                            <h3>Iniciar Sesión</h3>
                        </div>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuario, Email o Cédula</label>
                                <input type="text" name="eleccion" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="contrasena" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Iniciar Sesión</button>
                        </form>
                        <div class="text-center mt-3">
                            <small class="text-muted">EvoSpace - Sistema de Gestión</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>