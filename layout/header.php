<?php
// Barra superior: botón del sidebar, accesos rápidos y menú del usuario.
$stmt = $mysqli->prepare("SELECT name_user, foto, permisos_acceso FROM usuarios WHERE id_user = ?");
$stmt->bind_param('i', $_SESSION['id_user']);
$stmt->execute();
$usuarioTop = $stmt->get_result()->fetch_assoc() ?: ['name_user' => $_SESSION['name_user'] ?? '', 'foto' => '', 'permisos_acceso' => $role];
$stmt->close();

$fotoTop = 'images/user/' . (($usuarioTop['foto'] ?? '') !== '' ? rawurlencode($usuarioTop['foto']) : 'user-default.png');
$nombreTop = htmlspecialchars($usuarioTop['name_user'] ?? '', ENT_QUOTES, 'UTF-8');
$rolTop = htmlspecialchars($usuarioTop['permisos_acceso'] ?? '', ENT_QUOTES, 'UTF-8');
?>
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Mostrar u ocultar el menú">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block"><a href="?module=start" class="nav-link">Inicio</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $fotoTop ?>" class="user-image rounded-circle shadow" alt="Foto de <?= $nombreTop ?>">
                            <span class="d-none d-md-inline"><?= $nombreTop ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-primary">
                                <img src="<?= $fotoTop ?>" class="rounded-circle shadow" alt="Foto de <?= $nombreTop ?>">
                                <p>
                                    <?= $nombreTop ?>
                                    <small><?= $rolTop ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="?module=perfil" class="btn btn-outline-secondary btn-flat">Perfil</a>
                                <a href="#" class="btn btn-outline-danger btn-flat float-end" data-bs-toggle="modal" data-bs-target="#dialog">Cerrar sesión</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
