<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sysweb">
    <meta name="author" content="Aldo Torres">
    <title><?= htmlspecialchars($pageTitle) ?> · Sysweb</title>
    <link rel="shortcut icon" href="images/favicon.ico">

    <!-- Plantilla AdminLTE 4 (local) -->
    <link rel="stylesheet" href="assets/adminlte/css/overlayscrollbars.min.css">
    <link rel="stylesheet" href="assets/adminlte/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/sysweb.css">

    <!-- Librerías compartidas: se cargan UNA sola vez aquí; las vistas ya no deben volver a incluirlas -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/adminlte/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Valores por defecto para todas las tablas y selects del sistema
        $.extend(true, $.fn.dataTable.defaults, {
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
        });
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('width', '100%');
    </script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
