<?php
require_once "config/database.php";

// Capturar y limpiar los datos enviados desde el formulario
$username = mysqli_real_escape_string($mysqli, stripslashes(strip_tags(htmlspecialchars(trim($_POST['username'])))));
$password = mysqli_real_escape_string($mysqli, stripslashes(strip_tags(htmlspecialchars(trim($_POST['password'])))));

// Validar que los campos no contengan caracteres inválidos
if (!ctype_alnum($username) || !ctype_alnum($password)) {
    header("Location: index.php?alert=1"); // Error de credenciales
    exit();
}

// Convertir la contraseña a MD5 (solo por compatibilidad)
$password = md5($password);

//Verificar si el usuario existe
$query_user = mysqli_query($mysqli, "SELECT * FROM usuarios WHERE username = '$username'")
    or die('Error al realizar la consulta: ' . mysqli_error($mysqli));

if (mysqli_num_rows($query_user) == 0) {
    // Usuario no existe
    header("Location: index.php?alert=1");
    exit();
}

$data = mysqli_fetch_assoc($query_user);

//Verificar si está bloqueado
if ($data['status'] == 'bloqueado') {
    header("Location: index.php?alert=6"); // cuenta bloqueada
    exit();
}

// Verificar contraseña
if ($data['password'] === $password) {
    //contraseña correcta, reinicia contador
    mysqli_query($mysqli, "UPDATE usuarios SET intentos_fallidos = 0 WHERE username = '$username'")
        or die('Error al reiniciar intentos: ' . mysqli_error($mysqli));

    // Iniciar sesión
    session_start();
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['password'] = $data['password']; //acordate que esto es mala practica guardar los datos del cliente en variables xd
    $_SESSION['name_user'] = $data['name_user'];
    $_SESSION['permisos_acceso'] = $data['permisos_acceso'];

    header("Location: main.php?module=start");
    exit();
} else {
    //Contraseña incorrecta, incrementar contador
    $intentos = $data['intentos_fallidos'] + 1;

    // Actualizar contador
    mysqli_query($mysqli, "UPDATE usuarios SET intentos_fallidos = $intentos WHERE username = '$username'")
        or die('Error al actualizar intentos: ' . mysqli_error($mysqli));

    // Si llega a 3, bloquear usuario
    if ($intentos >= 3) {
        mysqli_query($mysqli, "UPDATE usuarios SET status = 'bloqueado' WHERE username = '$username'")
            or die('Error al bloquear usuario: ' . mysqli_error($mysqli));
        header("Location: index.php?alert=3"); // cuenta bloqueada
    } else {
        header("Location: index.php?alert=1"); // contraseña incorrecta
    }
    exit();
}
?>