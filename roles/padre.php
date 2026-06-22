<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'padre') {
    header('Location: /evospace/index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
require_once '../config/db.php';

$id_padre = $_SESSION['id_usuario'];
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_padre]);
$padre = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT a.*, c.nombre AS curso_nombre, c.tipo AS curso_tipo
        FROM alumnos a
        INNER JOIN cursos c ON a.id_curso = c.id_curso
        WHERE a.id_padre = ? AND a.activo = 1
        ORDER BY a.apellido, a.nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_padre]);
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5 pt-4">
    <div class="bg-danger text-white p-4 rounded mb-4">
        <h3 class="h3 fw-bold">EvoSpace</h3>
        <p class="mb-0">Panel de Padres</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h4><i class="bi bi-person-circle"></i> Bienvenido, <?= htmlspecialchars($padre['usuario']) ?></h4>
            <p class="mb-0">Email: <?= htmlspecialchars($padre['email']) ?></p>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-people-fill"></i> Mis hijos
        </div>
        <div class="card-body">
            <?php if (empty($hijos)): ?>
                <div class="alert alert-info">No tienes hijos registrados en el sistema.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($hijos as $hijo): 
                        $sql = "SELECT SUM(total) AS total_pagado FROM pagos WHERE id_alumno = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$hijo['id_alumno']]);
                        $total_pagado = $stmt->fetch(PDO::FETCH_ASSOC)['total_pagado'] ?? 0;
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-person-fill"></i> <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Curso:</strong> <?= htmlspecialchars($hijo['curso_tipo'] . ' - ' . $hijo['curso_nombre']) ?></p>
                                    <p><strong>Año ingreso:</strong> <?= $hijo['anio_ingreso'] ?></p>
                                    <p><strong>Becado:</strong> <?= $hijo['becado'] ? 'Sí' : 'No' ?></p>
                                    <p><strong>Total pagado:</strong> <?= number_format($total_pagado, 0, ',', '.') ?> Gs</p>
                                    <a href="/evospace/secciones/pagos.php?ver_hijo=<?= $hijo['id_alumno'] ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye-fill"></i> Ver pagos
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>