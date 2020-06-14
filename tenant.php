<?php 
    include 'php/layout/layout_head.php';
    include 'php/layout/layout_header.php';
    $tenant_id = $content['id'];
    if($content['id'] == $session['user']['id']){
        $self = 1;
        $controls = '<div class="section">            
                '.$gui_obj->button(['id'=>'btn_logout','class'=>'btn_border','name'=>'btn_link','value'=>'Выйти','data'=>['link'=>'logout']]).'
        </div>';
    }else{
        $self = 0;
        $controls = '';
    }
    $tenant_data = $content_obj->get_tenant_data($tenant_id,$self);
    $tenant_property = $content_obj->get_tenant_property($tenant_id,$self);
?>
<div class="content">
    <div class="panel">
        <?php echo($tenant_property); ?>
    </div>
    <div class="panel">
        <div class="section">                             
            <?php echo($tenant_data) ?>
        </div>
        <?php echo($controls) ?>
    </div>
</div>
<?php include('php/layout/layout_footer.php')?>
<?php include('php/layout/layout_foot.php')?>