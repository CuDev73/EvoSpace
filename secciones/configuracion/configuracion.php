<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /evospace/index.php');
    exit;
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-3">
    <div class="bg-danger text-white p-4 rounded mb-4">
        <h3 class="h3 fw-bold">EvoSpace</h3>
        <p class="mb-0">Panel de Configuración</p>
    </div>

    <div class="row g-4">
        <!-- Tarjeta: Configurar Pagos -->
        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-coin fs-1 text-danger"></i>
                    <h5 class="card-title mt-3">Pagos</h5>
                    <p class="card-text text-muted small">Editar precios por curso y conceptos</p>
                    <a href="configurar_pagos.php" class="btn btn-danger mt-2">
                        <i class="bi bi-arrow-right-circle"></i> Configurar
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Configurar Eventos (futuro) -->
        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-calendar-event fs-1 text-secondary"></i>
                    <h5 class="card-title mt-3">Eventos</h5>
                    <p class="card-text text-muted small">Gestionar tipos de eventos, colores, etc.</p>
                    <button class="btn btn-secondary mt-2" disabled>
                        <i class="bi bi-clock-history"></i> Próximamente
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Configurar Cursos (futuro) -->
        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-book fs-1 text-secondary"></i>
                    <h5 class="card-title mt-3">Cursos</h5>
                    <p class="card-text text-muted small">Añadir/eliminar cursos, cambiar nombres</p>
                    <button class="btn btn-secondary mt-2" disabled>
                        <i class="bi bi-clock-history"></i> Próximamente
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Configurar Usuarios (futuro) -->
        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-people fs-1 text-secondary"></i>
                    <h5 class="card-title mt-3">Usuarios</h5>
                    <p class="card-text text-muted small">Roles, permisos, etc.</p>
                    <button class="btn btn-secondary mt-2" disabled>
                        <i class="bi bi-clock-history"></i> Próximamente
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Configurar General (futuro) -->
        <div class="col-md-4">
            <div class="card shadow h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-gear fs-1 text-secondary"></i>
                    <h5 class="card-title mt-3">General</h5>
                    <p class="card-text text-muted small">Recargos, días límite, etc.</p>
                    <button class="btn btn-secondary mt-2" disabled>
                        <i class="bi bi-clock-history"></i> Próximamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>