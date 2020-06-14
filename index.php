<?php include 'php/layout/layout_head.php';         ?>
<div class="content">
    <div class="panel">
        <form data-form_src="tenant">
            <div class="section">
                <h3>Авторизация</h3>                
                <p class="txt_tiny">Для использования сервиса необходимо войти в свой аккаунт, а если его еще нет - <a href="reg"><u>зарегистрироваться в системе.</u></a></p>
                <div class="radio_input" id="user_types">
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
            <div class="section">
            <?php            
                echo($gui_obj->input('email',['id'=>'login_auth', 'placeholder'=>'электронная почта','required'=>'required','data'=>['input_src'=>'tenant']],'логин'));
                echo($gui_obj->input('password',['id'=>'password_auth','required'=>'required'],'пароль',0));                
            ?>
            </div>                
            <div class="section">
                <?php
                    echo($gui_obj->button(['id'=>'btn_auth','class'=>'btn_green','name'=>'btn_submit','value'=>'Войти']));
                    echo($gui_obj->button(['class'=>'btn_border','name'=>'btn_link','value'=>'Зарегистрироваться','data'=>['link'=>'reg']]));
                ?>
            </div>
        </form>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>