<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include '../../includes/header.php';
include '../../includes/navbar.php';
require_once '../../config/db.php';

$mensaje = '';
$porcentaje_beca = obtenerPorcentajeBeca($pdo);

// ==========================================================
// 1. Asegurar conceptos según el tipo de curso
// ==========================================================
$conceptos_por_tipo = [
    'Acrotelas' => ['matrícula', 'cuota', 'vestuarios', 'entradas'], // NO tiene folleto
    'Infantil'  => ['matrícula', 'cuota', 'vestuarios', 'entradas', 'folleto'],
    'Superior'  => ['matrícula', 'cuota', 'vestuarios', 'entradas', 'folleto']
];

$sqlCursos = "SELECT id_curso, tipo FROM cursos WHERE activo = 1";
$stmtCursos = $pdo->query($sqlCursos);
$todosCursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

foreach ($todosCursos as $curso) {
    $conceptos_requeridos = $conceptos_por_tipo[$curso['tipo']] ?? [];
    $sqlCheck = "SELECT concepto FROM precios WHERE id_curso = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$curso['id_curso']]);
    $existentes = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

    $faltantes = array_diff($conceptos_requeridos, $existentes);
    foreach ($faltantes as $concepto) {
        $sqlInsert = "INSERT INTO precios (id_curso, concepto, precio, descuento_beca, aplica_beca) 
                      VALUES (?, ?, 0, 0, 0)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$curso['id_curso'], $concepto]);
        $mensaje = 'Se agregó el concepto "' . $concepto . '" para el curso "' . $curso['tipo'] . ' - ' . $curso['id_curso'] . '"';
    }
}

// ==========================================================
// 2. Procesar actualización de precios y porcentaje de beca
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'actualizar_precios') {
        $precios = $_POST['precio'] ?? [];
        $errores = 0;
        foreach ($precios as $id_precio => $precio) {
            $precio = (float)$precio;
            $sql = "UPDATE precios SET precio = ? WHERE id_precio = ?";
            $stmt = $pdo->prepare($sql);
            if (!$stmt->execute([$precio, $id_precio])) {
                $errores++;
            }
        }
        if ($errores === 0) {
            $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Precios actualizados correctamente.';
        } else {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al actualizar algunos precios.';
        }
    }

    if ($accion === 'actualizar_beca') {
        $nuevo_porcentaje = (float)$_POST['porcentaje_beca'];
        if ($nuevo_porcentaje >= 0 && $nuevo_porcentaje <= 100) {
            $sql = "UPDATE configuracion SET valor = ? WHERE clave = 'porcentaje_beca'";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$nuevo_porcentaje])) {
                $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Porcentaje de beca actualizado correctamente.';
                $porcentaje_beca = $nuevo_porcentaje;
            } else {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al actualizar el porcentaje de beca.';
            }
        } else {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> El porcentaje debe estar entre 0 y 100.';
        }
    }
}

// ==========================================================
// 3. Obtener datos para mostrar
// ==========================================================
$sql = "SELECT c.id_curso, c.nombre AS curso_nombre, c.tipo AS curso_tipo, 
               p.id_precio, p.concepto, p.precio
        FROM cursos c
        LEFT JOIN precios p ON c.id_curso = p.id_curso
        WHERE c.activo = 1
        ORDER BY c.tipo, c.orden, FIELD(p.concepto, 'matrícula', 'cuota', 'vestuarios', 'entradas', 'folleto')";
$stmt = $pdo->query($sql);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cursos = [];
foreach ($datos as $row) {
    $cursos[$row['curso_tipo']][$row['id_curso']]['nombre'] = $row['curso_nombre'];
    if ($row['concepto']) {
        $cursos[$row['curso_tipo']][$row['id_curso']]['precios'][$row['concepto']] = [
            'id_precio' => $row['id_precio'],
            'precio' => $row['precio']
        ];
    }
}

$conceptos_orden = ['matrícula', 'cuota', 'vestuarios', 'entradas', 'folleto'];
$iconos = [
    'matrícula' => 'bi-file-earmark-text',
    'cuota' => 'bi-calendar-check',
    'vestuarios' => 'bi-person',
    'entradas' => 'bi-ticket',
    'folleto' => 'bi-book'
];
?>

<div class="container mt-3 pb-4">
    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- ========================================================== -->
    <!-- SECCIÓN: Porcentaje de beca global (input + botón juntos) -->
    <!-- ========================================================== -->
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark py-2">
            <i class="bi bi-percent"></i> Porcentaje que paga el alumno becado
        </div>
        <div class="card-body py-3">
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_beca">

                <!-- FILA 1: Input + Botón juntos (como input-group) -->
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Porcentaje a pagar (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" name="porcentaje_beca"
                                class="form-control" value="<?= $porcentaje_beca ?>"
                                min="0" max="100" required>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Espacio vacío para mantener la alineación (opcional) -->
                    </div>
                </div>

                <!-- LÍNEA SEPARADORA -->
                <hr class="my-3">

                <!-- FILA 2: Texto de ayuda -->
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Este porcentaje se aplica a la cuota de todos los alumnos becados.
                            Valor actual: <strong><?= $porcentaje_beca ?>%</strong>. Para ingresar un porcentaje con decimal utilizar "." el punto. Ej: <strong></strong>50.50</strong> que representa la coma.  
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- SECCIÓN: Precios por curso (con redondeo al millar) -->
    <!-- ========================================================== -->
    <form method="POST" id="formPrecios">
        <input type="hidden" name="accion" value="actualizar_precios">

        <div class="row g-3">
            <?php foreach ($cursos as $tipo => $cursosTipo): ?>
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white py-2">
                            <i class="bi bi-tag"></i> <?= $tipo ?>
                            <span class="badge bg-light text-dark ms-2"><?= count($cursosTipo) ?> cursos</span>
                        </div>
                        <div class="card-body py-2">
                            <?php foreach ($cursosTipo as $id_curso => $curso): ?>
                                <div class="row mb-2 p-2 border rounded align-items-center">
                                    <div class="col-md-2 fw-bold small"><?= htmlspecialchars($curso['nombre']) ?></div>
                                    <div class="col-md-10">
                                        <div class="row g-1">
                                            <?php foreach ($conceptos_orden as $concepto):
                                                $precioData = $curso['precios'][$concepto] ?? null;
                                                if (!$precioData) continue;
                                                $precio = $precioData['precio'];
                                                // PRECIO CON BECA: precio * (porcentaje_beca / 100)
                                                $precioConBeca = ($concepto === 'cuota') ? $precio * ($porcentaje_beca / 100) : $precio;
                                                // REDONDEO AL MILLAR MÁS CERCANO (ej: 99.990 → 100.000)
                                                $precioConBecaRedondeado = round($precioConBeca / 1000) * 1000;
                                            ?>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-0">
                                                        <i class="bi <?= $iconos[$concepto] ?> me-1"></i><?= ucfirst($concepto) ?>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Gs</span>
                                                        <input type="number" step="0.01"
                                                            name="precio[<?= $precioData['id_precio'] ?>]"
                                                            class="form-control form-control-sm"
                                                            value="<?= $precio ?>">
                                                    </div>
                                                    <?php if ($concepto === 'cuota'): ?>
                                                        <small class="text-muted" style="font-size: 0.65rem;">
                                                            Con beca (<?= $porcentaje_beca ?>%): Gs <?= number_format($precioConBecaRedondeado, 0, ',', '.') ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mt-4 pb-3">
            <a href="configuracion.php" class="btn btn-secondary flex-fill">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                <i class="bi bi-save"></i> Guardar todos los precios
            </button>
        </div>
    </form>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> Confirmar cambios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas <strong>guardar todos los cambios</strong> en los precios?</p>
                <p class="text-muted small">Esta acción actualizará los precios para todos los cursos y conceptos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarGuardar">
                    <i class="bi bi-check-circle"></i> Sí, guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnConfirmarGuardar').addEventListener('click', function() {
        document.getElementById('formPrecios').submit();
    });
</script>

<?php include '../../includes/footer.php'; ?>