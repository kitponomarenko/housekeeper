<?php
    spl_autoload_register(function ($class_name){include 'php/lib/'.$class_name . '.php';});
    $constructor_obj = new constructor();
    $page = $constructor_obj->get_page_data();    
    if(!empty($page['redirect'])){
        header('Location:'.$page['redirect']);
        exit;
    }
    $session = $page['session'];
    $content = $page['content'];
    if(file_exists(dirname(__FILE__).'/page_'.$page['title'].'.php')){include('page_'.$page['title'].'.php');}
    $kernel_obj = new kernel();
    $gui_obj = new gui();
    $content_obj = new content();
?>