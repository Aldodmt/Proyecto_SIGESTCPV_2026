        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Sysweb</div>
            <strong>Copyright &copy; <?= date('Y') ?> - Desarrollado por Black Smoke S.A.</strong>
        </footer>
    </div><!-- /.app-wrapper -->

    <!-- Confirmación de cierre de sesión -->
    <div class="modal fade" id="dialog" tabindex="-1" aria-labelledby="logoutLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutLabel"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Seguro que quieres cerrar la sesión?</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-danger" href="logout.php">Sí, salir</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/adminlte/js/overlayscrollbars.browser.es6.min.js"></script>
    <script src="assets/adminlte/js/adminlte.min.js"></script>
    <script>
        // Barra de desplazamiento del sidebar (OverlayScrollbars, como indica AdminLTE)
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.querySelector('.sidebar-wrapper');
            if (wrapper && window.OverlayScrollbarsGlobal && OverlayScrollbarsGlobal.OverlayScrollbars) {
                OverlayScrollbarsGlobal.OverlayScrollbars(wrapper, {
                    scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
                });
            }
            // Tooltips de Bootstrap en cualquier elemento data-bs-toggle="tooltip"
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { new bootstrap.Tooltip(el); });
        });
    </script>
</body>

</html>
