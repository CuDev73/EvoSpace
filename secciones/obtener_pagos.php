<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit('No autorizado');
}

require_once '../config/db.php';

if (!isset($_GET['id_alumno']) || !is_numeric($_GET['id_alumno'])) {
    http_response_code(400);
    exit('ID inválido');
}

$id_alumno = (int)$_GET['id_alumno'];

$sql = "SELECT a.*, c.nombre AS curso_nombre, c.tipo AS curso_tipo
        FROM alumnos a
        INNER JOIN cursos c ON a.id_curso = c.id_curso
        WHERE a.id_alumno = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_alumno]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    http_response_code(404);
    exit('Alumno no encontrado');
}

$sql = "SELECT * FROM pagos WHERE id_alumno = ? ORDER BY fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_alumno]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar cuotas de otros conceptos
$cuotas = [];
$otros = [];
foreach ($pagos as $pago) {
    if (stripos($pago['concepto'], 'cuota') !== false) {
        $cuotas[] = $pago;
    } else {
        $otros[] = $pago;
    }
}

$totalCuotas = count($cuotas);
$sumaCuotas = array_sum(array_column($cuotas, 'total'));
$promedioCuota = $totalCuotas > 0 ? $sumaCuotas / $totalCuotas : 0;
$totalOtros = count($otros);
$sumaOtros = array_sum(array_column($otros, 'total'));
?>
<div class="modal-header bg-info text-white">
    <h5 class="modal-title">
        <i class="bi bi-receipt"></i> Pagos de <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <p><strong>Curso:</strong> <?= htmlspecialchars($alumno['curso_tipo'] . ' - ' . $alumno['curso_nombre']) ?></p>
    <?php if ($alumno['becado']): ?>
        <p><span class="badge bg-warning text-dark">Becado (45.45% descuento en cuotas)</span></p>
    <?php endif; ?>

    <?php if (empty($pagos)): ?>
        <div class="alert alert-info">Este alumno no tiene pagos registrados.</div>
    <?php else: ?>
        <ul class="nav nav-tabs" id="pagosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="cuotas-tab" data-bs-toggle="tab" data-bs-target="#cuotas" type="button" role="tab">
                    <i class="bi bi-calendar-check"></i> Cuotas 
                    <span class="badge bg-secondary"><?= $totalCuotas ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="otros-tab" data-bs-toggle="tab" data-bs-target="#otros" type="button" role="tab">
                    <i class="bi bi-tag"></i> Otros conceptos
                    <span class="badge bg-secondary"><?= $totalOtros ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="pagosTabsContent">
            <!-- PESTAÑA CUOTAS -->
            <div class="tab-pane fade show active" id="cuotas" role="tabpanel">
                <?php if (empty($cuotas)): ?>
                    <div class="alert alert-info">No hay cuotas registradas.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Cant.</th>
                                    <th>Monto</th>
                                    <th>Dto %</th>
                                    <th>Recargo</th>
                                    <th>Total</th>
                                    <th>Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cuotas as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($pago['concepto']) ?></td>
                                        <td><?= $pago['cantidad'] ?></td>
                                        <td><?= number_format($pago['monto'], 0, ',', '.') ?> Gs</td>
                                        <td><?= number_format($pago['descuento'], 2) ?>%</td>
                                        <td><?= number_format($pago['recargo'], 0, ',', '.') ?> Gs</td>
                                        <td><strong><?= number_format($pago['total'], 0, ',', '.') ?> Gs</strong></td>
                                        <td><?= $pago['metodo_pago'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="6" class="text-end">Resumen de cuotas:</th>
                                    <th><?= number_format($sumaCuotas, 0, ',', '.') ?> Gs</th>
                                    <th></th>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="6" class="text-end"><small>Promedio por cuota:</small></td>
                                    <td><small><?= number_format($promedioCuota, 0, ',', '.') ?> Gs</small></td>
                                    <td></td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="6" class="text-end"><small>Cantidad de cuotas:</small></td>
                                    <td><small><?= $totalCuotas ?></small></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PESTAÑA OTROS -->
            <div class="tab-pane fade" id="otros" role="tabpanel">
                <?php if (empty($otros)): ?>
                    <div class="alert alert-info">No hay otros conceptos registrados.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Cant.</th>
                                    <th>Monto</th>
                                    <th>Dto %</th>
                                    <th>Recargo</th>
                                    <th>Total</th>
                                    <th>Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($otros as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($pago['concepto']) ?></td>
                                        <td><?= $pago['cantidad'] ?></td>
                                        <td><?= number_format($pago['monto'], 0, ',', '.') ?> Gs</td>
                                        <td><?= number_format($pago['descuento'], 2) ?>%</td>
                                        <td><?= number_format($pago['recargo'], 0, ',', '.') ?> Gs</td>
                                        <td><strong><?= number_format($pago['total'], 0, ',', '.') ?> Gs</strong></td>
                                        <td><?= $pago['metodo_pago'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="6" class="text-end">Total otros conceptos:</th>
                                    <th><?= number_format($sumaOtros, 0, ',', '.') ?> Gs</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen global -->
        <div class="alert alert-secondary mt-3">
            <strong>Total general pagado:</strong> <?= number_format($sumaCuotas + $sumaOtros, 0, ',', '.') ?> Gs
            <span class="ms-3"><strong>Cuotas:</strong> <?= number_format($sumaCuotas, 0, ',', '.') ?> Gs</span>
            <span class="ms-3"><strong>Otros:</strong> <?= number_format($sumaOtros, 0, ',', '.') ?> Gs</span>
        </div>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>