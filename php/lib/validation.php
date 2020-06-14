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
                    'login_auth' => 'Аккаунт с указанным логином не подтвержден. Ссылка с подтверждением должна была прийти в письме на указанный при регистрации адрес электронной почты',
                    'companyname' => 'Введите название компании',
                    'reg_num' => 'Введите ИНН компании',
                    'adress' => 'Введите адрес',
                    'flat_num' => 'Введите номер квартиры',
                    'flat_area' => 'Введите площадь квартиры',
                    'flat_share' => 'Укажите свою долю в праве собственности',
                    'share_amount' => 'Укажите общее число долей в праве собственности',
                    'title_doc' => 'Укажите правоустанавливающий документ',
                    'date_start' => 'Укажите дату начала собрания',
                    'days_amount' => 'Укажите продолжительность собрания в днях',
                    'poll_title' => 'Укажите тему собрания'
                ],
                'invalid' => [
                    'lastname' => 'Введите корректную фамилию',
                    'secondname' => 'Введите корректное отчество',
                    'firstname' => 'Введите корректное имя',
                    'email' => 'Введите корректный адрес эл. почты',
                    'password' => 'Введите корректный пароль',
                    'login_auth' => 'Пользователь с таким адресом электронной почты не зарегистрирован.',
                    'login_reg' => 'Пользователь с таким адресом электронной почты уже зарегистрирован',
                    'companyname' => 'Введите корректное название компании',
                    'reg_num' => 'Введите корректный ИНН компании',
                    'adress' => 'Введите корректный адрес',
                    'flat_num' => 'Введите корректный номер квартиры',
                    'flat_area' => 'Введите корректную площадь квартиры',
                    'flat_share' => 'Укажите кореектную долю в праве собственности',
                    'share_amount' => 'Укажите корректное число долей в праве собственности',
                    'title_doc' => 'Введите корректный правоустанавливающий документ',
                    'date_start' => 'Укажите дату начала собрания не ранее, чем через 14 дней',
                    'days_amount' => 'Укажите продолжительность собрания  не менее, чем 7 и не более, чем 16 дней',
                    'poll_title' => 'Укажите корректную тему собрания',
                    'poll_description' => 'Укажите корректное описание собрания'
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
            $user = '';
            
            
            if ((empty($value)) && ($entity['required'] == 'true')){                
                $valid = 0;
                $error= $this->error_list['empty'][$type];
            }
            
            if($valid == 1){
                $value = stripslashes($value);
                $value = htmlspecialchars($value);
                $value = trim($value);
                if(($type!=null) && ($entity['validate'] == 1) && (!empty($value))){
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
            
            if($valid == 1){
                if(stripos($entity['type'], 'date') !== false){
                    $val_format = strtotime($value);
                    if(!empty($entity['min'])){
                        if ($val_format < strtotime($entity['min'])){
                            $valid = 0;
                        }
                    }
                    
                    if(!empty($entity['max'])){
                        if ($val_format > strtotime($entity['max'])){
                            $valid = 0;
                        }
                    }
                }else{                
                    if(!empty($entity['min'])){
                        if ($value < $entity['min']){
                            $valid = 0;
                        }
                    }

                    if(!empty($entity['max'])){
                        if ($value > $entity['max']){
                            $valid = 0;
                        }
                    }
                }
                
                if($valid == 0){
                    $error= $this->error_list['invalid'][$type];
                }
            }
            
            if(($valid == 1) && ((stripos($entity['type'], 'num') !== false) || (stripos($entity['type'], 'share') !== false))){
                if($value <= 0){
                    $valid = 0;
                    $error= $this->error_list['invalid'][$type];
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
                            $user = $login_check;
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
                'value' => $value,
                'user' => $user
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
                $result['inputs'][$entity['id']] = $entity_check;
                if($entity['id'] == 'login_auth'){                    
                    $user = $entity_check['user'];
                    $result['user'] = $user;
                    $login = $entity_check['value'];                    
                }else if($entity['id'] == 'password_auth'){
                    $password = $entity_check['value'];                    
                    if($valid == 1){                       
                        if(password_verify($password, $user['password']) == false){
                            $valid = 0;
                            $error= $this->error_list['invalid']['password'];                            
                        }        
                        $result['inputs'][$entity['id']]['valid'] = $valid;
                        $result['inputs'][$entity['id']]['error'] = $error;
                    }
                }                
            }
            
            $result['valid'] = $valid;
            
            return $result;
        }  
    
    }
?>