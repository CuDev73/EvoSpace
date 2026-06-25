<?php 
ob_start(); // Inicia el buffer de salida para evitar errores con header()

//session_start();
/*if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}*/
include "./funciones.php";
include '../includes/header.php';
include '../includes/navbar.php';

if (isset($_REQUEST['idBorrar'])) {
    borrar("alumnos", $_REQUEST['idBorrar']);
    header("Location: alumnos.php");
    exit();
}

if (isset($_REQUEST["idEditar"])) {
    $idEditar = $_REQUEST["idEditar"];
    $p = traerPorID("alumnos", $idEditar);
    $id_alumno = $p->id_alumno;
    $nombre = $p->nombre;
    $apellido = $p->apellido;
    $id_curso = $p->id_curso;
    $anio_ingreso = $p->anio_ingreso;
    $horas_profesionales = $p->horas_profesionales;
    $ci = $p->ci;
    $telefono = $p->telefono;
    $id_padre = $p->id_padre;
    $becado = $p->becado;
    $activo = $p->activo;
    $fecha_creacion = $p->fecha_creacion;
} else {
    $idEditar = "";
    $nombre = "";
    $apellido = "";
    $id_curso = "";
    $anio_ingreso = "";
    $horas_profesionales = "";
    $ci = "";
    $telefono = "";
    $id_padre = "";
    $becado = "";
    $activo = "";
    $fecha_creacion = date("Y-m-d H:i:s"); // Sugiere la fecha/hora actual por defecto al agregar
}

if (isset($_REQUEST['guardar'])) {
    $id_alumno = $_REQUEST['id_alumno'];
    $nombre = $_REQUEST['nombre'];
    $apellido = $_REQUEST['apellido'];
    $id_curso = $_REQUEST['id_curso'];
    $anio_ingreso = $_REQUEST['anio_ingreso'];
    $horas_profesionales = $_REQUEST['horas_profesionales'];
    $ci = $_REQUEST['ci'];
    $telefono = $_REQUEST['telefono'];
    $id_padre = $_REQUEST['id_padre'];
    $becado = $_REQUEST['becado'];
    $activo = $_REQUEST['activo'];
    $fecha_creacion = $_REQUEST['fecha_creacion'];

    if ($nombre != "") {
        if ($id_alumno == "") {
            insertarAlumno($nombre, $apellido, $id_curso, $anio_ingreso, $horas_profesionales, $ci, $telefono, $id_padre, $becado, $activo, $fecha_creacion);
        } else {
            actualizarAlumno($id_alumno, $nombre, $apellido, $id_curso, $anio_ingreso, $horas_profesionales, $ci, $telefono, $id_padre, $becado, $activo, $fecha_creacion);
        }
    }
    
    header("Location: alumnos.php");
    exit();
}

$resul = traer("alumnos");
?>

<div class="container mt-3">

    <div class="row g-2 mb-3">
        <div class="col-md-12">
            <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar alumno por nombre o apellido...">
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-people-fill"></i> Alumnos Registrados
        </div>
        <div class="card-body p-2">
            <?php if (mysqli_num_rows($resul) == 0): ?>
                <div class="alert alert-warning mb-0">No hay alumnos registrados en el sistema.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="tablaAlumnos">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Curso (ID)</th>
                                <th>Año Ingreso</th>
                                <th>Horas Prof.</th>
                                <th>CI</th>
                                <th>Teléfono</th>
                                <th>Padre (ID)</th>
                                <th>Becado</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resul)): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $fila["id_alumno"] ?></td>
                                    <td class="nombre-alumno align-middle"><?= htmlspecialchars($fila["nombre"] . ' ' . $fila["apellido"]) ?></td>
                                    <td class="text-center align-middle"><span class="badge bg-secondary"><?= htmlspecialchars($fila["id_curso"]) ?></span></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["anio_ingreso"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["horas_profesionales"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["ci"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["telefono"]) ?></td>
                                    <td class="text-center align-middle"><?= htmlspecialchars($fila["id_padre"]) ?></td>
                                    <td class="text-center align-middle">
                                        <?= $fila['becado'] == 1 ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<i class="bi bi-x-circle-fill text-danger fs-5"></i>' ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?= $fila['activo'] == 1 ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<i class="bi bi-x-circle-fill text-danger fs-5"></i>' ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="alumnos.php?idEditar=<?= $fila['id_alumno'] ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="alumnos.php?idBorrar=<?= $fila['id_alumno'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar a este alumno?')">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-3">
        <?php $operacion = $idEditar == "" ? "Agregar Nuevo Alumno" : "Actualizar Alumno ID: " . $idEditar ?>
        <div class="card-header <?= $idEditar == "" ? 'bg-success' : 'bg-warning text-dark' ?> text-white py-2">
            <i class="bi <?= $idEditar == "" ? 'bi-plus-circle-fill' : 'bi-pencil-square' ?>"></i> <?= $operacion ?>
        </div>
        <div class="card-body">
            <form method="POST" action="alumnos.php">
                <input type="hidden" name="id_alumno" value="<?= $idEditar ?>">
                <input type="hidden" name="fecha_creacion" value="<?= htmlspecialchars($fecha_creacion) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1 small">Nombre *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" value="<?= htmlspecialchars($nombre) ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label mb-1 small">Apellido *</label>
                        <input type="text" name="apellido" class="form-control form-control-sm" value="<?= htmlspecialchars($apellido) ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1 small">ID Curso *</label>
                        <input type="number" name="id_curso" class="form-control form-control-sm" value="<?= htmlspecialchars($id_curso) ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1 small">Año de Ingreso *</label>
                        <input type="text" name="anio_ingreso" class="form-control form-control-sm" value="<?= htmlspecialchars($anio_ingreso) ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1 small">Horas Profesionales</label>
                        <input type="number" name="horas_profesionales" class="form-control form-control-sm" value="<?= htmlspecialchars($horas_profesionales) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1 small">CI / Cédula *</label>
                        <input type="text" name="ci" class="form-control form-control-sm" value="<?= htmlspecialchars($ci) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1 small">Teléfono</label>
                        <input type="text" name="telefono" class="form-control form-control-sm" value="<?= htmlspecialchars($telefono) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1 small">ID Padre / Tutor</label>
                        <input type="number" name="id_padre" class="form-control form-control-sm" value="<?= htmlspecialchars($id_padre) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1 small">¿Becado?</label>
                        <select name="becado" class="form-select form-select-sm">
                            <option value="0" <?= $becado == "0" || $becado === "" ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= $becado == "1" ? 'selected' : '' ?>>Sí</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1 small">Estado</label>
                        <select name="activo" class="form-select form-select-sm">
                            <option value="1" <?= $activo == "1" || $activo === "" ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $activo == "0" && $activo !== "" ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                        <?php if ($idEditar != ""): ?>
                            <a href="alumnos.php" class="btn btn-secondary btn-sm">Cancelar Edición</a>
                        <?php endif; ?>
                        <button type="submit" name="guardar" class="btn <?= $idEditar == "" ? 'btn-success' : 'btn-sm' ?> btn-sm">
                            <i class="bi bi-save-fill"></i> <?= $idEditar == "" ? 'Guardar Alumno' : 'Actualizar Datos' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ========== FILTRO DE BÚSQUEDA EN TIEMPO REAL ==========
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaAlumnos');

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