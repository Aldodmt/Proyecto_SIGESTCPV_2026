<?php
require_once "../../config/database.php";
require_once "../../config/auth.php";

$token = $_GET['token'] ?? '';
$tokenValido = auth_token_valido($mysqli, $token) !== null;
$alert = (int) ($_GET['alert'] ?? 0);
?><!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sysweb">
    <meta name="author" content="Aldo Torres">

    <link rel="shortcut icon" href="../../images/favicon.ico" />
    <title>Sysweb - Nueva contraseña</title>

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
                    <h1 style="color: #3c8dbc;">Nueva contraseña</h1>
                </div>

                <?php if ($alert === 1): ?>
                    <div class="alert alert-warning" role="alert"><i class="cil-warning"></i> Las contraseñas deben coincidir.</div>
                <?php elseif ($alert === 2): ?>
                    <div class="alert alert-warning" role="alert"><i class="cil-warning"></i> La contraseña no cumple los requisitos de seguridad.</div>
                <?php elseif ($alert === 3): ?>
                    <div class="alert alert-danger" role="alert"><i class="cil-x-circle"></i> El enlace no es válido, ya fue usado o venció.</div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3"><i class="cil-lock-locked"></i> Restablecer contraseña</h5>
                        <?php if ($tokenValido): ?>
                            <form action="proceso.php" method="POST" id="formNueva" novalidate>
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <div class="mb-3">
                                    <label for="nuevo" class="form-label">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="nuevo" name="nuevo"
                                        placeholder="Nueva contraseña" maxlength="128" autocomplete="new-password"
                                        required>
                                    <ul class="list-unstyled small mt-2 mb-0" id="requisitos">
                                        <li data-regla="len"><i class="cil-circle"></i> Mínimo <?= AUTH_MIN_PASSWORD ?> caracteres</li>
                                        <li data-regla="min"><i class="cil-circle"></i> Una letra minúscula</li>
                                        <li data-regla="may"><i class="cil-circle"></i> Una letra mayúscula</li>
                                        <li data-regla="num"><i class="cil-circle"></i> Un número</li>
                                        <li data-regla="sim"><i class="cil-circle"></i> Un símbolo (! @ # $ %)</li>
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <label for="repetir" class="form-label">Repetir contraseña</label>
                                    <input type="password" class="form-control" id="repetir" name="repetir"
                                        placeholder="Repetir contraseña" maxlength="128" autocomplete="new-password"
                                        required>
                                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100" name="Guardar" id="btnGuardar"
                                    disabled>Guardar</button>
                            </form>
                        <?php else: ?>
                            <p class="text-danger">El enlace de recuperación no es válido, ya fue usado o venció.</p>
                            <a href="recuperar.php" class="btn btn-primary w-100">Solicitar un nuevo enlace</a>
                        <?php endif; ?>
                        <hr>
                        <a href="../../index.php">Volver al inicio de sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../dist/js/coreui.min.js"></script>
    <?php if ($tokenValido): ?>
        <script>
            (function () {
                const nuevo = document.getElementById('nuevo');
                const repetir = document.getElementById('repetir');
                const btn = document.getElementById('btnGuardar');
                const reglas = {
                    len: p => p.length >= <?= AUTH_MIN_PASSWORD ?>,
                    min: p => /[a-z]/.test(p),
                    may: p => /[A-Z]/.test(p),
                    num: p => /\d/.test(p),
                    sim: p => /[^a-zA-Z0-9]/.test(p)
                };

                function revisar() {
                    let todas = true;
                    document.querySelectorAll('#requisitos li').forEach(li => {
                        const ok = reglas[li.dataset.regla](nuevo.value);
                        todas = todas && ok;
                        li.className = ok ? 'text-success' : 'text-muted';
                        li.querySelector('i').className = ok ? 'cil-check-circle' : 'cil-circle';
                    });
                    const coincide = repetir.value !== '' && nuevo.value === repetir.value;
                    repetir.classList.toggle('is-invalid', repetir.value !== '' && !coincide);
                    repetir.classList.toggle('is-valid', coincide);
                    btn.disabled = !(todas && coincide);
                }
                nuevo.addEventListener('input', revisar);
                repetir.addEventListener('input', revisar);
            })();
        </script>
    <?php endif; ?>
</body>

</html>
