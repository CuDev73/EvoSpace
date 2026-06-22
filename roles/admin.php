<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-3">
    <div class="bg-danger text-white p-4 rounded mb-4">
        <h1 class="display-5 fw-bold">EvoSpace</h1>
        <p class="mb-0">Curso Superior > Curso Infantil > Acrotelas</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <a href="/evospace/secciones/alumnos.php" class="btn btn-danger w-100 py-3">Nueva inscripción</a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/evospace/secciones/pagos.php" class="btn btn-danger w-100 py-3">Nuevo pago</a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/evospace/secciones/eventos.php" class="btn btn-danger w-100 py-3">Nuevo evento</a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/evospace/secciones/alumnos.php" class="btn btn-danger w-100 py-3">Ver alumnos activos</a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-danger text-white fs-4">
            PRÓXIMO EVENTO: Sesión de fotos para la obra
        </div>
        <div class="card-body">
            <p>Locación: Tal parte Avda. Lalaland c/12 de junio</p>
            <p>Llevar polleras, sombreros y utilería.</p>
            <div class="text-end fw-bold text-danger">Fecha: 13 de agosto del 2026</div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>