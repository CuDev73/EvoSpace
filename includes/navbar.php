<nav class="navbar navbar-dark bg-danger fixed-top">
    <div class="container-fluid position-relative">
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand fw-bold position-absolute start-50 translate-middle-x" href="/evospace/roles/admin.php">
            Evolucionarte
        </a>

        <span class="navbar-text text-white d-none d-md-inline ms-auto">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario'] ?? 'Admin') ?>
        </span>

        <div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="offcanvasNavbar">
            <div class="offcanvas-header bg-danger text-white d-flex align-items-center" style="min-height: 56px; padding: 0.5rem 1rem;">
                <h5 class="offcanvas-title mb-0 fs-6">Secciones</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav flex-grow-1">
                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    $secciones = [
                        'Inicio' => ['url' => '/evospace/roles/admin.php', 'icon' => 'bi-house-door-fill'],
                        'Registro Asistencia' => ['url' => '/evospace/secciones/registroAsistencia.php', 'icon' => 'bi-calendar-check-fill'],
                        'Alumnos' => ['url' => '/evospace/secciones/alumnos.php', 'icon' => 'bi-person-fill'],
                        'Pagos' => ['url' => '/evospace/secciones/pagos.php', 'icon' => 'bi-cash-coin'],
                        'Cantina' => ['url' => '/evospace/secciones/cantina.php', 'icon' => 'bi-cup-straw'],
                        'Profesores' => ['url' => '/evospace/secciones/profesores.php', 'icon' => 'bi-person-badge-fill'],
                        'Eventos' => ['url' => '/evospace/secciones/eventos.php', 'icon' => 'bi-calendar-event-fill'],
                        'Configuración' => ['url' => '/evospace/secciones/configuracion/configuracion.php', 'icon' => 'bi-gear-fill'],
                        'Usuarios' => ['url' => '/evospace/secciones/usuarios.php', 'icon' => 'bi-people-fill'],
                    ];
                    foreach ($secciones as $nombre => $datos):
                        $esActivo = (basename($datos['url']) == $currentPage) ? 'active' : '';
                    ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $esActivo ?>" href="<?= $datos['url'] ?>">
                                <i class="bi <?= $datos['icon'] ?> me-2"></i> <?= $nombre ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <a href="/evospace/index.php" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        font-family: 'Montserrat', sans-serif;
        min-height: 56px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .navbar-brand {
        font-size: 1.25rem;
        letter-spacing: 0.5px;
        color: #fff !important;
        text-decoration: none;
    }
    .navbar-brand:hover {
        color: #fff !important;
    }
    .navbar-toggler {
        padding: 0.4rem 0.6rem;
        font-size: 1.3rem;
        border: none;
        outline: none;
    }
    .navbar-toggler:focus {
        box-shadow: none;
    }
    .navbar-text {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .offcanvas-header {
        border-bottom: 2px solid #a30d11;
        padding: 0.5rem 1rem;
    }
    .offcanvas-body .nav-link {
        font-weight: 500;
        color: #333;
        padding: 12px 16px;
        border-radius: 8px;
        transition: background 0.2s, color 0.2s;
    }
    .offcanvas-body .nav-link:hover {
        background: #f8f9fa;
        color: #c81015;
    }
    .offcanvas-body .nav-link.active {
        background: #c81015;
        color: #fff !important;
    }
    .offcanvas-body .nav-link i {
        width: 28px;
        text-align: center;
        font-size: 1.2rem;
    }
    .offcanvas {
        max-width: 300px;
    }
    @media (max-width: 768px) {
        .navbar-text {
            display: none !important;
        }
        .navbar-brand {
            font-size: 1.1rem;
        }
    }
</style>