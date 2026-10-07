<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sysweb">
    <meta name="author" content="Aldo Torres">

    <link rel="shortcut icon" href="../../images/favicon.ico" />
    <title>Sysweb - Recuperar</title>

    <!-- CoreUI CSS -->
    <link href="../../dist/css/coreui.min.css" rel="stylesheet">
    <link href="../../dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@coreui/icons/css/all.min.css">

</head>

<body class="app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <img src="../../images/favicon.ico" alt="Sysweb" height="50">
                    <h1 style="color: #3c8dbc;">Recuperar contraseña</h1>
                </div>

                <!-- Alerts -->
                <?php
                $m = htmlspecialchars($_GET['m'] ?? '', ENT_QUOTES, 'UTF-8');
                $alertas = [
                    1 => ['success', 'cil-check-circle', "Se envió un enlace de recuperación al correo asociado a tu usuario ($m). Vence en 30 minutos."],
                    2 => ['warning', 'cil-warning', 'Ingresa un nombre de usuario válido.'],
                    3 => ['danger', 'cil-x-circle', 'No se pudo enviar el correo. Inténtalo más tarde o contacta al administrador.'],
                    4 => ['danger', 'cil-x-circle', 'El usuario ingresado no está registrado.'],
                    5 => ['warning', 'cil-warning', 'Este usuario no tiene un correo asociado. Contacta al administrador para recuperar tu acceso.'],
                ];
                $a = $alertas[(int) ($_GET['alert'] ?? 0)] ?? null;
                if ($a) {
                    echo "<div class='alert alert-{$a[0]}' role='alert' aria-live='polite'><i class='{$a[1]}'></i> {$a[2]}</div>";
                }
                ?>

                <!-- Formulario -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">
                            <i class="cil-user"></i> Ingrese su usuario
                        </h5>
                        <form action="proses.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Usuario"
                                    maxlength="150" autocomplete="username" autofocus required>
                                <div class="form-text">Enviaremos el enlace al correo registrado en tu cuenta.</div>
                            </div>
                            <input type="submit" class="btn btn-primary" name="recuperar" value="Enviar"> <a class="ms-2" href="../../index.php">Volver</a>
                            <hr>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CoreUI JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.2.0/dist/js/coreui.min.js"
        integrity="sha384-c4nHOtHRPhkHqJsqK5SH1UkyoL2HUUhzGfzGkchJjwIrAlaYVBv+yeU8EYYxW6h5"
        crossorigin="anonymous"></script>
</body>

</html>
