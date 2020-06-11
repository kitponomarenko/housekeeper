<?php
    if (isset($_POST['class'])){
        $class = ($_POST['class']);
    }
    if (isset($_POST['method'])){
        $method = ($_POST['method']);
    }
    if (isset($_POST['params'])){
        $params = array_values($_POST['params']);
    }
    spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
    $class_obj = new $class();
    $response = $class_obj->$method(...$params);
    echo json_encode($response);
?>