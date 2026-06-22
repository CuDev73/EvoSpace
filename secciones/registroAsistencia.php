<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /evospace/index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
require_once '../config/db.php';
// ... resto del código

// ------------------------------------------------------------------
// Procesar acciones del formulario
// ------------------------------------------------------------------
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ---------- ELIMINAR ----------
    if ($accion === 'eliminar' && isset($_POST['id_alumno'])) {
        $id = (int)$_POST['id_alumno'];
        $stmt = $pdo->prepare("DELETE FROM alumnos WHERE id_alumno = ?");
        if ($stmt->execute([$id])) {
            $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno eliminado correctamente.';
        } else {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al eliminar.';
        }
    }

    // ---------- AGREGAR / EDITAR ----------
    if ($accion === 'guardar') {
        $id_alumno = isset($_POST['id_alumno']) ? (int)$_POST['id_alumno'] : 0;
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $curso = $_POST['curso'];
        $anio_ingreso = (int)$_POST['anio_ingreso'];
        // Si el curso no es Superior, forzamos horas a 0
        $horas = ($curso === 'Curso Superior') ? (float)($_POST['horas_profesionales'] ?? 0) : 0;
        $ci = trim($_POST['ci']);
        $telefono = trim($_POST['telefono']);
        $id_padre = !empty($_POST['id_padre']) ? (int)$_POST['id_padre'] : NULL;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id_alumno > 0) {
            // Editar
            $sql = "UPDATE alumnos SET 
                        nombre=?, apellido=?, curso=?, anio_ingreso=?, 
                        horas_profesionales=?, ci=?, telefono=?, id_padre=?, activo=?
                    WHERE id_alumno=?";
            $stmt = $pdo->prepare($sql);
            $params = [$nombre, $apellido, $curso, $anio_ingreso, $horas, $ci, $telefono, $id_padre, $activo, $id_alumno];
            try {
                $stmt->execute($params);
                $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno actualizado correctamente.';
            } catch (PDOException $e) {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
            }
        } else {
            // Agregar
            $sql = "INSERT INTO alumnos (nombre, apellido, curso, anio_ingreso, horas_profesionales, ci, telefono, id_padre, activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $params = [$nombre, $apellido, $curso, $anio_ingreso, $horas, $ci, $telefono, $id_padre, $activo];
            try {
                $stmt->execute($params);
                $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno creado correctamente.';
            } catch (PDOException $e) {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
            }
        }
    }
}

// ------------------------------------------------------------------
// Obtener listado de alumnos con datos del padre (si existe)
// ------------------------------------------------------------------
$alumnos = [];
$sql = "SELECT a.*, u.usuario AS nombre_padre, u.email AS email_padre 
        FROM alumnos a
        LEFT JOIN usuarios u ON a.id_padre = u.id_usuario
        ORDER BY a.id_alumno DESC";
$stmt = $pdo->query($sql);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de padres (usuarios con rol 'padre') para el select del modal
$padres = [];
$stmt = $pdo->query("SELECT id_usuario, usuario, email FROM usuarios WHERE rol = 'padre' ORDER BY usuario");
$padres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5 pt-4">
    <!-- TÍTULO -->
    <div class="bg-danger text-white p-4 rounded mb-4">
        <h3 class="h3 fw-bold">EvoSpace</h3>
        <p class="mb-0">Curso Superior > Curso Infantil > Acrotelas</p>
    </div>

    <!-- Mensaje de feedback -->
    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Botón para agregar nuevo alumno con ícono -->
    <div class="mb-4">
        <button class="btn btn-evo w-100" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="limpiarFormulario()">
            <i class="bi bi-person-plus-fill me-2"></i> Nuevo Alumno
        </button>
    </div>

    <!-- Listado de alumnos en tarjetas -->
    <div class="row">
        <?php if (empty($alumnos)): ?>
            <div class="col-12">
                <div class="alert alert-warning">No hay alumnos registrados.</div>
            </div>
        <?php else: ?>
            <?php foreach ($alumnos as $alumno): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header bg-danger text-white fw-bold">
                            <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?>
                        </div>
                        <div class="card-body">
                            <p><strong>Año de ingreso:</strong> <?= $alumno['anio_ingreso'] ?></p>
                            <p><strong>Curso actual:</strong> <?= $alumno['curso'] ?></p>
                            <p><strong>CI:</strong> <?= htmlspecialchars($alumno['ci']) ?></p>
                            <?php if ($alumno['curso'] === 'Curso Superior'): ?>
                                <p><strong>Horas profesionales:</strong> <?= number_format($alumno['horas_profesionales'], 2) ?> hs</p>
                            <?php endif; ?>
                            <p><strong>Tel:</strong> <?= htmlspecialchars($alumno['telefono'] ?? 'N/A') ?></p>
                            <p><strong>Padre/Madre:</strong> <?= htmlspecialchars($alumno['nombre_padre'] ?? 'Sin asignar') ?></p>
                            <p>
                                <strong>Activo:</strong> 
                                <?php if ($alumno['activo']): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i> Sí
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i> No
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlumno"
                                    onclick="editarAlumno(<?= htmlspecialchars(json_encode($alumno)) ?>)">
                                <i class="bi bi-pencil-fill"></i> Editar
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este alumno?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_alumno" value="<?= $alumno['id_alumno'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL para AGREGAR / EDITAR alumno -->
<!-- ========================================================== -->
<div class="modal fade" id="modalAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalTitulo">Nuevo Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formAlumno">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="id_alumno" id="id_alumno" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Curso *</label>
                            <select name="curso" id="curso" class="form-select" required onchange="toggleHoras()">
                                <option value="Acrotelas">Acrotelas</option>
                                <option value="Curso Superior">Curso Superior</option>
                                <option value="Curso Infantil">Curso Infantil</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Año de ingreso *</label>
                            <input type="number" name="anio_ingreso" id="anio_ingreso" class="form-control" required min="2000" max="2099">
                        </div>
                        <!-- Campo de horas profesionales, oculto por defecto (solo para Superior) -->
                        <div class="col-md-6" id="divHoras" style="display:none;">
                            <label class="form-label">Horas profesionales</label>
                            <input type="number" step="0.01" name="horas_profesionales" id="horas_profesionales" class="form-control" value="0">
                            <small class="text-muted">Solo para Curso Superior</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cédula *</label>
                            <input type="text" name="ci" id="ci" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Padre/Madre (opcional)</label>
                            <select name="id_padre" id="id_padre" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($padres as $p): ?>
                                    <option value="<?= $p['id_usuario'] ?>"><?= htmlspecialchars($p['usuario'] . ' (' . $p['email'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="activo" id="activo" class="form-check-input" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para mostrar/ocultar el campo de horas según el curso seleccionado
    function toggleHoras() {
        var curso = document.getElementById('curso').value;
        var divHoras = document.getElementById('divHoras');
        if (curso === 'Curso Superior') {
            divHoras.style.display = 'block';
        } else {
            divHoras.style.display = 'none';
            document.getElementById('horas_profesionales').value = '0';
        }
    }

    function limpiarFormulario() {
        document.getElementById('modalTitulo').innerText = 'Nuevo Alumno';
        document.getElementById('id_alumno').value = '0';
        document.getElementById('nombre').value = '';
        document.getElementById('apellido').value = '';
        document.getElementById('curso').value = 'Acrotelas';
        document.getElementById('anio_ingreso').value = '<?= date("Y") ?>';
        document.getElementById('horas_profesionales').value = '0';
        document.getElementById('ci').value = '';
        document.getElementById('telefono').value = '';
        document.getElementById('id_padre').value = '';
        document.getElementById('activo').checked = true;
        toggleHoras(); // ocultar horas si no es Superior
    }

    function editarAlumno(alumno) {
        document.getElementById('modalTitulo').innerText = 'Editar Alumno';
        document.getElementById('id_alumno').value = alumno.id_alumno;
        document.getElementById('nombre').value = alumno.nombre;
        document.getElementById('apellido').value = alumno.apellido;
        document.getElementById('curso').value = alumno.curso;
        document.getElementById('anio_ingreso').value = alumno.anio_ingreso;
        document.getElementById('horas_profesionales').value = alumno.horas_profesionales || 0;
        document.getElementById('ci').value = alumno.ci;
        document.getElementById('telefono').value = alumno.telefono || '';
        document.getElementById('id_padre').value = alumno.id_padre || '';
        document.getElementById('activo').checked = (alumno.activo == 1);
        toggleHoras(); // ajustar visibilidad del campo horas según el curso cargado
    }

    // Ejecutar toggleHoras al cargar la página por si el modal se abre con edición
    document.addEventListener('DOMContentLoaded', function() {
        toggleHoras();
    });
</script>

<?php include '../includes/footer.php'; ?>