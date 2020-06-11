<?php
    spl_autoload_register(function ($class_name){include 'php/lib/'.$class_name . '.php';});
    $constructor_obj = new constructor();
    $page = $constructor_obj->get_page_data();
?>