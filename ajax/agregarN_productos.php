<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_prod = mysqli_real_escape_string($mysqli, $_POST['nombre_prod']);
    $tipo_producto = mysqli_real_escape_string($mysqli, $_POST['tipo_producto']);
    $unidad_medida = mysqli_real_escape_string($mysqli, $_POST['unidad_medida']);
    $tipo_impuesto = mysqli_real_escape_string($mysqli, $_POST['tipo_impuesto']);


    //Validar campos vacíos
    if (empty($nombre_prod) || empty($tipo_producto) || empty($unidad_medida) || empty($tipo_impuesto)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Todos los campos son obligatorios. Por favor complete los datos.'
        ]);
        exit;
    }

    // Validar si ya existe un producto con la misma descripción
    $check_query = mysqli_query($mysqli, "SELECT cod_producto FROM producto WHERE p_descrip = '$nombre_prod'");
    if (mysqli_num_rows($check_query) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ya existe un producto con esa descripción.'
        ]);
        exit;
    }

    // Generar código único para el producto
    $query_id = mysqli_query($mysqli, "SELECT MAX(cod_producto) as id FROM producto");
    $data_id = mysqli_fetch_assoc($query_id);
    $nuevo_codigo = ($data_id['id'] ?? 0) + 1;

    $query = "INSERT INTO producto (cod_producto, cod_tipo_prod, id_u_medida, p_descrip, tipo_impuesto) 
              VALUES ('$nuevo_codigo', '$tipo_producto', '$unidad_medida', '$nombre_prod', '$tipo_impuesto')";

    if (mysqli_query($mysqli, $query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Producto guardado correctamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => mysqli_error($mysqli)
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido'
    ]);
}
