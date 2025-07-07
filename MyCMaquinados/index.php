<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M&C Maquinados</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .hero {
            background: url('img/maquinados.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero h1 {
            background-color: rgb(0, 0, 0);
            padding: 20px;
            border-radius: 10px;
            color: white;
        }
    </style>
</head>

<body>
    <!-- NAVBAR ORIGINAL -->
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">M&C Maquinados</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="./registro.php">Registrate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./login.php">Inicia Sesion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <h1>Bienvenido a M&C Maquinados</h1>
    </section>

    <!-- SERVICIOS -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Nuestros Servicios</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Fabricación de Piezas</h5>
                            <p class="card-text">Piezas de precisión con los más altos estándares de calidad.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Diseño CAD/CAM</h5>
                            <p class="card-text">Diseñamos piezas a medida según tus necesidades técnicas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reparación y Mantenimiento</h5>
                            <p class="card-text">Reparamos y mantenemos maquinaria industrial y piezas clave.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SOBRE NOSOTROS -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Sobre Nosotros</h2>
            <p class="text-center">En M&C Maquinados somos especialistas en soluciones industriales a medida. Contamos con años de experiencia fabricando, diseñando y manteniendo piezas y maquinaria de precisión para distintas industrias.</p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-primary text-white text-center py-3">
        &copy; 2025 M&C Maquinados. Todos los derechos reservados.
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>
