<?php
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">

    <!-- TÍTULO -->
    <div class="bg-danger text-white p-4 rounded mb-4">

        <h1 class="display-5 fw-bold">
            EvoSpace
        </h1>

        <p class="mb-0">
            Curso Superior > Curso Infantil > Acrotelas
        </p>

    </div>

    <!-- BOTONES -->
    <div class="row g-3 mb-4">

        <button
            class="btn btn-danger"
            data-bs-toggle="modal"
            data-bs-target="#modalPrueba">

            Nueva Clase
        </button>

        <div class="modal fade" id="modalPrueba" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Modal de prueba
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        Hola mundo.

                    </div>

                </div>

            </div>

        </div>

    </div>
    <?php
    include '../includes/footer.php';
    ?>