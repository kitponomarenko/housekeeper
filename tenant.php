<?php 
    include 'php/layout/layout_head.php';
    $tenant_data = $content_obj->get_tenant_data();
    $tenant_property = $content_obj->get_tenant_property();
?>
<div class="content">
    <div class="panel">
        <?php echo($tenant_property); ?>
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