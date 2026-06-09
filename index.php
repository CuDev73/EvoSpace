<?php
session_start();
require_once './config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $eleccion = trim($_POST['eleccion']);
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios
            WHERE email = ?
            OR cedula = ?
            OR nombre = ?
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$eleccion, $eleccion, $eleccion]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['password_hash'])) {

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['cedula'] = $usuario['cedula'];
        $_SESSION['rol'] = $usuario['rol'];

        header("Location: panel.php");
        exit();

    } else {

        header("Location: index.php?error=1");
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
<body class="bg-light">

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center m-3">
            Usuario o contraseña incorrectos
        </div>
    <?php endif; ?>

    <div class="container d-flex justify-content-center align-items-center vh-100">

        <div class="card shadow p-4" style="width: 400px;">

            <h3 class="text-center mb-4">Iniciar Sesión</h3>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">
                        Nombre, Email o Cédula
                    </label>

                    <input
                        type="text"
                        name="eleccion"
                        class="form-control"
                        placeholder="Ingrese nombre, email o cédula"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        name="contrasena"
                        class="form-control"
                        placeholder="••••••••"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Iniciar sesión
                </button>

                <div class="text-center mt-3">
                    <a href="registro.php" class="btn btn-secondary">
                        Registrarse
                    </a>
                </div>

            </form>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>