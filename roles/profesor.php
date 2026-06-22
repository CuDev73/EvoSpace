<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: /evospace/index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container mt-3">
    <h1>Panel de Profesor</h1>
    <p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></p>
</div>
<?php include '../includes/footer.php'; ?>