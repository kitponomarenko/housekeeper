<?php
    $confirm = 1;
    if (isset($_GET['type'])){
        $user_type = ($_GET['type']);        
    }else{
        $confirm = 0;        
    }
    if (isset($_GET['id'])){
        $user_id = ($_GET['id']);        
    }else{
        $confirm = 0;        
    }
    if (isset($_GET['token'])){
        $user_token = ($_GET['token']);        
    }else{
        $confirm = 0;       
    }

    if($confirm == 1){
        spl_autoload_register(function ($class_name){include 'php/lib/'.$class_name . '.php';});
        $user_obj = new user();
        $confirm = $user_obj->user_confirm($user_type, $user_id, $user_token);
    }
    
    if($confirm == 0){
        header('Location: index'); 
        exit;  
    }else{
        header('Location: '.$user_type); 
        exit;  
    }
?>