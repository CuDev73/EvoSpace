<?php
// db.php

$host = 'localhost';
$dbname = 'evospace';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}

function obtenerPorcentajeBeca($pdo) {
    $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'porcentaje_beca'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['valor'] : 45.45;
}
?>