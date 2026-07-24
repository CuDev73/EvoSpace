<?php
ob_start();

/*session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}*/

include "./funciones.php";
include '../includes/header.php';
include '../includes/navbar.php';

// ==========================================
// SECCIÓN ORIGINAL: PROFESORES
// ==========================================

if (isset($_REQUEST['idBorrar'])) {
    borrarProfesor("profesores", $_REQUEST['idBorrar']);
    header("Location: profesores.php");
    exit();
}

if (isset($_REQUEST["idEditar"])) {
    $idEditar = $_REQUEST["idEditar"];
    $p = traerPorID_PROFESORES("profesores", $idEditar);
    $id_profesor = $p->id_profesor;
    $nombre = $p->nombre;
    $apellido = $p->apellido;
    $ci = $p->ci;
    $anio_ingreso = $p->anio_ingreso;
    $salario_base = $p->salario_base;
    $activo = $p->activo;
} else {
    $idEditar = "";
    $nombre = "";
    $apellido = "";
    $ci = "";
    $anio_ingreso = "";
    $salario_base = "";
    $activo = "";
}

if (isset($_REQUEST['guardar'])) {
    $id_profesor = $_REQUEST['id_profesor'];
    $nombre = $_REQUEST['nombre'];
    $apellido = $_REQUEST['apellido'];
    $ci = $_REQUEST['ci'];
    $anio_ingreso = $_REQUEST['anio_ingreso'];
    $salario_base = $_REQUEST['salario_base'];
    $activo = $_REQUEST['activo'];
    
    if ($nombre != "") {
        if ($id_profesor == "") {
            insertarProfesor($nombre, $apellido, $ci, $anio_ingreso, $salario_base, $activo);
        } else {
            actualizarProfesor($id_profesor, $nombre, $apellido, $ci, $anio_ingreso, $salario_base, $activo);
        }
    }
    header("Location: profesores.php");
    exit();
}

$resul = traer("profesores");

// ==========================================
// SECCIÓN NUEVA: PROCESAMIENTO DE ABONOS
// ==========================================

// Borrar Abono
if (isset($_REQUEST['idBorrarAbono'])) {
    borrarAbono($_REQUEST['idBorrarAbono']);
    header("Location: profesores.php");
    exit();
}

// Cargar Datos para Editar Abono
if (isset($_REQUEST["idEditarAbono"])) {
    $idEditarAbono = $_REQUEST["idEditarAbono"];
    $ab = traerPorID_Abono($idEditarAbono);
    $id_abono = $ab->id_abono;
    $fecha_abono = $ab->fecha_abono;
    $profesor_abono = $ab->profesor;
    $monto_abono = $ab->monto_abono;
} else {
    $idEditarAbono = "";
    $fecha_abono = "";
    $profesor_abono = "";
    $monto_abono = "";
}

// Guardar o Actualizar Abono
if (isset($_REQUEST['guardarAbono'])) {
    $id_abono = $_REQUEST['id_abono'];
    $fecha_abono = $_REQUEST['fecha_abono'];
    $profesor_abono = $_REQUEST['profesor'];
    $monto_abono = $_REQUEST['monto_abono'];

    if ($profesor_abono != "" && $monto_abono != "") {
        if ($id_abono == "") {
            insertarAbono($fecha_abono, $profesor_abono, $monto_abono);
        } else {
            actualizarAbono($id_abono, $fecha_abono, $profesor_abono, $monto_abono);
        }
    }
    header("Location: profesores.php");
    exit();
}

// Obtener todos los abonos
$resulAbonos = traerAbonos();
?>

<div class="container mt-3">

    <!-- Buscador y Botón para desplegar el formulario -->
    <div class="row g-2 mb-3 align-items-center">
        <div class="col-md-8">
            <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar profesor por nombre o apellido...">
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success btn-sm w-100 w-md-auto" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFormulario" aria-expanded="<?= $idEditar != '' ? 'true' : 'false' ?>" aria-controls="collapseFormulario">
                <i class="bi bi-plus-circle-fill"></i> <?= $idEditar == "" ? "Nuevo Profesor" : "Editar Profesor" ?>
            </button>
        </div>
    </div>

    <!-- Contenedor Colapsable del Formulario -->
    <div class="collapse <?= $idEditar != '' ? 'show' : '' ?> mb-4" id="collapseFormulario">
        <div class="card shadow">
            <?php $operacion = $idEditar == "" ? "Agregar Nuevo Profesor" : "Actualizar Profesor ID: " . $idEditar ?>
            <div class="card-header <?= $idEditar == "" ? 'bg-success' : 'bg-warning text-dark' ?> text-white py-2">
                <i class="bi <?= $idEditar == "" ? 'bi-plus-circle-fill' : 'bi-pencil-square' ?>"></i> <?= $operacion ?>
            </div>
            <div class="card-body">
                <form method="POST" action="profesores.php">
                    <input type="hidden" name="id_profesor" value="<?= $idEditar ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label mb-1 small">Nombre *</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" value="<?= htmlspecialchars($nombre) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label mb-1 small">Apellido *</label>
                            <input type="text" name="apellido" class="form-control form-control-sm" value="<?= htmlspecialchars($apellido) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Cédula de Identidad *</label>
                            <input type="text" name="ci" class="form-control form-control-sm" value="<?= htmlspecialchars($ci) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Fecha de Ingreso *</label>
                            <input type="date" name="anio_ingreso" class="form-control form-control-sm" value="<?= htmlspecialchars($anio_ingreso) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Salario Base (Gs) *</label>
                            <input type="number" step="0.01" name="salario_base" class="form-control form-control-sm" value="<?= htmlspecialchars($salario_base) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Activo</label>
                            <select name="activo" class="form-select form-select-sm">  
                                <option value="1" <?= $activo == "1" || $activo === "" ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= $activo == "0" && $activo !== "" ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        
                        <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                            <?php if ($idEditar != ""): ?>
                                <a href="profesores.php" class="btn btn-secondary btn-sm">Cancelar Edición</a>
                            <?php endif; ?>
                            <button type="submit" name="guardar" class="btn <?= $idEditar == "" ? 'btn-success' : 'btn-warning' ?> btn-sm">
                                <i class="bi bi-save-fill"></i> <?= $idEditar == "" ? 'Guardar Profesor' : 'Actualizar Datos' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla de Profesores -->
    <div class="card shadow mb-4">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-people-fill"></i> Profesores Registrados
        </div>
        <div class="card-body p-2">
            <?php if (mysqli_num_rows($resul) == 0): ?>
                <div class="alert alert-warning mb-0">No hay profesores registrados en el sistema.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="tablaProfesores">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>CI</th>
                                <th>Fecha Ingreso</th>
                                <th>Salario Base Mensual</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resul)): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $fila["id_profesor"] ?></td>
                                    <td class="nombre-profesor align-middle"><?= htmlspecialchars($fila["nombre"] . ' ' . $fila["apellido"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["ci"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["anio_ingreso"]) ?></td>
                                    <td class="text-end align-middle">Gs <?= number_format($fila["salario_base"], 0, ',', '.') ?></td>
                                    <td class="text-center align-middle">
                                        <?= $fila['activo'] == 1 ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<i class="bi bi-x-circle-fill text-danger fs-5"></i>' ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="profesores.php?idEditar=<?= $fila['id_profesor'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>
                                        <a href="profesores.php?idBorrar=<?= $fila['id_profesor'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar a este profesor?')">
                                            <i class="bi bi-trash-fill"></i> <span class="d-none d-sm-inline">Borrar</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- SECCIÓN NUEVA: MÓDULO DE ABONOS DE PROFESORES          -->
    <!-- ======================================================= -->
    <!-- Botón para Abrir/Desplegar Formulario de Abonos -->
    <div class="row g-2 mb-4 align-items-center">
        <div class="col-md-8">
            <h5 class="mb-0 text-secondary"><i class="bi bi-cash-stack"></i> Gestión de Abonos</h5>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success btn-sm w-100 w-md-auto" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFormAbono" aria-expanded="<?= $idEditarAbono != '' ? 'true' : 'false' ?>" aria-controls="collapseFormAbono">
                <i class="bi bi-plus-circle-fill"></i> <?= $idEditarAbono == "" ? "Nuevo Abono" : "Editar Abono" ?>
            </button>
        </div>
    </div>

    <!-- Formulario Colapsable de Abonos -->
    <div class="collapse <?= $idEditarAbono != '' ? 'show' : '' ?> mb-4" id="collapseFormAbono">
        <div class="card shadow">
            <?php $opAbono = $idEditarAbono == "" ? "Registrar Nuevo Abono" : "Actualizar Abono ID: " . $idEditarAbono ?>
            <div class="card-header <?= $idEditarAbono == "" ? 'bg-success text-white' : 'bg-warning text-dark' ?> py-2">
                <i class="bi <?= $idEditarAbono == "" ? 'bi-plus-circle-fill' : 'bi-pencil-square' ?>"></i> <?= $opAbono ?>
            </div>
            <div class="card-body">
                <form method="POST" action="profesores.php">
                    <input type="hidden" name="id_abono" value="<?= $idEditarAbono ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Profesor *</label>
                            <input type="text" name="profesor" class="form-control form-control-sm" value="<?= htmlspecialchars($profesor_abono) ?>" placeholder="Nombre del profesor..." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Fecha del Abono *</label>
                            <input type="date" name="fecha_abono" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_abono) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1 small">Monto Abono (Gs) *</label>
                            <input type="number" name="monto_abono" class="form-control form-control-sm" value="<?= htmlspecialchars($monto_abono) ?>" required>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                            <?php if ($idEditarAbono != ""): ?>
                                <a href="profesores.php" class="btn btn-secondary btn-sm">Cancelar Edición</a>
                            <?php endif; ?>
                            <button type="submit" name="guardarAbono" class="btn <?= $idEditarAbono == "" ? 'btn-success' : 'btn-warning' ?> btn-sm">
                                <i class="bi bi-save-fill"></i> <?= $idEditarAbono == "" ? 'Guardar Abono' : 'Actualizar Abono' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla de Abonos -->
    <div class="card shadow mb-4">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-receipt"></i> Registro de Abonos Realizados
        </div>
        <div class="card-body p-2">
            <?php if (mysqli_num_rows($resulAbonos) == 0): ?>
                <div class="alert alert-info mb-0">No hay abonos registrados en la base de datos.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="tablaAbonos">
                        <thead class="text-center">
                            <tr>
                                <th>ID Abono</th>
                                <th>Fecha Abono</th>
                                <th>Profesor</th>
                                <th>Monto Abono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($abono = mysqli_fetch_assoc($resulAbonos)): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $abono["id_abono"] ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($abono["fecha_abono"]) ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($abono["profesor"]) ?></td>
                                    <td class="text-end align-middle">Gs <?= number_format($abono["monto_abono"], 0, ',', '.') ?></td>
                                    <td class="text-center align-middle">
                                        <a href="profesores.php?idEditarAbono=<?= $abono['id_abono'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>
                                        <a href="profesores.php?idBorrarAbono=<?= $abono['id_abono'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este abono?')">
                                            <i class="bi bi-trash-fill"></i> <span class="d-none d-sm-inline">Borrar</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    // ========== FILTRO DE BÚSQUEDA ==========
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaProfesores');

        if (buscador && tabla) {
            buscador.addEventListener('keyup', function() {
                const filtro = this.value.toLowerCase();
                const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                for (let fila of filas) {
                    const nombreCompleto = fila.cells[1].textContent.toLowerCase();
                    fila.style.display = nombreCompleto.includes(filtro) ? '' : 'none';
                }
            });
        }
    });
</script>

<?php
include '../includes/footer.php';
ob_end_flush();
?>