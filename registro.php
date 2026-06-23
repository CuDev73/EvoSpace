<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
require_once 'config/db.php';

$mensaje = '';
$tipoMensaje = 'info';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Eliminar alumno
    if ($accion === 'eliminar' && isset($_POST['id_alumno'])) {
        $id = (int)$_POST['id_alumno'];
        $stmt = $pdo->prepare("DELETE FROM alumnos WHERE id_alumno = ?");
        if ($stmt->execute([$id])) {
            $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno eliminado correctamente.';
            $tipoMensaje = 'success';
        } else {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al eliminar.';
            $tipoMensaje = 'danger';
        }
    }

    // Guardar (agregar/editar) alumno
    if ($accion === 'guardar') {
        $id_alumno = isset($_POST['id_alumno']) ? (int)$_POST['id_alumno'] : 0;
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $curso = $_POST['curso'];
        $anio_ingreso = (int)$_POST['anio_ingreso'];
        $horas = ($curso === 'Curso Superior') ? (float)($_POST['horas_profesionales'] ?? 0) : 0;
        $ci = trim($_POST['ci']);
        $telefono = trim($_POST['telefono']);
        $id_padre = !empty($_POST['id_padre']) ? (int)$_POST['id_padre'] : NULL;
        $becado = isset($_POST['becado']) ? 1 : 0;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || empty($apellido) || empty($ci)) {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Nombre, apellido y cédula son obligatorios.';
            $tipoMensaje = 'danger';
        } else {
            try {
                if ($id_alumno > 0) {
                    $sql = "UPDATE alumnos SET 
                                nombre=?, apellido=?, curso=?, anio_ingreso=?, 
                                horas_profesionales=?, ci=?, telefono=?, id_padre=?, becado=?, activo=?
                            WHERE id_alumno=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nombre, $apellido, $curso, $anio_ingreso, $horas, $ci, $telefono, $id_padre, $becado, $activo, $id_alumno]);
                    $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno actualizado correctamente.';
                    $tipoMensaje = 'success';
                } else {
                    $sql = "INSERT INTO alumnos (nombre, apellido, curso, anio_ingreso, horas_profesionales, ci, telefono, id_padre, becado, activo)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nombre, $apellido, $curso, $anio_ingreso, $horas, $ci, $telefono, $id_padre, $becado, $activo]);
                    $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Alumno creado correctamente.';
                    $tipoMensaje = 'success';
                }
            } catch (PDOException $e) {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
                $tipoMensaje = 'danger';
            }
        }
    }

    // Crear nuevo padre
    if ($accion === 'crear_padre') {
        $usuario = trim($_POST['usuario_padre']);
        $email = trim($_POST['email_padre']);
        $cedula = trim($_POST['cedula_padre']);
        $password = $_POST['password_padre'];

        if (empty($usuario) || empty($email) || empty($cedula) || empty($password)) {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Todos los campos son obligatorios.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            if ($stmt->fetch()) {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> El usuario "' . htmlspecialchars($usuario) . '" ya existe. Usa otro nombre.';
                $tipoMensaje = 'danger';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $sql = "INSERT INTO usuarios (usuario, email, cedula, password_hash, rol, activo)
                            VALUES (?, ?, ?, ?, 'padre', 1)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$usuario, $email, $cedula, $hash]);
                    $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Padre creado correctamente. Ahora selecciona al padre en el campo correspondiente.';
                    $tipoMensaje = 'success';
                } catch (PDOException $e) {
                    $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
                    $tipoMensaje = 'danger';
                }
            }
        }
    }
}

// ==========================================================
// OBTENER DATOS CON FILTRO POR PADRE (opcional)
// ==========================================================
$filtro_padre = isset($_GET['padre']) ? (int)$_GET['padre'] : 0;

$alumnos = [];
$sql = "SELECT a.*, u.usuario AS nombre_padre, u.email AS email_padre 
        FROM alumnos a
        LEFT JOIN usuarios u ON a.id_padre = u.id_usuario
        WHERE 1=1";
$params = [];
if ($filtro_padre > 0) {
    $sql .= " AND a.id_padre = ?";
    $params[] = $filtro_padre;
}
$sql .= " ORDER BY a.id_alumno DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista de padres para el filtro y para el select del modal
$padres = [];
$stmt = $pdo->query("SELECT id_usuario, usuario, email FROM usuarios WHERE rol = 'padre' ORDER BY usuario");
$padres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cursos = ['Acrotelas', 'Curso Superior', 'Curso Infantil'];
?>

<div class="container mt-3 pb-4">
    <div class="bg-danger text-white p-3 rounded mb-3">
        <h4 class="h4 fw-bold mb-0">EvoSpace</h4>
        <p class="mb-0">Gestión de Alumnos</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar por nombre, curso o cédula...">
        </div>
        <div class="col-md-4">
            <select id="filtroPadre" class="form-select form-select-sm" onchange="window.location.href='?padre='+this.value">
                <option value="0">Todos los padres</option>
                <?php foreach ($padres as $p): ?>
                    <option value="<?= $p['id_usuario'] ?>" <?= $filtro_padre == $p['id_usuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['usuario'] . ' (' . $p['email'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="limpiarFormularioAlumno()">
                <i class="bi bi-person-plus-fill"></i> Nuevo Alumno
            </button>
        </div>
    </div>

    <!-- Listado de alumnos en tarjetas -->
    <div class="row" id="listaAlumnos">
        <?php if (empty($alumnos)): ?>
            <div class="col-12">
                <div class="alert alert-warning">No hay alumnos registrados<?= $filtro_padre > 0 ? ' para este padre' : '' ?>.</div>
            </div>
        <?php else: ?>
            <?php foreach ($alumnos as $alumno): ?>
                <div class="col-md-6 col-lg-4 mb-4 tarjeta-alumno" 
                     data-nombre="<?= strtolower($alumno['nombre'] . ' ' . $alumno['apellido']) ?>" 
                     data-curso="<?= strtolower($alumno['curso']) ?>" 
                     data-ci="<?= $alumno['ci'] ?>">
                    <div class="card shadow h-100">
                        <div class="card-header bg-danger text-white fw-bold d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?></span>
                            <?php if ($alumno['becado']): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Becado</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p><strong>Curso:</strong> <?= htmlspecialchars($alumno['curso']) ?></p>
                            <p><strong>Año de ingreso:</strong> <?= $alumno['anio_ingreso'] ?></p>
                            <p><strong>CI:</strong> <?= htmlspecialchars($alumno['ci']) ?></p>
                            <?php if ($alumno['curso'] === 'Curso Superior'): ?>
                                <p><strong>Horas profesionales:</strong> <?= number_format($alumno['horas_profesionales'], 2) ?> hs</p>
                            <?php endif; ?>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($alumno['telefono'] ?? 'N/A') ?></p>
                            <p><strong>Padre/Madre:</strong> <?= htmlspecialchars($alumno['nombre_padre'] ?? 'Sin asignar') ?></p>
                            <p class="mb-0">
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
<!-- MODAL para AGREGAR / EDITAR ALUMNO -->
<!-- ========================================================== -->
<div class="modal fade" id="modalAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalTituloAlumno">Nuevo Alumno</h5>
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
                            <div class="form-check">
                                <input type="checkbox" name="becado" id="becado" class="form-check-input" value="1">
                                <label class="form-check-label" for="becado">Becado (descuento en cuotas)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="activo" id="activo" class="form-check-input" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Padre/Madre (opcional)</label>
                            <div class="d-flex gap-1">
                                <select name="id_padre" id="id_padre" class="form-select flex-grow-1">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($padres as $p): ?>
                                        <option value="<?= $p['id_usuario'] ?>"><?= htmlspecialchars($p['usuario'] . ' (' . $p['email'] . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoPadre">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
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

<!-- ========================================================== -->
<!-- MODAL para NUEVO PADRE -->
<!-- ========================================================== -->
<div class="modal fade" id="modalNuevoPadre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill"></i> Nuevo Padre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formNuevoPadre">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear_padre">
                    <div class="mb-3">
                        <label class="form-label">Usuario *</label>
                        <input type="text" name="usuario_padre" id="usuario_padre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email_padre" id="email_padre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula_padre" id="cedula_padre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" name="password_padre" id="password_padre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Crear padre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ==========================================================
    // FUNCIONES
    // ==========================================================

    function toggleHoras() {
        const curso = document.getElementById('curso').value;
        const divHoras = document.getElementById('divHoras');
        if (curso === 'Curso Superior') {
            divHoras.style.display = 'block';
        } else {
            divHoras.style.display = 'none';
            document.getElementById('horas_profesionales').value = '0';
        }
    }

    function limpiarFormularioAlumno() {
        document.getElementById('modalTituloAlumno').innerText = 'Nuevo Alumno';
        document.getElementById('id_alumno').value = '0';
        document.getElementById('nombre').value = '';
        document.getElementById('apellido').value = '';
        document.getElementById('curso').value = 'Acrotelas';
        document.getElementById('anio_ingreso').value = new Date().getFullYear();
        document.getElementById('horas_profesionales').value = '0';
        document.getElementById('ci').value = '';
        document.getElementById('telefono').value = '';
        document.getElementById('becado').checked = false;
        document.getElementById('activo').checked = true;
        document.getElementById('id_padre').value = '';
        toggleHoras();
    }

    function editarAlumno(alumno) {
        document.getElementById('modalTituloAlumno').innerText = 'Editar Alumno';
        document.getElementById('id_alumno').value = alumno.id_alumno;
        document.getElementById('nombre').value = alumno.nombre;
        document.getElementById('apellido').value = alumno.apellido;
        document.getElementById('curso').value = alumno.curso;
        document.getElementById('anio_ingreso').value = alumno.anio_ingreso;
        document.getElementById('horas_profesionales').value = alumno.horas_profesionales || 0;
        document.getElementById('ci').value = alumno.ci;
        document.getElementById('telefono').value = alumno.telefono || '';
        document.getElementById('becado').checked = (alumno.becado == 1);
        document.getElementById('activo').checked = (alumno.activo == 1);
        document.getElementById('id_padre').value = alumno.id_padre || '';
        toggleHoras();
    }

    // ==========================================================
    // FILTRO DE BÚSQUEDA EN TIEMPO REAL
    // ==========================================================
    document.getElementById('buscador').addEventListener('keyup', function() {
        const filtro = this.value.toLowerCase();
        const tarjetas = document.querySelectorAll('.tarjeta-alumno');
        tarjetas.forEach(tarjeta => {
            const nombre = tarjeta.dataset.nombre || '';
            const curso = tarjeta.dataset.curso || '';
            const ci = tarjeta.dataset.ci || '';
            const coincide = nombre.includes(filtro) || curso.includes(filtro) || ci.includes(filtro);
            tarjeta.style.display = coincide ? '' : 'none';
        });
    });

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        toggleHoras();
    });
</script>

<?php include 'includes/footer.php'; ?>