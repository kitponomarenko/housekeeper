<?php
    spl_autoload_register(function ($class_name){include 'php/lib/'.$class_name . '.php';});
    $user_obj = new user();
    $confirm = $user_obj->user_logout();
?>