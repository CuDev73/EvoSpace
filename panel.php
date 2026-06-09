<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
</head>
<body>

    <h1>Bienvenido <?= htmlspecialchars($_SESSION['nombre']) ?></h1>

    <p>ID: <?= $_SESSION['id_usuario'] ?></p>
    <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>
    <p>Cédula: <?= htmlspecialchars($_SESSION['cedula']) ?></p>
    <p>Rol: <?= htmlspecialchars($_SESSION['rol']) ?></p>

    <a href="index.php">Cerrar sesión</a>

</body>
</html>