<?php include 'php/layout/layout_head.php';         ?>
<div class="content">
    <div class="panel">
        <form data-form_src="tenant">
            <div class="section">
                <h2>Регистрация</h2>                
                <p class="txt_tiny">Для использования сервиса необходимо зарегистрироваться в системе, а если аккаунт уже есть - <a href="index"><u>войти в свой аккаунт</u></a></p>
                <div class="radio_input" id="user_reg_types">
                    <?php
                        $user_types = '';
                        $type_active = 1;
                        $user_query = $kernel_obj->get_table('user',"WHERE visible='1'",1);
                        while($user_type = mysqli_fetch_array($user_query)){                        
                            $user_types .= $gui_obj->btn_radio(['name'=>'switch_user','value'=>$user_type['title'],'data'=>['user_type'=>$user_type['tbl_name']]],$type_active);
                            if($type_active == 1){
                                $type_active = 0;
                            }
                        }
                        echo($user_types);                    
                    ?>
                </div>
            </div>
            <?php
                echo($gui_obj->input('email',['id'=>'login_reg', 'placeholder'=>'электронная почта','required'=>'required','data'=>['input_src'=>'tenant']],'логин'));
            ?>
            <div class="section" name="reg_inputs" id="reg_tenant">
                <?php          
                    echo($gui_obj->input('text',['id'=>'lastname','name'=>'lastname','required'=>'required','placeholder'=>'Иванов'],'фамилия'));
                    echo($gui_obj->input('text',['id'=>'firstname','name'=>'firstname','required'=>'required','placeholder'=>'Иван'],'имя'));
                    echo($gui_obj->input('text',['id'=>'secondname','name'=>'secondname','placeholder'=>'Иванович'],'отчество (если есть)'));
                ?>
            </div>
            <div class="section" name="reg_inputs" id="reg_company" hidden>
                <?php      
                    echo($gui_obj->input('text',['id'=>'companyname','name'=>'companyname','required'=>'required','placeholder'=>'УК "Великолепная"'],'название компании'));
                    echo($gui_obj->input('text',['id'=>'reg_name','name'=>'reg_num','required'=>'required','placeholder'=>'0123456789'],'ИНН компании'));
                    echo($gui_obj->textarea(['id'=>'adress','name'=>'adress','required'=>'required','placeholder'=>'г. Оренбург, ул. Старая, д. 5'],'адрес компании'));
                ?>
            </div>
            <div class="section">
                <?php
                    echo($gui_obj->button(['class'=>'btn_green','name'=>'btn_submit','value'=>'Зарегистрироваться']));
                    echo($gui_obj->button(['class'=>'btn_border','name'=>'btn_link','value'=>'Войти в аккаунт','data'=>['link'=>'index']]));
                ?>
            </div>
        </form>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>