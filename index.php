<?php
require_once "config/auth.php";

// Mensaje que llega por redirección (cierre de sesión, sesión vencida, acceso sin JavaScript, etc.)
$alerta = null;
if (!empty($_GET['alert'])) {
    $alerta = auth_mensaje((int) $_GET['alert'], (int) ($_GET['left'] ?? 0));
}
$iconos = ['success' => 'bi-check-circle', 'danger' => 'bi-x-circle', 'warning' => 'bi-exclamation-triangle'];

$base = '';
$authTitle = 'Iniciar sesión';
require 'layout/auth_head.php';
?>
                <p class="login-box-msg">Por favor, inicie sesión</p>

                <!-- Mensajes (aria-live para lectores de pantalla) -->
                <div id="mensaje" aria-live="polite">
                    <?php if ($alerta): ?>
                        <div class="alert alert-<?= $alerta[0] ?>" role="alert">
                            <i class="bi <?= $iconos[$alerta[0]] ?? 'bi-exclamation-triangle' ?>"></i> <?= htmlspecialchars($alerta[1]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="formLogin" action="login-check.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Usuario"
                            maxlength="150" autocomplete="username" autofocus required>
                        <div class="invalid-feedback" id="errUsername"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group has-validation">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Contraseña" maxlength="128" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="verPassword"
                                aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                                <i class="bi bi-eye-slash" id="iconoVer"></i>
                            </button>
                            <div class="invalid-feedback" id="errPassword"></div>
                        </div>
                        <div class="form-text text-warning d-none" id="avisoMayusculas">
                            <i class="bi bi-exclamation-triangle"></i> Tienes las mayúsculas activadas.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btnIngresar">
                        <span class="spinner-border spinner-border-sm d-none" id="spinner" role="status"
                            aria-hidden="true"></span>
                        <span id="textoBtn">Ingresar</span>
                    </button>
                </form>
                <p class="mb-0 mt-3">
                    <a href="modules/recuperar/recuperar.php">¿Olvidaste tu contraseña?</a>
                </p>
<?php require 'layout/auth_foot.php'; ?>
    <script>
        (function () {
            const form = document.getElementById('formLogin');
            const user = document.getElementById('username');
            const pass = document.getElementById('password');
            const btn = document.getElementById('btnIngresar');
            const spinner = document.getElementById('spinner');
            const textoBtn = document.getElementById('textoBtn');
            const caja = document.getElementById('mensaje');
            const iconos = { success: 'bi-check-circle', danger: 'bi-x-circle', warning: 'bi-exclamation-triangle' };

            function mostrar(nivel, texto) {
                caja.innerHTML = '';
                const d = document.createElement('div');
                d.className = 'alert alert-' + nivel;
                d.setAttribute('role', 'alert');
                const i = document.createElement('i');
                i.className = 'bi ' + (iconos[nivel] || 'bi-exclamation-triangle');
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
                document.getElementById('iconoVer').className = oculto ? 'bi bi-eye' : 'bi bi-eye-slash';
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
