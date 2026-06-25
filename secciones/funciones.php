<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

// Creamos la conexión una sola vez para todo el script
$db = mysqli_connect("localhost", "root", "", "evospace");

if (!$db) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

function traer($tabla){
    global $db;
    $str = "SELECT * FROM $tabla";
    $resul = mysqli_query($db, $str);
    return $resul;
}

function traerPorID($tabla, $id_alumno){
    global $db;
    $str = "SELECT * FROM $tabla WHERE id_alumno = $id_alumno";
    $resul = mysqli_query($db, $str) or die("Error en traerPorID: " . mysqli_error($db));
    return mysqli_fetch_object($resul);
}

function borrar($tabla, $id_alumno){
    global $db;
    $str = "DELETE FROM $tabla WHERE id_alumno = $id_alumno";
    mysqli_query($db, $str) or die("Error en borrar Alumno: " . mysqli_error($db));
}

function insertarAlumno($nombre,$apellido,$id_curso,$anio_ingreso, $horas_profesionales, $ci, $telefono, $id_padre, $becado, $activo, $fecha_creacion){
    global $db;
    $str = "INSERT INTO alumnos(nombre, apellido, id_curso, anio_ingreso, horas_profesionales, ci, telefono, 
    id_padre, becado, activo, fecha_creacion) VALUES 
    ('$nombre','$apellido','$id_curso','$anio_ingreso', '$horas_profesionales', 
    '$ci', '$telefono', '$id_padre', '$becado', '$activo', '$fecha_creacion')";
    mysqli_query($db, $str) or die("Error en insertarAlumno: " . mysqli_error($db));
}

function actualizarAlumno($id_alumno,$nombre,$apellido,$id_curso,$anio_ingreso, $horas_profesionales, $ci, $telefono, $id_padre, $becado, $activo, $fecha_creacion){
    global $db;
    $str = "UPDATE alumnos SET nombre='$nombre',apellido='$apellido',
    id_curso='$id_curso',anio_ingreso='$anio_ingreso',horas_profesionales='$horas_profesionales',
    ci='$ci',telefono='$telefono',id_padre='$id_padre',becado='$becado',activo='$activo',
    fecha_creacion='$fecha_creacion' WHERE id_alumno=$id_alumno ";
    mysqli_query($db, $str) or die("Error en actualizarAlumno: " . mysqli_error($db));
}

// ====== FUNCIONES DE PROFESORES ======

function traerPorID_PROFESORES($tabla, $id_profesor){
    global $db;
    $str = "SELECT * FROM $tabla WHERE id_profesor = $id_profesor";
    $resul = mysqli_query($db, $str) or die("Error en traerPorID_PROFESORES: " . mysqli_error($db));
    return mysqli_fetch_object($resul);
}

function borrarProfesor($tabla, $id_profesor){
    global $db;
    $str = "DELETE FROM $tabla WHERE id_profesor = $id_profesor";
    mysqli_query($db, $str) or die("Error en borrarProfesor: " . mysqli_error($db));
}

function insertarProfesor($nombre,$apellido,$ci,$anio_ingreso,$salario_base,$activo){
    global $db;
    $str = "INSERT INTO profesores(nombre, apellido, ci, anio_ingreso, salario_base, activo) 
    VALUES ('$nombre','$apellido','$ci','$anio_ingreso', '$salario_base','$activo')";
    mysqli_query($db, $str) or die("Error en insertarProfesor: " . mysqli_error($db));
}

function actualizarProfesor($id_profesor,$nombre,$apellido,$ci,$anio_ingreso, $salario_base, $activo){
    global $db;
    $str = "UPDATE profesores SET nombre='$nombre', apellido='$apellido',
    ci='$ci', anio_ingreso='$anio_ingreso', salario_base='$salario_base', activo='$activo' 
    WHERE id_profesor=$id_profesor";
    mysqli_query($db, $str) or die("Error en actualizarProfesor: " . mysqli_error($db));
}
?>