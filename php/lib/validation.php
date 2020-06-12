<?php
    class validation{
        
        private $kernel_obj;
        private $error_list;

        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->error_list = [
                'empty' => [
                    'lastname' => 'Введите свою фамилию',
                    'secondname' => 'Введите свое отчество',
                    'firstname' => 'Введите свое имя',
                    'email' => 'Введите свой адрес эл. почты',
                    'password' => 'Введите пароль',
                    'login_auth' => 'Аккаунт с указанным логином не подтвержден. Ссылка с подтверждением должна была прийти в письме на указанный при регистрации адрес электронной почты'
                ],
                'invalid' => [
                    'lastname' => 'Введите корректную фамилию',
                    'secondname' => 'Введите корректное отчество',
                    'firstname' => 'Введите корректное имя',
                    'email' => 'Введите корректный адрес эл. почты',
                    'password' => 'Введите корректный пароль',
                    'login_auth' => 'Пользователь с таким адресом электронной почты не зарегистрирован',
                    'login_reg' => 'Пользователь с таким адресом электронной почты уже зарегистрирован'
                ]
            ];
        }
        
        function validate_entity(
            $entity
        ){
            $valid = 1;
            $error = '';
            $result = [];
            $type = $entity['type'];
            $value = $entity['value'];
            
            
            if ((empty($value)) && ($entity['required'] == true)){
                $valid = 0;
                $error= $this->error_list['empty'][$type];
            }
            
            if($valid == 1){
                $value = stripslashes($value);
                $value = htmlspecialchars($value);
                $value = trim($value);
                if(($type!=null) && ($entity['validate'] == 1)){
                    if($type=='email'){
                        if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
                            $valid = 0;
                        }
                    }
                    else if(!empty($this->kernel_obj->config[$type.'_mask'])){
                        if(!preg_match($this->kernel_obj->config[$type.'_mask'],$value)) {
                            $valid = 0;
                        }
                    }
                    if($valid == 0){
                        $error= $this->error_list['invalid'][$type];
                    }
                }
            }
            
            if(($valid == 1) && (stripos($entity['id'], 'login') !== false)){
                $login_type = $entity['id'];
                $login_check = $this->kernel_obj->get_table($entity['src'],"WHERE $type='$value'");
                
                if($login_type == 'login_auth'){
                    if(empty($login_check)){
                        $valid = 0;
                        $error= $this->error_list['invalid'][$login_type];
                    }else{
                        if($login_check['confirm'] == 0){
                            $valid = 0;
                            $error= $this->error_list['empty'][$login_type];
                        }else{
                            $result['user'] = $login_check;
                        }
                    }
                }else if($login_type == 'login_reg'){
                    if((!empty($login_check)) && ($login_check['confirm'] == 1)){
                        $valid = 0;
                        $error= $this->error_list['invalid'][$login_type];
                    }                    
                }            
                
            }
            
            $result = [
                'valid' => $valid,
                'error' => $error,
                'value' => $value                
            ];
            
            return $result;
        }
        
        function validate_auth(
                $form
        ){
            $valid = 1;
            $error = '';
            $result = [];
            foreach($form as $entity){
                $entity_check = $this->validate_entity($entity);
                $valid = $entity_check['valid'];
                $result[$entity['id']] = $entity_check;
                if($entity['id'] == 'login_auth'){                    
                    $user = $entity_check['user'];
                    $login = $entity_check['value'];                    
                }else if($entity['id'] == 'password_auth'){
                    if($valid == 1){
                        $password = $entity_check['value'];
                        if(password_verify($password, $user['password']) == false){
                            $valid = 0;
                            $error= $this->error_list['invalid']['password'];
                        }
                    }
                }
            }
            
            $result['valid'] = $valid;
            
            return $result;
        }  
    
    }
?>