<?php
require_once './config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $cedula = trim($_POST['cedula']);
    $rol = $_POST['rol'];
    $password = $_POST['password'];

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios
            (nombre, apellido, email, password_hash, cedula, rol)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        $nombre,
        $apellido,
        $email,
        $password_hash,
        $cedula,
        $rol
    ])) {
        $mensaje = "Usuario creado correctamente";
    } else {
        $mensaje = "Error al crear usuario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header">
            <h3>Nuevo Usuario</h3>
        </div>

        <div class="card-body">

            <?php if ($mensaje): ?>
                <div class="alert alert-info">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Apellido</label>
                    <input type="text" name="apellido" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Cédula</label>
                    <input type="text" name="cedula" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Rol</label>
                    <select name="rol" class="form-select">
                        <option value="admin">Admin</option>
                        <option value="profesor">Profesor</option>
                        <option value="padre">Padre</option>
                    </select>
                </div>

                <button class="btn btn-success">
                    Guardar Usuario
                </button>

            </form>

        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>