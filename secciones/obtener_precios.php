<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit('No autorizado');
}

require_once '../config/db.php';

if (!isset($_GET['id_curso'])) {
    http_response_code(400);
    exit('Falta id_curso');
}

$id_curso = (int)$_GET['id_curso'];

$sql = "SELECT concepto, precio FROM precios WHERE id_curso = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_curso]);
$precios = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($precios);
?>