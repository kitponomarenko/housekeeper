<?php
    class user{
        
        private $kernel_obj;

        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
        }
        
        function open_session(){
            if(session_status() == 1){
                session_start();
            }
        }
        
        
        function check_session(
        ){
            $user = '';
            $user_role = 4; // guest-role by default
            $user_tbl = '';         
            $cookies_set = 0;            
            
            $this->open_session();
            $token_cookie = $this->kernel_obj->config['site_prefix'].'_cookie_token';
            $role_cookie = $this->kernel_obj->config['site_prefix'].'_cookie_role';
            
            if(isset($_COOKIE[$token_cookie])){
                $user_token = $_COOKIE[$token_cookie];
                ++$cookies_set;
            }
            if(isset($_COOKIE[$role_cookie])){
                $user_role = $_COOKIE[$role_cookie];
                ++$cookies_set;
            }
            
            if($cookies_set == 2){
                $user_tbl = $this->kernel_obj->get_table('user',"WHERE id='$user_role'")['tbl_name'];
                $user = $this->kernel_obj->get_table($user_tbl,"WHERE token='$user_token'");
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user_role;
            }            
            
            if ((isset($_SESSION['id'])) && (isset($_SESSION['role']))){
                if(empty($user_tbl)){
                    $user_tbl = $this->kernel_obj->get_table('user',"WHERE id='$_SESSION[role]'")['tbl_name'];
                    $user = $this->kernel_obj->get_table($user_tbl,"WHERE id='$_SESSION[id]'");
                }                
            }
            
            return [
                'user' => $user,
                'role' => $user_role                             
                ];
        }
        
        
        function check_access(
            $user_role,
            $page_access
        )
        {
            $result = false;
            
            if($page_access != 0){
                $access_arr = explode('#', $page_access);
                foreach ($access_arr as $arr_el){
                    if($arr_el == $user_role){
                        $result = true; 
                        break;
                    }
                }
            }else{
                $result = true;
            }
            
            return $result;
        }
    }

?>