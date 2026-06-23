<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
require_once '../config/db.php';

$mensaje = '';
$tipoMensaje = 'info';

// ==========================================================
// PROCESAR ACCIONES POST
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ELIMINAR USUARIO
    if ($accion === 'eliminar' && isset($_POST['id_usuario'])) {
        $id = (int)$_POST['id_usuario'];
        if ($id == $_SESSION['id_usuario']) {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> No puedes eliminar tu propio usuario.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            if ($stmt->execute([$id])) {
                $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Usuario eliminado correctamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al eliminar.';
                $tipoMensaje = 'danger';
            }
        }
    }

    // GUARDAR USUARIO
    if ($accion === 'guardar') {
        $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        $usuario = trim($_POST['usuario']);
        $email = trim($_POST['email']);
        $cedula = trim($_POST['cedula']);
        $rol = $_POST['rol'];
        $password = $_POST['password'];
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($usuario) || empty($email) || empty($cedula)) {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Usuario, email y cédula son obligatorios.';
            $tipoMensaje = 'danger';
        } else {
            try {
                if ($id_usuario > 0) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "UPDATE usuarios SET usuario=?, email=?, cedula=?, password_hash=?, rol=?, activo=? WHERE id_usuario=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario, $email, $cedula, $hash, $rol, $activo, $id_usuario]);
                    } else {
                        $sql = "UPDATE usuarios SET usuario=?, email=?, cedula=?, rol=?, activo=? WHERE id_usuario=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario, $email, $cedula, $rol, $activo, $id_usuario]);
                    }
                    $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Usuario actualizado correctamente.';
                    $tipoMensaje = 'success';
                } else {
                    if (empty($password)) {
                        $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> La contraseña es obligatoria para nuevos usuarios.';
                        $tipoMensaje = 'danger';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO usuarios (usuario, email, cedula, password_hash, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario, $email, $cedula, $hash, $rol, $activo]);
                        $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Usuario creado correctamente.';
                        $tipoMensaje = 'success';
                    }
                }
            } catch (PDOException $e) {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error: ' . $e->getMessage();
                $tipoMensaje = 'danger';
            }
        }
    }

    // CAMBIAR ESTADO
    if ($accion === 'toggle_activo' && isset($_POST['id_usuario'])) {
        $id = (int)$_POST['id_usuario'];
        $nuevo_estado = (int)$_POST['nuevo_estado'];
        if ($id == $_SESSION['id_usuario'] && $nuevo_estado == 0) {
            $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> No puedes desactivar tu propio usuario.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
            if ($stmt->execute([$nuevo_estado, $id])) {
                $estadoTexto = $nuevo_estado ? 'activado' : 'desactivado';
                $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Usuario ' . $estadoTexto . ' correctamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Error al cambiar estado.';
                $tipoMensaje = 'danger';
            }
        }
    }

    // ASIGNAR HIJOS
    if ($accion === 'guardar_hijos' && isset($_POST['id_padre'])) {
        $id_padre = (int)$_POST['id_padre'];
        $hijos_seleccionados = isset($_POST['hijos']) ? array_map('intval', $_POST['hijos']) : [];

        $stmt = $pdo->prepare("UPDATE alumnos SET id_padre = NULL WHERE id_padre = ?");
        $stmt->execute([$id_padre]);

        if (!empty($hijos_seleccionados)) {
            $placeholders = implode(',', array_fill(0, count($hijos_seleccionados), '?'));
            $sql = "UPDATE alumnos SET id_padre = ? WHERE id_alumno IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$id_padre], $hijos_seleccionados);
            $stmt->execute($params);
        }

        $mensaje = '<i class="bi bi-check-circle-fill text-success"></i> Relación padre-hijos actualizada correctamente.';
        $tipoMensaje = 'success';
    }
}

// ==========================================================
// OBTENER DATOS CON FILTROS
// ==========================================================
$filtro_rol = isset($_GET['rol']) ? $_GET['rol'] : '';
$filtro_estado = isset($_GET['estado']) ? (int)$_GET['estado'] : -1;

$sql = "SELECT u.*, COUNT(a.id_alumno) AS total_hijos
        FROM usuarios u
        LEFT JOIN alumnos a ON u.id_usuario = a.id_padre
        WHERE 1=1";
$params = [];
if ($filtro_rol) {
    $sql .= " AND u.rol = ?";
    $params[] = $filtro_rol;
}
if ($filtro_estado !== -1) {
    $sql .= " AND u.activo = ?";
    $params[] = $filtro_estado;
}
$sql .= " GROUP BY u.id_usuario ORDER BY u.id_usuario DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = ['admin', 'profesor', 'padre'];

// ==========================================================
// OBTENER ALUMNOS CON NOMBRE, CURSO Y TIPO PARA EL MODAL
// ==========================================================
$alumnos_todos = [];
$sql = "SELECT a.id_alumno, CONCAT(a.nombre, ' ', a.apellido) AS nombre_completo, 
               c.nombre AS curso_nombre, c.tipo AS curso_tipo
        FROM alumnos a
        INNER JOIN cursos c ON a.id_curso = c.id_curso
        ORDER BY a.nombre, a.apellido";
$stmt = $pdo->query($sql);
$alumnos_todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-3 pb-4">
    <!-- Título -->
    <div class="bg-danger text-white p-3 rounded mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="h4 fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Gestión de Usuarios</h4>
            <small>Administra los usuarios del sistema y asigna hijos a padres</small>
        </div>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="limpiarFormulario()">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Rol</label>
            <select id="filtroRol" class="form-select form-select-sm" onchange="window.location.href='?rol='+this.value+'&estado='+document.getElementById('filtroEstado').value">
                <option value="">Todos</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>" <?= $filtro_rol === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Estado</label>
            <select id="filtroEstado" class="form-select form-select-sm" onchange="window.location.href='?rol='+document.getElementById('filtroRol').value+'&estado='+this.value">
                <option value="-1">Todos</option>
                <option value="1" <?= $filtro_estado === 1 ? 'selected' : '' ?>>Activos</option>
                <option value="0" <?= $filtro_estado === 0 ? 'selected' : '' ?>>Inactivos</option>
            </select>
        </div>
        <div class="col-md-6 text-end">
            <a href="?rol=&estado=-1" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Cédula</th>
                            <th>Rol</th>
                            <th>Hijos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr><td colspan="8" class="text-center">No hay usuarios registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="text-center align-middle">
                                    <td><?= $u['id_usuario'] ?></td>
                                    <td class="text-start"><i class="bi bi-person-fill me-2 text-secondary"></i><?= htmlspecialchars($u['usuario']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['cedula']) ?></td>
                                    <td><span class="badge bg-<?= $u['rol'] === 'admin' ? 'danger' : ($u['rol'] === 'profesor' ? 'info' : 'success') ?>"><?= ucfirst($u['rol']) ?></span></td>
                                    <td>
                                        <?php if ($u['rol'] === 'padre'): ?>
                                            <span class="badge bg-primary"><?= $u['total_hijos'] ?></span>
                                            <button class="btn btn-outline-primary btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#modalHijos"
                                                    onclick="cargarHijos(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars($u['usuario']) ?>')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['activo']): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario"
                                                onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="accion" value="toggle_activo">
                                                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?= $u['activo'] ? 0 : 1 ?>">
                                                <button type="submit" class="btn btn-<?= $u['activo'] ? 'warning' : 'success' ?> btn-sm">
                                                    <i class="bi bi-<?= $u['activo'] ? 'pause-circle-fill' : 'play-circle-fill' ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL para AGREGAR / EDITAR USUARIO -->
<!-- ========================================================== -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalTituloUsuario"><i class="bi bi-person-fill me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="0">

                    <div class="mb-3">
                        <label class="form-label">Usuario *</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula" id="cedula" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                        <small class="text-muted">Obligatoria para nuevos usuarios.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="rol" id="rol" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="activo" id="activo" class="form-check-input" checked>
                        <label class="form-check-label" for="activo">Activo</label>
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
<!-- MODAL para ASIGNAR HIJOS (mejorado con tipo de curso) -->
<!-- ========================================================== -->
<div class="modal fade" id="modalHijos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-people-fill me-2"></i>Asignar hijos a <span id="nombrePadre"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formHijos">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="guardar_hijos">
                    <input type="hidden" name="id_padre" id="id_padre_modal" value="0">

                    <div class="mb-3">
                        <label class="form-label">Buscar alumno</label>
                        <input type="text" id="buscadorHijos" class="form-control" placeholder="Escribe el nombre...">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="seleccionarTodos" onclick="toggleTodos()"></th>
                                    <th>Nombre</th>
                                    <th>Curso</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody id="listaHijos">
                                <?php foreach ($alumnos_todos as $alumno): ?>
                                    <tr class="fila-alumno">
                                        <td><input type="checkbox" name="hijos[]" value="<?= $alumno['id_alumno'] ?>" class="checkbox-hijo"></td>
                                        <td><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
                                        <td><?= htmlspecialchars($alumno['curso_nombre']) ?></td>
                                        <td><span class="badge bg-<?= $alumno['curso_tipo'] === 'Acrotelas' ? 'warning' : ($alumno['curso_tipo'] === 'Infantil' ? 'info' : 'primary') ?>"><?= htmlspecialchars($alumno['curso_tipo']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Selecciona los hijos que pertenecen a este padre.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function limpiarFormulario() {
        document.getElementById('modalTituloUsuario').innerHTML = '<i class="bi bi-person-fill me-2"></i>Nuevo Usuario';
        document.getElementById('id_usuario').value = '0';
        document.getElementById('usuario').value = '';
        document.getElementById('email').value = '';
        document.getElementById('cedula').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password').placeholder = 'Contraseña (obligatoria)';
        document.getElementById('password').required = true;
        document.getElementById('rol').value = 'padre';
        document.getElementById('activo').checked = true;
    }

    function editarUsuario(usuario) {
        document.getElementById('modalTituloUsuario').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Editar Usuario';
        document.getElementById('id_usuario').value = usuario.id_usuario;
        document.getElementById('usuario').value = usuario.usuario;
        document.getElementById('email').value = usuario.email;
        document.getElementById('cedula').value = usuario.cedula;
        document.getElementById('password').value = '';
        document.getElementById('password').placeholder = 'Dejar en blanco para no cambiar';
        document.getElementById('password').required = false;
        document.getElementById('rol').value = usuario.rol;
        document.getElementById('activo').checked = (usuario.activo == 1);
    }

    function cargarHijos(idPadre, nombrePadre) {
        document.getElementById('id_padre_modal').value = idPadre;
        document.getElementById('nombrePadre').textContent = nombrePadre;

        fetch('get_hijos.php?id_padre=' + idPadre)
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.checkbox-hijo').forEach(cb => {
                    cb.checked = data.includes(parseInt(cb.value));
                });
            })
            .catch(error => console.error('Error al cargar hijos:', error));
    }

    function toggleTodos() {
        const checked = document.getElementById('seleccionarTodos').checked;
        document.querySelectorAll('.checkbox-hijo').forEach(cb => cb.checked = checked);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorHijos');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                const filtro = this.value.toLowerCase();
                document.querySelectorAll('.fila-alumno').forEach(fila => {
                    const nombre = fila.cells[1].textContent.toLowerCase();
                    fila.style.display = nombre.includes(filtro) ? '' : 'none';
                });
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>