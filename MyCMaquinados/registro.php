<?php

require_once './bd/db_conexion.php';
session_start();

// Validar si el formulario fue enviado
if (isset($_POST['registrar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Sanitizar el email
    $password = trim($_POST['password']); // Eliminar espacios en blanco al inicio y final
    $rol = htmlspecialchars(trim($_POST['rol']), ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales

    // Validar que los campos no estén vacíos
    if (empty($name) || empty($email) || empty($password) || empty($rol)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: registro.php");
        exit();
    }

    // Validar que el email sea válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El email ingresado no es válido.'
        ];
        header("Location: registro.php");
        exit();
    }

    // Validar la longitud de la contraseña
    if (strlen($password) < 8) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'La contraseña debe tener al menos 8 caracteres.'
        ];
        header("Location: registro.php");
        exit();
    }

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);



    try {
        // Verificar si el email ya está registrado
        $sql_check = "SELECT id_usuario FROM users WHERE email = ?";
        $query_check = $cnnPDO->prepare($sql_check);
        $query_check->execute([$email]);

        if ($query_check->rowCount() > 0) {
            $_SESSION['toastr'] = [
                'type' => 'error',
                'message' => 'El email ya está registrado. Intente con otro.'
            ];
            header("Location: registro.php");
            exit();
        }

        // Insertar el nuevo usuario en la base de datos
        $sql = "INSERT INTO users (name, email, password, rol) VALUES (?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$name, $email, $hashed_password, $rol]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Ha sido registrado correctamente.'
        ];
    } catch (PDOException $e) {
        // Registrar el error en un archivo de log para evitar exponer detalles al usuario
        error_log($e->getMessage(), 3, 'errors.log');

        // Mensaje genérico para el usuario
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error en el registro. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: registro.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- CSS primero -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- JavaScript después, en ORDEN CORRECTO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

</head>

<body>
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
                        <a class="nav-link" href="registro.php">Registrate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Inicia Sesion</a>
                    </li>
            </div>
        </div>
    </nav>

    <div class="col-md-4" style="margin: 0 auto; margin-top: 50px;">
        <div>
            <form method="post">
                <h4 class="form-label mt-4">Registrate</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name="name" placeholder="name@example.com">
                    <label for="name"><i class="fa-regular fa-user"></i> Nombre</label>

                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" placeholder="name@example.com">
                    <label for="email"><i class="fa-regular fa-envelope"></i> Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" placeholder="Password" autocomplete="off">
                    <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                </div>
                <div class="form-floating mt-3">
                    <select class="form-select" id="rol" name="rol">
                        <option value="tecnico">Técnico</option>
                        <option value="almacen">Almacén</option>
                        <option value="compras">Compras</option>
                    </select>
                    <label for="rol"><i class="fa-solid fa-user-gear"></i> Rol</label>
                </div>

                <button type="submit" class="btn btn-primary mt-3" name="registrar">Registrar</button>
            </form>

        </div>
    </div>

    <!-- Script para mostrar las alertas de Toastr -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

    <script>
        // Configuración global de Toastr
        toastr.options = {
            closeButton: true, // Agrega el botón de cerrar
            progressBar: true, // Muestra una barra de progreso
            timeOut: 3000, // Tiempo antes de que desaparezca (3s)
            extendedTimeOut: 2000,
            positionClass: "toast-top-right"
        };

        $(document).ready(function() {
            <?php if (isset($_SESSION['toastr'])): ?>
                toastr.<?= $_SESSION['toastr']['type'] ?>("<?= addslashes($_SESSION['toastr']['message']) ?>");
                <?php unset($_SESSION['toastr']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>