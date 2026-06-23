<?php


include 'C:\xampp\htdocs\evospace\includes\header.php';
include 'C:\xampp\htdocs\evospace\includes\navbar.php';
require_once 'C:\xampp\htdocs\evospace\config\db.php';
require_once 'C:\xampp\htdocs\evospace\secciones\eventoscasi\models\EventoModel.php';
require_once 'C:\xampp\htdocs\evospace\secciones\eventoscasi\models\NotificacionModel.php';

$mensaje = '';
$eventoModel = new EventoModel($pdo);

// 1. PROCESAR NUEVO EVENTO (POST tradicional igual que agregar_pago)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_evento') {
    $datos = [
        'titulo'            => trim($_POST['titulo']),
        'fecha'             => $_POST['fecha'],
        'hora'              => !empty($_POST['hora']) ? $_POST['hora'] : null,
        'lugar'             => !empty($_POST['lugar']) ? trim($_POST['lugar']) : null,
        'enlace_ubicacion'  => !empty($_POST['enlace_ubicacion']) ? trim($_POST['enlace_ubicacion']) : null,
        'descripcion'       => !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null,
        'color'             => $_POST['color'] ?? '#8B1A1A',
        'ramas'             => $_POST['ramas'] ?? [] // IDs de los cursos seleccionados (1, 2, 3...)
    ];

    try {
        $eventoModel->crearEvento($datos);
        $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Evento registrado y notificaciones enviadas correctamente.';
    } catch (Exception $e) {
        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
    }
}

// 2. PROCESAR ELIMINACIÓN DE EVENTO (GET tradicional por URL)
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id_eliminar = (int)$_GET['id'];
    try {
        $eventoModel->eliminarEvento($id_eliminar);
        $mensaje = '<i class="bi bi-trash-fill text-success"></i> Evento eliminado correctamente.';
    } catch (Exception $e) {
        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al eliminar: ' . $e->getMessage();
    }
}

// 3. FILTROS SUPERIORES 
$tipoSeleccionado = $_GET['tipo'] ?? ''; // Mantenemos la lógica de tipos por compatibilidad de interfaz
$cursoSeleccionado = $_GET['curso'] ?? 0;

// Traemos los cursos para el selector dinámico del filtro superior
$cursosFiltro = [];
$sqlFiltro = "SELECT id, nombre FROM cursos ORDER BY nombre";
$cursosFiltro = $pdo->query($sqlFiltro)->fetchAll(PDO::FETCH_ASSOC);

// CORREGIDO CON EL PDF: La tabla 'cursos' usa 'id' y 'nombre'
$sqlTodosCursos = "SELECT id, nombre FROM cursos ORDER BY nombre";
$todosLosCursos = $pdo->query($sqlTodosCursos)->fetchAll(PDO::FETCH_ASSOC);

// 4. OBTENER Y FILTRAR EVENTOS
$todosEventos = $eventoModel->obtenerEventos();
$eventosFiltrados = [];

foreach ($todosEventos as $ev) {
    $cumpleCurso = true;

    // Si se filtra por un curso específico (por su ID numérico)
    if ($cursoSeleccionado > 0) {
        $cumpleCurso = false;
        if (isset($ev['ramas']) && is_array($ev['ramas'])) {
            foreach ($ev['ramas'] as $rama) {
                // Comparamos contra 'id' o 'id_curso' según devuelva tu EventoModel
                if ((isset($rama['id']) && $rama['id'] == $cursoSeleccionado) || (isset($rama['id_curso']) && $rama['id_curso'] == $cursoSeleccionado)) {
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
?>

<div class="container mt-3" style="font-family: 'Montserrat', sans-serif;">
    <?php if ($mensaje): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
                        <label class="form-label mb-1 small text-muted fw-bold">Seleccionar Curso / Modalidad</label>
                        <select name="curso" id="filtroCurso" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0">Todos los cursos</option>
                            <?php foreach ($cursosFiltro as $curso): ?>
                                <option value="<?= $curso['id'] ?>" <?= $cursoSeleccionado == $curso['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nombre']) ?>
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

    <div class="row g-2 mb-3">
        <div class="col-md-12">
            <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar evento por título, lugar o descripción...">
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-calendar-event-fill"></i> Panel de Eventos Disponibles
            <?php if ($cursoSeleccionado > 0): ?>
                <span class="badge bg-light text-dark ms-2">
                    <?php
                    $cursoNombre = array_filter($cursosFiltro, function ($c) use ($cursoSeleccionado) {
                        return $c['id'] == $cursoSeleccionado;
                    });
                    echo !empty($cursoNombre) ? reset($cursoNombre)['nombre'] : 'Curso';
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
                                <th style="width: 60px;">Color</th>
                                <th>Título del Evento</th>
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
                                        <div class="rounded shadow-sm" style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($ev['color'] ?? '#8B1A1A') ?>; margin: 0 auto;"></div>
                                    </td>
                                    <td class="evento-info">
                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($ev['titulo']) ?></span>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;"><?= htmlspecialchars($ev['descripcion'] ?? 'Sin descripción') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-calendar3 text-danger me-1"></i><?= htmlspecialchars($ev['fecha']) ?><br>
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
                                                    <span class="badge bg-info text-dark font-monospace small" style="font-size: 0.75rem;">
                                                        <?= htmlspecialchars($rama['nombre']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="?accion=eliminar&id=<?= $ev['id_evento'] ?><?= $cursoSeleccionado ? '&curso='.$cursoSeleccionado : '' ?>" 
                                           class="btn btn-outline-danger btn-sm fw-bold"
                                           onclick="return confirm('¿Estás seguro de que querés eliminar el evento: \'<?= htmlspecialchars($ev['titulo']) ?>\'?');">
                                            <i class="bi bi-trash-fill"></i>
                                            <span class="d-none d-sm-inline">Eliminar</span>
                                        </a>
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
                            <input type="text" name="titulo" class="form-control form-control-sm" required placeholder="Ej: Festival de Fin de Año Evolucionarte">
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
                            <label class="form-label fw-bold small">Lugar / Establecimiento</label>
                            <input type="text" name="lugar" class="form-control form-control-sm" placeholder="Ej: Teatro Municipal de Asunción">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Enlace de Ubicación (Google Maps URL)</label>
                            <input type="url" name="enlace_ubicacion" class="form-control form-control-sm" placeholder="https://maps.google.com/...">
                        </div>

                        <div class="col-md-9">
                            <label class="form-label fw-bold small">Descripción del Evento</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="2" placeholder="Detalles organizativos adicionales..."></textarea>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Color Distintivo</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#8B1A1A" style="height: 38px;">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger mb-1 small"><i class="bi bi-bell-fill"></i> Seleccionar Cursos a Notificar (Ramas):</label>
                            <div class="p-3 border rounded bg-light" style="max-height: 160px; overflow-y: auto;">
                                <div class="row g-2">
                                    <?php foreach ($todosLosCursos as $cursoItem): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="ramas[]" value="<?= $cursoItem['id'] ?>" id="curso_check_<?= $cursoItem['id'] ?>">
                                                <label class="form-check-label small" for="curso_check_<?= $cursoItem['id'] ?>">
                                                    <?= htmlspecialchars($cursoItem['nombre']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <small class="text-muted">A los cursos marcados se les generará una notificación automática en el sistema.</small>
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

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="toastNotificacion" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-bell-fill text-warning me-2 animate-bounce"></i>
                <strong class="me-auto">¡EvoSpace Sistema!</strong><br>
                <span id="toastMensaje">El evento se registró correctamente y las ramas fueron notificadas.</span>
            </div>
            <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Detectamos si la alerta invisible de PHP que pusimos arriba está en la pantalla
    const alertaInfo = document.querySelector('.alert-info');
    
    if (alertaInfo && alertaInfo.textContent.includes('Evento registrado')) {
        // Extraemos los datos reales que pusiste en el formulario para armar la notificación
        const tituloInput = document.querySelector('input[name="titulo"]');
        const tituloEvento = tituloInput ? tituloInput.value : "Nuevo Evento";
        
        // Personalizamos el texto del cartel flotante
        document.getElementById('toastMensaje').innerHTML = `Se creó con éxito el evento: <strong>"${tituloEvento}"</strong> y se generaron los avisos en la base de datos.`;

        // 2. Inicializamos y mostramos el cartel flotante de Bootstrap
        const elementoToast = document.getElementById('toastNotificacion');
        const tuNotificacion = new bootstrap.Toast(elementoToast);
        tuNotificacion.show();
    }
});
</script>

<style>
/* Un pequeño efecto de animación para la campanita de la alerta */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}
.animate-bounce {
    display: inline-block;
    animation: bounce 0.5s infinite alternate;
}
</style>
<?php include 'C:\xampp\htdocs\evospace\includes\footer.php'; ?>
