<?php
// Cabecera común de las pantallas públicas (login y recuperación) con la plantilla AdminLTE.
// Variables: $base (prefijo de ruta hacia la raíz, '' o '../../'), $authTitle (título de la pestaña).
?><!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sysweb">
    <meta name="author" content="Aldo Torres">
    <title><?= htmlspecialchars($authTitle) ?> · Sysweb</title>
    <link rel="shortcut icon" href="<?= $base ?>images/favicon.ico">
    <link rel="stylesheet" href="<?= $base ?>assets/adminlte/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>assets/adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/sysweb.css">
</head>

<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?= $base ?>index.php"><img src="<?= $base ?>images/favicon.ico" alt="" height="40"> <b>Sys</b>web</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
