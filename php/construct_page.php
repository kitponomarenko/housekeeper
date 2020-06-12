<?php
    spl_autoload_register(function ($class_name){include 'php/lib/'.$class_name . '.php';});
    $constructor_obj = new constructor();
    $page = $constructor_obj->get_page_data();
    if(!empty($page['redirect'])){
        header('Location:'.$page['redirect']);
        exit;
    }
    if(file_exists(dirname(__FILE__).'/page_'.$page['title'].'.php')){include('page_'.$page['title'].'.php');}
    $gui_obj = new gui();
?>