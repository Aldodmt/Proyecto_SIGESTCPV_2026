<?php
session_start();
require "../../config/database.php";
require_once "../../config/auth.php";
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
} else {
    if (isset($_POST['Guardar'])) {
        if (isset($_SESSION['id_user'])) {
            $old_pass = $_POST['old_pass'] ?? '';
            $new_pass = $_POST['new_pass'] ?? '';
            $retype_pass = $_POST['retype_pass'] ?? '';

            $id_user = (int) $_SESSION['id_user'];

            $stmt = $mysqli->prepare("SELECT password FROM usuarios WHERE id_user = ?");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data || !auth_verificar($old_pass, $data['password'])) {
                header("Location: ../../main.php?module=password&alert=1");
            } elseif ($new_pass !== $retype_pass) {
                header("Location: ../../main.php?module=password&alert=2");
            } elseif (auth_validar_politica($new_pass)) {
                header("Location: ../../main.php?module=password&alert=4");
            } else {
                $hash = auth_hash($new_pass);
                $up = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id_user = ?");
                $up->bind_param('si', $hash, $id_user);
                if ($up->execute()) {
                    header("Location: ../../main.php?module=password&alert=3");
                } else {
                    header("Location: ../../main.php?module=password&alert=1");
                }
                $up->close();
            }
        }
    }
}
