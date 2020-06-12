<?php
    class user{
        
        private $kernel_obj;
        private $valid_obj;
        private $gui_obj;
        private $token_cookie;
        private $role_cookie;

        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->valid_obj = new validation();
            $this->gui_obj = new gui();
            $this->token_cookie = $this->kernel_obj->config['site_prefix'].'_cookie_token';
            $this->role_cookie = $this->kernel_obj->config['site_prefix'].'_cookie_role';
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
            $token_cookie = $this->token_cookie;
            $role_cookie = $this->role_cookie;
            
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
        
        function user_start_session(
                $user_id,
                $user_type,
                $user_token
        ){
            $token_cookie = $this->token_cookie;
            $role_cookie = $this->role_cookie;
            $user_role = $this->kernel_obj->get_table('user',"WHERE tbl_name='$user_type'")['id'];
            
            $this->open_session();
            $_SESSION['id'] = $user_id;
            $_SESSION['role'] = $user_role;
            
            setcookie($token_cookie, $user_token, time()+(3600 * 24 * 30), "/");
            setcookie($role_cookie, $user_role, time()+(3600 * 24 * 30), "/");
        }
        
        function user_reg(
                $user_tbl,
                $form
        ){
            $result = [];
            $query_arr = [];
            $valid = 1;
            
            foreach($form as $entity){
                $entity_check = $this->valid_obj->validate_entity($entity);                
                $result['inputs'][$entity['id']] = $entity_check;
                $query_arr[$entity['type']] = $entity_check['value'];
                if($entity['type'] == 'email'){
                    $user_email = $entity_check['value'];
                }
                if($valid == 1){
                    $valid = $entity_check['valid'];
                }
            }
            
            if($valid == 1){
                $query_condition = '';
                $check_old = $this->kernel_obj->get_table($user_tbl,"WHERE email='$user_email'");
                if(!empty($check_old)){
                    $old_id = $check_old['id'];
                    $query_condition = "WHERE id='$old_id'";
                }
                
                $password = $this->kernel_obj->generate_code();
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $token = md5(uniqid(rand(),1));
                
                $query_arr['date_reg'] = date('Y-m-d H:i:s');
                $query_arr['password'] = $password_hash;
                $query_arr['token'] = $token;
                
                $create_user = $this->kernel_obj->new_query($user_tbl,$query_arr,$query_condition);
                if(empty($query_condition)){
                    $user_id = $create_user['id'];
                }else{
                    $user_id = $old_id;
                }
                
                $mail_subject = 'Подтверждение регистрации';
                $confirm_link = $this->kernel_obj->config['site_url'].'user_confirm?type='.$user_tbl.'&id='.$user_id.'&token='.$token;
                $mail_message = '
                    <h2>Спасибо за регистрацию</h2>
                    <p>Для подтверждения регистрации в системе необходимо перейти по ссылке:</p>
                    <a href="'.$confirm_link.'">Подтвердить регистрацию аккаунта</a>
                    <br>
                    <br>
                    <p>Ваш пароль для авторизации в системе:</p>
                    <h3>'.$password.'</h3>
                ';
                $this->kernel_obj->send_mail($user_email, $mail_subject, $mail_message);
                
                $message = '
                        <div class="panel">
                            <h3>Спасибо за регистрацию!</h3>
                            <div class="divider"></div>
                            <p>На указанный вами адрес электронной почты отправлено письмо с паролем и ссылкой для активации аккаунта. <br><br> Если в ближайшее время письмо не появится во "входящих", пожалуйста, проверьте папку "спам".</p>
                            <div class="divider"></div>
                            '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Перейти к авторизации','data'=>['link'=>'index']]).'
                        </div>
                    ';
                $result['message'] = $message;
            }
            
            $result['valid'] = $valid;
            
            return $result;
        }
        
        function user_confirm(
                $user_type,
                $user_id,
                $user_token
        ){
            $confirm = 1;
            
            $user_check = $this->kernel_obj->get_table($user_type,"WHERE id='$user_id'");
            if(empty($user_check)){
                $confirm = 0;
            }else{
                if ($user_token == $user_check['token']){
                    if($user_check['confirm'] == 0){
                        $confirm_user = $this->kernel_obj->new_query($user_type,['confirm'=>'1'],"WHERE id='$user_id'");
                        $this->user_start_session($user_id, $user_type, $user_token);                 
                    } else{
                        $confirm = 0;
                    } 
                } else{
                    $confirm = 0;
                }
            }
            
            return $confirm;
        }
        
        function user_auth(
                $user_tbl,
                $form
        ){
            $form_check = $this->valid_obj->validate_auth($form);
            $result['inputs'] = $form_check['inputs'];
            if($form_check['valid'] == 1){
                $this->user_start_session($form_check['user']['id'], $user_tbl, $form_check['user']['token']);
                $result['redirect'] = $user_tbl;                
            }
            
            $result['valid'] = $form_check['valid'];
            
            return $result;
        }
        
        function user_logout()
        {
            $token_cookie = $this->token_cookie;
            $role_cookie = $this->role_cookie;
            unset($_COOKIE[$token_cookie]);
            unset($_COOKIE[$role_cookie]);
            setcookie($token_cookie, null, -1, '/');
            setcookie($role_cookie, null, -1, '/');
            $this->open_session();
            session_unset();
            header( 'Location: index');
        }
        
    }

?>