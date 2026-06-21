<?php
session_start();
require_once 'config/db.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

$rol = $_SESSION['rol'];

// Si no es admin ni super_admin, redirigir a su panel según rol
if (!in_array($rol, ['admin', 'super_admin'])) {
    switch ($rol) {
        case 'profesor': header('Location: roles/profesor.php'); break;
        case 'padre':    header('Location: roles/padre.php');    break;
        case 'alumno':   header('Location: roles/alumno.php');   break;
        default:         header('Location: index.php');          break;
    }
    exit;
}

// ------------------------------------------------------------
// PROCESAR ACCIONES (AGREGAR, EDITAR, ELIMINAR)
// ------------------------------------------------------------

$mensaje = '';

// --- ELIMINAR ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // No permitir eliminar a uno mismo
    if ($id == $_SESSION['id_usuario']) {
        $mensaje = '❌ No puedes eliminar tu propio usuario.';
    } else {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $mensaje = '✅ Usuario eliminado correctamente.';
        } else {
            $mensaje = '❌ Error al eliminar.';
        }
    }
}

// --- AGREGAR o EDITAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $usuario = trim($_POST['usuario']);
        $email   = trim($_POST['email']);
        $cedula  = trim($_POST['cedula']);
        $rol     = $_POST['rol'];
        $password = $_POST['password'];
        $activo   = isset($_POST['activo']) ? 1 : 0;

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (usuario, email, cedula, password_hash, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$usuario, $email, $cedula, $hash, $rol, $activo]);
            $mensaje = '✅ Usuario creado correctamente.';
        } catch (PDOException $e) {
            $mensaje = '❌ Error: ' . $e->getMessage();
        }
    }

    elseif ($accion === 'editar') {
        $id = (int)$_POST['id_usuario'];
        $usuario = trim($_POST['usuario']);
        $email   = trim($_POST['email']);
        $cedula  = trim($_POST['cedula']);
        $rol     = $_POST['rol'];
        $activo   = isset($_POST['activo']) ? 1 : 0;
        $password = trim($_POST['password']);

        // Construir consulta dinámica: si password no está vacío, actualizar hash
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET usuario=?, email=?, cedula=?, password_hash=?, rol=?, activo=? WHERE id_usuario=?";
            $params = [$usuario, $email, $cedula, $hash, $rol, $activo, $id];
        } else {
            $sql = "UPDATE usuarios SET usuario=?, email=?, cedula=?, rol=?, activo=? WHERE id_usuario=?";
            $params = [$usuario, $email, $cedula, $rol, $activo, $id];
        }
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute($params);
            $mensaje = '✅ Usuario actualizado correctamente.';
        } catch (PDOException $e) {
            $mensaje = '❌ Error: ' . $e->getMessage();
        }
    }
}

// ------------------------------------------------------------
// OBTENER LISTA DE USUARIOS
// ------------------------------------------------------------
$usuarios = [];
$sql = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ------------------------------------------------------------
// MOSTRAR VISTA
// ------------------------------------------------------------
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestión de Usuarios</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="limpiarFormulario()">
            + Nuevo Usuario
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Cédula</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Fecha creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['cedula']) ?></td>
                        <td><?= $u['rol'] ?></td>
                        <td><?= $u['activo'] ? '✅' : '❌' ?></td>
                        <td><?= $u['fecha_creacion'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario"
                                    onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)">
                                Editar
                            </button>
                            <a href="?delete=<?= $u['id_usuario'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="8" class="text-center">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL para AGREGAR / EDITAR usuario -->
<!-- ========================================================== -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitulo">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion" value="agregar">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="">

                    <div class="mb-3">
                        <label>Usuario *</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email *</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Cédula *</label>
                        <input type="text" name="cedula" id="cedula" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                        <small class="text-muted">Al crear nuevo usuario, es obligatorio.</small>
                    </div>
                    <div class="mb-3">
                        <label>Rol *</label>
                        <select name="rol" id="rol" class="form-select" required>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="profesor">Profesor</option>
                            <option value="padre">Padre</option>
                            <option value="alumno">Alumno</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="activo" id="activo" class="form-check-input" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para limpiar el formulario cuando se abre para agregar
    function limpiarFormulario() {
        document.getElementById('modalTitulo').innerText = 'Nuevo Usuario';
        document.getElementById('accion').value = 'agregar';
        document.getElementById('id_usuario').value = '';
        document.getElementById('usuario').value = '';
        document.getElementById('email').value = '';
        document.getElementById('cedula').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password').placeholder = 'Contraseña (obligatoria)';
        document.getElementById('password').required = true;
        document.getElementById('rol').value = 'alumno';
        document.getElementById('activo').checked = true;
        document.getElementById('formUsuario').action = '';
    }

    // Función para cargar datos en el formulario al editar
    function editarUsuario(usuario) {
        document.getElementById('modalTitulo').innerText = 'Editar Usuario';
        document.getElementById('accion').value = 'editar';
        document.getElementById('id_usuario').value = usuario.id_usuario;
        document.getElementById('usuario').value = usuario.usuario;
        document.getElementById('email').value = usuario.email;
        document.getElementById('cedula').value = usuario.cedula;
        document.getElementById('password').value = '';
        document.getElementById('password').placeholder = 'Dejar en blanco para no cambiar';
        document.getElementById('password').required = false;
        document.getElementById('rol').value = usuario.rol;
        document.getElementById('activo').checked = (usuario.activo == 1);
        document.getElementById('formUsuario').action = '';
    }

    // Al abrir el modal desde el botón "Nuevo", se limpia
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = document.getElementById('modalUsuario');
        myModal.addEventListener('show.bs.modal', function (event) {
            // Si el botón que disparó no es "editar", limpiamos (ya se hace en onclick)
            // Pero por si acaso:
            var button = event.relatedTarget;
            if (button && button.getAttribute('data-bs-target') === '#modalUsuario') {
                // Si no tiene el onclick de editar, se limpia (pero ya llamamos a limpiarFormulario desde el botón Nuevo)
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>