<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- CSS primero -->
    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/compras.css">
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- JS (en el orden correcto) -->
    <script src="../functions/functions.js"></script>
    <!-- jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

    <!-- Toastr -->
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand navbar-dark bg-dark">
        <div class="nav navbar-nav">
            <img src="../img/M&CMaquinados.jpg  " alt="" class="logo">
            <a class="nav-item nav-link" href="">Almacen</a>
            <a class="nav-item nav-link" href="productos.php">Productos</a>
            <a class="nav-item nav-link" href="proveedores.php">Proveedores</a>
            <a class="nav-item nav-link" href="solicitar_material.php">Solicitar Material</a>
            <a class="nav-item nav-link" href="gestionar_solicitudes.php">Solicitudes De Material</a>
            <a class="nav-item nav-link" href="surtido.php">Ingresar Material</a>
            <div class="dropdown d-inline me-2" style="background-color: aqua;">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="logout.php">Cerrar Sesion</a></li>
                </ul>
            </div>
        </div>
    </nav>