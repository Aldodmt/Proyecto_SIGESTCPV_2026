<?php
$base = '../../';
$authTitle = 'Recuperar contraseña';

$m = htmlspecialchars($_GET['m'] ?? '', ENT_QUOTES, 'UTF-8');
$alertas = [
    1 => ['success', 'bi-check-circle', "Se envió un enlace de recuperación al correo asociado a tu usuario ($m). Vence en 30 minutos."],
    2 => ['warning', 'bi-exclamation-triangle', 'Ingresa un nombre de usuario válido.'],
    3 => ['danger', 'bi-x-circle', 'No se pudo enviar el correo. Inténtalo más tarde o contacta al administrador.'],
    4 => ['danger', 'bi-x-circle', 'El usuario ingresado no está registrado.'],
    5 => ['warning', 'bi-exclamation-triangle', 'Este usuario no tiene un correo asociado. Contacta al administrador para recuperar tu acceso.'],
];
$a = $alertas[(int) ($_GET['alert'] ?? 0)] ?? null;

require '../../layout/auth_head.php';
?>
                <p class="login-box-msg">Recuperar contraseña</p>

                <?php if ($a): ?>
                    <div class="alert alert-<?= $a[0] ?>" role="alert" aria-live="polite">
                        <i class="bi <?= $a[1] ?>"></i> <?= $a[2] ?>
                    </div>
                <?php endif; ?>

                <form action="proses.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Usuario"
                            maxlength="150" autocomplete="username" autofocus required>
                        <div class="form-text">Enviaremos el enlace al correo registrado en tu cuenta.</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <input type="submit" class="btn btn-primary" name="recuperar" value="Enviar">
                        <a href="../../index.php">Volver al inicio de sesión</a>
                    </div>
                </form>
<?php require '../../layout/auth_foot.php'; ?>
</body>

</html>
