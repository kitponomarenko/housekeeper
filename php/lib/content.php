<?php
    class content{
        
        private $kernel_obj;
        private $user_obj;
        
        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->user_obj = new user();
        }
        
        function get_tenant_data(
                $user_id = ''
        ){
            $result = '';
            
            if(empty($user_id)){
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }
            
            $tenant_data = $this->kernel_obj->get_table('tenant',"WHERE id='$user_id'");
            $result = '
                <h3>'.$tenant_data['lastname'].' '.$tenant_data['firstname'].' '.$tenant_data['secondname'].'</h3>
                <div class="data_grid">
                    <div><p>Дата регистрации</p><p>'.$tenant_data['date_reg'].'</p></div>
                    <div><p>Эл. почта</p><p>'.$tenant_data['email'].'</p></div>
                </div>
            ';
            
            return $result;
        }
        
        function get_company_data(
                $user_id = ''
        ){
            $result = '';
            
            if(empty($user_id)){
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }
            
            $company_data = $this->kernel_obj->get_table('company',"WHERE id='$user_id'");
            $result = '
                <h3>'.$company_data['companyname'].'</h3>
                <div class="data_grid">
                    <div><p>Дата регистрации</p><p>'.$company_data['date_reg'].'</p></div>
                    <div><p>Эл. почта</p><p>'.$company_data['email'].'</p></div>
                    <div><p>ИНН</p><p>'.$company_data['reg_num'].'</p></div>
                    <div><p>Адрес</p><p>'.$company_data['adress'].'</p></div>
                </div>
            ';
            
            return $result;
        }
    }
        
?>