<?php include 'php/layout/layout_head.php';         ?>
<div class="content">
    <div class="panel">
        <form>
            <h2>Авторизация</h2>
            
            <div class="radio_input">
                <?php
                    $user_types = '';
                    $type_active = 1;
                    $user_query = $kernel_obj->get_table('user',"WHERE visible='1'",1);
                    while($user_type = mysqli_fetch_array($user_query)){                        
                        $user_types .= $gui_obj->btn_radio(['name'=>'switch_user','value'=>$user_type['title'],'data'=>['user_id'=>$user_type['tbl_name']]],$type_active);
                        if($type_active == 1){
                            $type_active = 0;
                        }
                    }
                    echo($user_types);                    
                ?>
            </div>
            <?php            
                echo($gui_obj->input('email',['id'=>'login_auth', 'placeholder'=>'электронная почта','required'=>'required','data'=>['input_src'=>'tenant']],'логин'));
                echo($gui_obj->input('password',['id'=>'password_auth','placeholder'=>'пароль','required'=>'required'],'пароль'));
                echo($gui_obj->button(['class'=>'btn_green','name'=>'btn_submit','value'=>'Войти']));
                echo($gui_obj->button(['class'=>'btn_border','name'=>'btn_link','value'=>'Зарегистрироваться']));
            ?>
        </form>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>