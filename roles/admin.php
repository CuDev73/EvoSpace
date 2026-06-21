<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolucionarte</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./evo.ico">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap');

        * {
            font-family: "Montserrat", sans-serif;
        }

        body {
            background-color: #d8d5c9;
        }

        .btn-evo {
            background-color: #c81015;
            color: white;
            height: 80px;
            border: none;
            border-radius: 15px;
            width: 100%;
            font-size: 1.2rem;
        }

        .btn-evo:hover {
            background-color: #a30d11;
            color: white;
        }

        .evento-header {
            background-color: #c81015;
            color: white;
        }
    </style>
</head>

<body>

    <!-- NAVBAR ORIGINAL -->
    <?php include '../includes/navbar.php' ?>

    <!-- CONTENIDO -->
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

            <div class="col-12 col-md-6">
                <button class="btn-evo">
                    Nueva inscripción
                </button>
            </div>

            <div class="col-12 col-md-6">
                <button class="btn-evo">
                    Nuevo pago
                </button>
            </div>

            <div class="col-12 col-md-6">
                <button class="btn-evo">
                    Nuevo evento
                </button>
            </div>

            <div class="col-12 col-md-6">
                <button class="btn-evo">
                    Ver alumnos activos
                </button>
            </div>

        </div>

        <!-- EVENTO -->
        <div class="card shadow mb-4">

            <div class="card-header evento-header fs-4">
                PRÓXIMO EVENTO: Sesión de fotos para la obra
            </div>

            <div class="card-body">

                <p>
                    Locación: Tal parte Avda. Lalaland c/12 de junio
                </p>

                <p>
                    Llevar polleras, sombreros y utilería.
                </p>

                <div class="text-end fw-bold text-danger">
                    Fecha: 13 de agosto del 2026
                </div>

            </div>

        </div>

        <!-- EVENTO -->
        <div class="card shadow">

            <div class="card-header evento-header fs-4">
                PRÓXIMO EVENTO: Grabación de Mentiras
            </div>

            <div class="card-body">

                <p>
                    Locación: Teatro Municipal.
                </p>

                <p>
                    Ensayo general a las 18:00 hs.
                </p>

                <div class="text-end fw-bold text-danger">
                    Fecha: 20 de agosto del 2026
                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>