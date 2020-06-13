<?php 
    include 'php/layout/layout_head.php';
    $company_data = $content_obj->get_company_data();
    $company_houses = $content_obj->get_company_houses();
?>
<div class="content">
    <div class="panel">
        <?php echo($company_houses); ?>
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