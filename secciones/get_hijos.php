<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    exit('No autorizado');
}
require_once '../config/db.php';
if (!isset($_GET['id_padre'])) {
    exit('ID no válido');
}
$id_padre = (int)$_GET['id_padre'];
$stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE id_padre = ?");
$stmt->execute([$id_padre]);
$hijos = $stmt->fetchAll(PDO::FETCH_COLUMN);
header('Content-Type: application/json');
echo json_encode($hijos);
?>