<?php
require_once "config/auth.php";

// Mensaje que llega por redirección (cierre de sesión, sesión vencida, acceso sin JavaScript, etc.)
$alerta = null;
if (!empty($_GET['alert'])) {
    $alerta = auth_mensaje((int) $_GET['alert'], (int) ($_GET['left'] ?? 0));
}
$iconos = ['success' => 'cil-check-circle', 'danger' => 'cil-x-circle', 'warning' => 'cil-warning'];
?><!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sysweb">
    <meta name="author" content="Aldo Torres">

    <link rel="shortcut icon" href="images/favicon.ico" />
    <title>Sysweb - Login</title>

    <!-- CoreUI CSS -->
    <link href="dist/css/coreui.min.css" rel="stylesheet">
    <link href="dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@coreui/icons/css/all.min.css">

</head>

<body class="app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <img src="images/favicon.ico" alt="Sysweb" height="50">
                    <h1 style="color: #3c8dbc;">Sysweb</h1>
                </div>

                <!-- Mensajes (aria-live para lectores de pantalla) -->
                <div id="mensaje" aria-live="polite">
                    <?php if ($alerta): ?>
                        <div class="alert alert-<?= $alerta[0] ?>" role="alert">
                            <i class="<?= $iconos[$alerta[0]] ?? 'cil-warning' ?>"></i> <?= htmlspecialchars($alerta[1]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Login Form -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">
                            <i class="cil-user"></i> Por favor, inicie sesión
                        </h5>
                        <form id="formLogin" action="login-check.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Usuario" maxlength="150" autocomplete="username" autofocus required>
                                <div class="invalid-feedback" id="errUsername"></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group has-validation">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Contraseña" maxlength="128" autocomplete="current-password"
                                        required>
                                    <button class="btn btn-outline-secondary" type="button" id="verPassword"
                                        aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                                        <i class="cil-low-vision" id="iconoVer"></i>
                                    </button>
                                    <div class="invalid-feedback" id="errPassword"></div>
                                </div>
                                <div class="form-text text-warning d-none" id="avisoMayusculas">
                                    <i class="cil-warning"></i> Tienes las mayúsculas activadas.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btnIngresar">
                                <span class="spinner-border spinner-border-sm d-none" id="spinner" role="status"
                                    aria-hidden="true"></span>
                                <span id="textoBtn">Ingresar</span>
                            </button>
                            <hr>
                            <a href="modules/recuperar/recuperar.php">¿Olvidaste tu contraseña?</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CoreUI JS -->
    <script src="dist/js/coreui.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('formLogin');
            const user = document.getElementById('username');
            const pass = document.getElementById('password');
            const btn = document.getElementById('btnIngresar');
            const spinner = document.getElementById('spinner');
            const textoBtn = document.getElementById('textoBtn');
            const caja = document.getElementById('mensaje');
            const iconos = { success: 'cil-check-circle', danger: 'cil-x-circle', warning: 'cil-warning' };

            function mostrar(nivel, texto) {
                caja.innerHTML = '';
                const d = document.createElement('div');
                d.className = 'alert alert-' + nivel;
                d.setAttribute('role', 'alert');
                const i = document.createElement('i');
                i.className = iconos[nivel] || 'cil-warning';
                d.append(i, ' ' + texto);
                caja.appendChild(d);
            }

            function validar(campo, errEl, nombre) {
                const v = campo.value;
                let msg = '';
                if (v.trim() === '') msg = 'Ingresa tu ' + nombre + '.';
                else if (campo === user && v.trim().length < 3) msg = 'El usuario debe tener al menos 3 caracteres.';
                campo.classList.toggle('is-invalid', msg !== '');
                campo.classList.toggle('is-valid', msg === '' && v !== '');
                errEl.textContent = msg;
                return msg === '';
            }

            user.addEventListener('input', () => validar(user, document.getElementById('errUsername'), 'usuario'));
            pass.addEventListener('input', () => validar(pass, document.getElementById('errPassword'), 'contraseña'));
            user.addEventListener('blur', () => validar(user, document.getElementById('errUsername'), 'usuario'));
            pass.addEventListener('blur', () => validar(pass, document.getElementById('errPassword'), 'contraseña'));

            // Mostrar/ocultar contraseña
            document.getElementById('verPassword').addEventListener('click', function () {
                const oculto = pass.type === 'password';
                pass.type = oculto ? 'text' : 'password';
                this.setAttribute('aria-pressed', oculto ? 'true' : 'false');
                document.getElementById('iconoVer').className = oculto ? 'cil-eye' : 'cil-low-vision';
            });

            // Aviso de bloqueo de mayúsculas
            pass.addEventListener('keyup', e => {
                document.getElementById('avisoMayusculas')
                    .classList.toggle('d-none', !(e.getModifierState && e.getModifierState('CapsLock')));
            });

            function ocupado(si) {
                btn.disabled = si;
                spinner.classList.toggle('d-none', !si);
                textoBtn.textContent = si ? 'Verificando...' : 'Ingresar';
            }

            // Envío por AJAX: errores de credenciales al instante, sin recargar la página
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const okU = validar(user, document.getElementById('errUsername'), 'usuario');
                const okP = validar(pass, document.getElementById('errPassword'), 'contraseña');
                if (!okU || !okP) {
                    (okU ? pass : user).focus();
                    return;
                }
                ocupado(true);
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form)
                    });
                    const r = await resp.json();
                    if (r.ok) {
                        textoBtn.textContent = 'Ingresando...';
                        window.location.href = r.redirect;
                        return;
                    }
                    mostrar(r.nivel, r.mensaje);
                    pass.value = '';
                    pass.classList.remove('is-valid', 'is-invalid');
                    if (r.bloqueado) {
                        user.disabled = pass.disabled = true;
                        btn.disabled = true;
                        textoBtn.textContent = 'Cuenta bloqueada';
                        spinner.classList.add('d-none');
                        return;
                    }
                    pass.focus();
                } catch (err) {
                    mostrar('warning', 'No se pudo conectar con el servidor. Revisa tu conexión e inténtalo de nuevo.');
                }
                ocupado(false);
            });
        })();
    </script>
</body>

</html>
