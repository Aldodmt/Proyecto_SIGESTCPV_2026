<?php
// Guarda la nueva contraseña si el token es válido y cumple la política.
require_once "../../config/database.php";
require_once "../../config/auth.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['Guardar'])) {
    header("Location: recuperar.php");
    exit();
}

$token = $_POST['token'] ?? '';
$nuevo = $_POST['nuevo'] ?? '';
$repetir = $_POST['repetir'] ?? '';

$id = auth_token_valido($mysqli, $token);
if ($id === null) {
    header("Location: nueva_contra.php?alert=3"); // enlace inválido o vencido
    exit();
}

if ($nuevo !== $repetir) {
    header("Location: nueva_contra.php?token=" . urlencode($token) . "&alert=1");
    exit();
}
if (auth_validar_politica($nuevo)) {
    header("Location: nueva_contra.php?token=" . urlencode($token) . "&alert=2");
    exit();
}

$hash = auth_hash($nuevo);
$up = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id_user = ?");
$up->bind_param('si', $hash, $id);
$ok = $up->execute();
$up->close();

if (!$ok) {
    header("Location: ../../index.php?alert=5");
    exit();
}

// El enlace deja de servir
$tokenHash = hash('sha256', $token);
$uso = $mysqli->prepare("UPDATE password_resets SET usado = 1 WHERE token_hash = ?");
$uso->bind_param('s', $tokenHash);
$uso->execute();
$uso->close();

$u = $mysqli->query("SELECT username FROM usuarios WHERE id_user = $id")->fetch_assoc();
auth_log($mysqli, $id, $u['username'] ?? '', 0, 'RECUPERACION', 'Contraseña restablecida con el enlace', 0);

header("Location: ../../index.php?alert=4");
exit();
