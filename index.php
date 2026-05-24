<?php
session_start();
require_once './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleccion = $_POST['eleccion'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? OR telefono = ? OR ci = ?");
    $stmt->execute([$eleccion, $eleccion, $eleccion]);
    $usuario = $stmt->fetch();

    if ($usuario !== false && password_verify($contrasena, $usuario['contrasena'])) {
        $_SESSION['usuario'] = $usuario;

        if ($usuario['rol'] == 'admin') {
            header('Location: roles/admin.php');
        } else if ($usuario['rol'] == 'profesor') {
            header('Location: roles/profesor.php');
        } else {
            header('Location: roles/alumno.php');
        }
        exit();
    } else {
        header('Location: index.php?error=1');
        exit();
    }
}
?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="./evo.ico">
    </head>
    <body>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center">Email o contraseña incorrectos</div>
        <?php endif; ?>

        <div class="container d-flex justify-content-center align-items-center vh-100">
            <div class="card shadow p-4" style="width: 400px;">
                <h4 class="text-center mb-4">Iniciar sesión</h4>
                <form method="POST" action="index.php">                    

                    <div class="mb-3">
                        <label class="form-label">Email - CI - Número de teléfono</label>
                        <input type="text" name="eleccion" class="form-control" placeholder="Ingrese email - numero - cedula" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                    <div class="d-flex justify-content-center m-4">
                        <a href="./registro.php" class="btn btn-secondary">Aún no estas registrado?</a>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>