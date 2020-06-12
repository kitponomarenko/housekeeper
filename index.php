<?php include 'php/layout/layout_head.php';         ?>
<div class="content">
    <div class="panel">
        <form>
            <h2>Авторизация</h2>
            
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