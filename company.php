<?php 
    include 'php/layout/layout_head.php';
    $company_data = $content_obj->get_company_data();
?>
<div class="content">
    <div class="panel">
        <form data-form_src="tenant">
            <div class="section">
                <h3>Дома в управлении</h3>                
                <p class="txt_tiny">Для начала работы в системе необходимо добавить хотя бы один дом.</p>
            </div>          
            <div class="section">
                <?php
                    echo($gui_obj->button(['id'=>'btn_add_house','class'=>'btn_green','name'=>'btn_submit','value'=>'+ Добавить дом']));
                ?>
            </div>
        </form>
    </div>
    <div class="panel">
        <div class="section">                             
            <?php echo($company_data) ?>
        </div>
        <div class="section">
            <?php
                echo($gui_obj->button(['id'=>'btn_logout','class'=>'btn_border','name'=>'btn_link','value'=>'Выйти','data'=>['link'=>'logout']]));
            ?>
        </div>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>