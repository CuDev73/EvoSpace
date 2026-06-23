<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include '../../includes/header.php';
include '../../includes/navbar.php';
require_once '../../config/db.php';
require_once 'models/EventoModel.php';
require_once 'models/NotificacionModel.php';

$mensaje = '';
$eventoModel = new EventoModel($pdo);

// ==========================================================
// 1. PROCESAR NUEVO EVENTO
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_evento') {
    $datos = [
        'titulo'            => trim($_POST['titulo']),
        'fecha'             => $_POST['fecha'],
        'hora'              => !empty($_POST['hora']) ? $_POST['hora'] : null,
        'lugar'             => !empty($_POST['lugar']) ? trim($_POST['lugar']) : null,
        'enlace_ubicacion'  => !empty($_POST['enlace_ubicacion']) ? trim($_POST['enlace_ubicacion']) : null,
        'descripcion'       => !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null,
        'color'             => $_POST['color'] ?? '#c81015',
        'ramas'             => isset($_POST['ramas']) ? array_map('intval', $_POST['ramas']) : []
    ];

    try {
        $eventoModel->crearEvento($datos);
        $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Evento registrado correctamente.';
    } catch (Exception $e) {
        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
    }
}

// ==========================================================
// 2. ACTUALIZAR EVENTO (editar)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar_evento') {
    $id_evento = (int)$_POST['id_evento'];
    $datos = [
        'titulo'            => trim($_POST['titulo']),
        'fecha'             => $_POST['fecha'],
        'hora'              => !empty($_POST['hora']) ? $_POST['hora'] : null,
        'lugar'             => !empty($_POST['lugar']) ? trim($_POST['lugar']) : null,
        'enlace_ubicacion'  => !empty($_POST['enlace_ubicacion']) ? trim($_POST['enlace_ubicacion']) : null,
        'descripcion'       => !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null,
        'color'             => $_POST['color'] ?? '#c81015',
        'ramas'             => isset($_POST['ramas']) ? array_map('intval', $_POST['ramas']) : []
    ];

    try {
        $eventoModel->actualizarEvento($id_evento, $datos);
        $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Evento actualizado correctamente.';
    } catch (Exception $e) {
        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
    }
}

// ==========================================================
// 3. ELIMINAR EVENTO
// ==========================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id_eliminar = (int)$_GET['id'];
    try {
        $eventoModel->eliminarEvento($id_eliminar);
        $mensaje = '<i class="bi bi-trash-fill text-success"></i> Evento eliminado correctamente.';
    } catch (Exception $e) {
        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al eliminar: ' . $e->getMessage();
    }
}

// ==========================================================
// 4. OBTENER CURSOS Y EVENTOS
// ==========================================================
$cursoSeleccionado = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

$sqlCursos = "SELECT id_curso, nombre, tipo FROM cursos WHERE activo = 1 ORDER BY tipo, orden";
$stmt = $pdo->query($sqlCursos);
$todosCursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cursosPorTipo = [];
foreach ($todosCursos as $c) {
    $cursosPorTipo[$c['tipo']][] = $c;
}

$todosEventos = $eventoModel->obtenerEventos();

$eventosFiltrados = [];
foreach ($todosEventos as $ev) {
    $cumpleCurso = true;
    if ($cursoSeleccionado > 0) {
        $cumpleCurso = false;
        if (isset($ev['ramas']) && is_array($ev['ramas'])) {
            foreach ($ev['ramas'] as $rama) {
                if (isset($rama['id_curso']) && $rama['id_curso'] == $cursoSeleccionado) {
                    $cumpleCurso = true;
                    break;
                }
            }
        }
    }
    if ($cumpleCurso) {
        $eventosFiltrados[] = $ev;
    }
}

$tipoColores = [
    'Acrotelas' => 'warning',
    'Infantil'  => 'info',
    'Superior'  => 'primary'
];
?>

<div class="container mt-3">
    <?php if ($mensaje): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="card shadow mb-3">
        <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-funnel"></i> Filtrar eventos por curso</span>
            <button class="btn btn-sm btn-light fw-bold text-danger" data-bs-toggle="modal" data-bs-target="#modalNuevoEvento">
                <i class="bi bi-plus-circle-fill text-danger"></i> Nuevo Evento
            </button>
        </div>
        <div class="card-body py-2">
            <form method="GET" id="filtroForm">
                <div class="row g-2">
                    <div class="col-md-8 d-flex flex-column">
                        <label class="form-label mb-1 small text-muted fw-bold">Seleccionar Curso</label>
                        <select name="curso" id="filtroCurso" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0">Todos los cursos</option>
                            <?php foreach ($todosCursos as $curso): ?>
                                <option value="<?= $curso['id_curso'] ?>" <?= $cursoSeleccionado == $curso['id_curso'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['tipo'] . ' - ' . $curso['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-end">
                        <a href="eventos.php" class="btn btn-secondary btn-sm w-100">
                            <i class="bi bi-arrow-clockwise"></i> Limpiar filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="row g-2 mb-3">
        <div class="col-md-12">
            <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar evento por título, lugar o descripción...">
        </div>
    </div>

    <!-- TABLA DE EVENTOS -->
    <div class="card shadow">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-calendar-event-fill"></i> Panel de Eventos
            <?php if ($cursoSeleccionado > 0): ?>
                <span class="badge bg-light text-dark ms-2">
                    <?php
                    $cursoNombre = array_filter($todosCursos, function ($c) use ($cursoSeleccionado) {
                        return $c['id_curso'] == $cursoSeleccionado;
                    });
                    $curso = !empty($cursoNombre) ? reset($cursoNombre) : null;
                    echo $curso ? htmlspecialchars($curso['tipo'] . ' - ' . $curso['nombre']) : 'Curso';
                    ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="card-body p-2">
            <?php if (empty($eventosFiltrados)): ?>
                <div class="alert alert-warning mb-0">No se encontraron eventos programados para el curso seleccionado.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle" id="tablaEventos">
                        <thead class="text-center table-light">
                            <tr>
                                <th style="width: 50px;">Color</th>
                                <th>Título</th>
                                <th>Fecha y Hora</th>
                                <th>Lugar</th>
                                <th>Cursos Notificados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventosFiltrados as $ev): ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="rounded shadow-sm" style="width: 28px; height: 28px; background-color: <?= htmlspecialchars($ev['color'] ?? '#c81015') ?>; margin: 0 auto;"></div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($ev['titulo']) ?></span>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;"><?= htmlspecialchars($ev['descripcion'] ?? 'Sin descripción') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-calendar3 text-danger me-1"></i><?= date('d/m/Y', strtotime($ev['fecha'])) ?><br>
                                        <small class="text-muted fw-bold"><i class="bi bi-clock me-1"></i><?= !empty($ev['hora']) ? htmlspecialchars($ev['hora']) : '--:--' ?></small>
                                    </td>
                                    <td>
                                        <span class="small d-block text-center"><?= htmlspecialchars($ev['lugar'] ?? 'No especificado') ?></span>
                                        <?php if (!empty($ev['enlace_ubicacion'])): ?>
                                            <a href="<?= htmlspecialchars($ev['enlace_ubicacion']) ?>" target="_blank" class="btn btn-link p-0 d-block text-center small text-primary"><i class="bi bi-geo-alt-fill"></i> Ver mapa</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <?php if (empty($ev['ramas'])): ?>
                                                <span class="badge bg-secondary small">General (Todos)</span>
                                            <?php else: ?>
                                                <?php foreach ($ev['ramas'] as $rama): ?>
                                                    <?php 
                                                        $tipo = $rama['tipo'] ?? 'Superior';
                                                        $color = $tipoColores[$tipo] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $color ?> text-dark font-monospace small" style="font-size: 0.7rem;">
                                                        <?= htmlspecialchars($tipo . ' - ' . ($rama['nombre'] ?? 'Curso')) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-warning btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalEditarEvento"
                                                    onclick="cargarEvento(<?= htmlspecialchars(json_encode($ev)) ?>)">
                                                <i class="bi bi-pencil-fill"></i>
                                                <span class="d-none d-sm-inline">Editar</span>
                                            </button>
                                            <a href="?accion=eliminar&id=<?= $ev['id_evento'] ?><?= $cursoSeleccionado ? '&curso='.$cursoSeleccionado : '' ?>" 
                                               class="btn btn-danger btn-sm px-3"
                                               onclick="return confirm('¿Estás seguro de que querés eliminar el evento: \'<?= htmlspecialchars($ev['titulo']) ?>\'?');">
                                                <i class="bi bi-trash-fill"></i>
                                                <span class="d-none d-sm-inline">Eliminar</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL NUEVO EVENTO -->
<!-- ========================================================== -->
<div class="modal fade" id="modalNuevoEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Registrar Nuevo Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEvento">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="agregar_evento">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Título del Evento *</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required placeholder="Ej: Festival de Fin de Año">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fecha *</label>
                            <input type="date" name="fecha" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Hora</label>
                            <input type="time" name="hora" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Lugar</label>
                            <input type="text" name="lugar" class="form-control form-control-sm" placeholder="Ej: Teatro Municipal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Enlace de Ubicación</label>
                            <input type="url" name="enlace_ubicacion" class="form-control form-control-sm" placeholder="https://maps.google.com/...">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold small">Descripción</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="2" placeholder="Detalles adicionales..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#c81015" style="height: 38px;">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger mb-1 small"><i class="bi bi-bell-fill"></i> Seleccionar Cursos a Notificar:</label>
                            <div class="p-3 border rounded bg-light" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($cursosPorTipo as $tipo => $cursos): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold small text-uppercase text-muted"><?= $tipo ?></span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm seleccionar-todos" data-tipo="<?= $tipo ?>">
                                                Seleccionar todos
                                            </button>
                                        </div>
                                        <div class="row g-1 mt-1">
                                            <?php foreach ($cursos as $curso): ?>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="ramas[]" value="<?= $curso['id_curso'] ?>" id="curso_check_<?= $curso['id_curso'] ?>">
                                                        <label class="form-check-label small" for="curso_check_<?= $curso['id_curso'] ?>">
                                                            <?= htmlspecialchars($curso['nombre']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">A los cursos marcados se les generará una notificación automática.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-save"></i> Guardar e Informar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL EDITAR EVENTO -->
<!-- ========================================================== -->
<div class="modal fade" id="modalEditarEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditarEvento">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar_evento">
                    <input type="hidden" name="id_evento" id="edit_id_evento" value="0">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Título del Evento *</label>
                            <input type="text" name="titulo" id="edit_titulo" class="form-control form-control-sm" required placeholder="Ej: Festival de Fin de Año">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fecha *</label>
                            <input type="date" name="fecha" id="edit_fecha" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Hora</label>
                            <input type="time" name="hora" id="edit_hora" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Lugar</label>
                            <input type="text" name="lugar" id="edit_lugar" class="form-control form-control-sm" placeholder="Ej: Teatro Municipal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Enlace de Ubicación</label>
                            <input type="url" name="enlace_ubicacion" id="edit_enlace" class="form-control form-control-sm" placeholder="https://maps.google.com/...">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold small">Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control form-control-sm" rows="2" placeholder="Detalles adicionales..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Color</label>
                            <input type="color" name="color" id="edit_color" class="form-control form-control-color w-100" value="#c81015" style="height: 38px;">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger mb-1 small"><i class="bi bi-bell-fill"></i> Seleccionar Cursos a Notificar:</label>
                            <div class="p-3 border rounded bg-light" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($cursosPorTipo as $tipo => $cursos): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold small text-uppercase text-muted"><?= $tipo ?></span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm seleccionar-todos-edit" data-tipo="<?= $tipo ?>">
                                                Seleccionar todos
                                            </button>
                                        </div>
                                        <div class="row g-1 mt-1">
                                            <?php foreach ($cursos as $curso): ?>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="ramas[]" value="<?= $curso['id_curso'] ?>" id="edit_curso_check_<?= $curso['id_curso'] ?>">
                                                        <label class="form-check-label small" for="edit_curso_check_<?= $curso['id_curso'] ?>">
                                                            <?= htmlspecialchars($curso['nombre']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">A los cursos marcados se les generará una notificación automática.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-save"></i> Actualizar Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Buscador en tabla
    const buscador = document.getElementById('buscador');
    if (buscador) {
        buscador.addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            document.querySelectorAll('#tablaEventos tbody tr').forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });
    }

    // "Seleccionar todos" en modal nuevo
    document.querySelectorAll('.seleccionar-todos').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.mb-3');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const todasMarcadas = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !todasMarcadas);
            this.textContent = todasMarcadas ? 'Seleccionar todos' : 'Deseleccionar todos';
        });
    });

    // "Seleccionar todos" en modal editar
    document.querySelectorAll('.seleccionar-todos-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.mb-3');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const todasMarcadas = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !todasMarcadas);
            this.textContent = todasMarcadas ? 'Seleccionar todos' : 'Deseleccionar todos';
        });
    });
});

// Cargar datos del evento en el modal de edición
function cargarEvento(evento) {
    document.getElementById('edit_id_evento').value = evento.id_evento;
    document.getElementById('edit_titulo').value = evento.titulo;
    document.getElementById('edit_fecha').value = evento.fecha;
    document.getElementById('edit_hora').value = evento.hora || '';
    document.getElementById('edit_lugar').value = evento.lugar || '';
    document.getElementById('edit_enlace').value = evento.enlace_ubicacion || '';
    document.getElementById('edit_descripcion').value = evento.descripcion || '';
    document.getElementById('edit_color').value = evento.color || '#c81015';

    // Marcar checkboxes de cursos según las ramas del evento
    const ramasIds = evento.ramas ? evento.ramas.map(r => r.id_curso) : [];
    document.querySelectorAll('#modalEditarEvento input[name="ramas[]"]').forEach(cb => {
        cb.checked = ramasIds.includes(parseInt(cb.value));
    });

    // Actualizar texto de los botones "Seleccionar todos" según el estado
    document.querySelectorAll('.seleccionar-todos-edit').forEach(btn => {
        const container = btn.closest('.mb-3');
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const todasMarcadas = Array.from(checkboxes).every(cb => cb.checked);
        btn.textContent = todasMarcadas ? 'Deseleccionar todos' : 'Seleccionar todos';
    });
}
</script>

<?php include '../../includes/footer.php'; ?>