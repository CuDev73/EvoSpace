<?php
// helpers/functions.php

function redirigirSegunRol($rolNombre) {
    $rutas = [
        'admin' => '/evospace/roles/admin.php',
        'profesor' => '/evospace/roles/profesor.php',
        'padre' => '/evospace/roles/padre.php',
        'auxiliar' => '/evospace/roles/auxiliar.php'
    ];
    header('Location: ' . ($rutas[$rolNombre] ?? '/evospace/index.php'));
    exit;
}

function obtenerPermisos($pdo, $id_rol) {
    $stmt = $pdo->prepare("
        SELECT p.nombre FROM permisos p
        JOIN rol_permiso rp ON p.id_permiso = rp.id_permiso
        WHERE rp.id_rol = ?
    ");
    $stmt->execute([$id_rol]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function tienePermiso($permiso) {
    return isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos']);
}

function verificarPermiso($permiso) {
    if (!tienePermiso($permiso)) {
        header('Location: /evospace/index.php');
        exit;
    }
}

function obtenerPorcentajeBeca($pdo) {
    $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'porcentaje_beca'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['valor'] : 45.45;
}

function obtenerDiaLimite($pdo) {
    $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'dia_limite_pago'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['valor'] : 10;
}

function obtenerRecargoPorDia($pdo) {
    $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'recargo_por_dia'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['valor'] : 1000;
}

function formatoMoneda($numero) {
    return number_format($numero, 0, ',', '.') . ' Gs';
}