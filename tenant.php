<?php 
    include 'php/layout/layout_head.php';
    $tenant_data = $content_obj->get_tenant_data();
?>
<div class="content">
    <div class="panel">
        <form data-form_src="tenant">
            <div class="section">
                <h3>Квартиры в собственности</h3>                
                <p class="txt_tiny">Для участия в собраниях собственников необходимо добавить в аккаунт хотя бы одну квартиру.</p>
            </div>          
            <div class="section">
                <?php
                    echo($gui_obj->button(['id'=>'btn_add_flat','class'=>'btn_green','name'=>'btn_submit','value'=>'+ Добавить квартиру']));
                ?>
            </div>
        </form>
    </div>
    <div class="panel">
        <div class="section">                             
            <?php echo($tenant_data) ?>
        </div>
        <div class="section">
            <?php
                echo($gui_obj->button(['id'=>'btn_logout','class'=>'btn_border','name'=>'btn_link','value'=>'Выйти','data'=>['link'=>'logout']]));
            ?>
        </div>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>