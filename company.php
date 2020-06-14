<?php 
    include 'php/layout/layout_head.php';
    $company_id = $content['id'];
    if($content['id'] == $session['user']['id']){
        $self = 1;
        $controls = '<div class="section">            
                '.$gui_obj->button(['id'=>'btn_logout','class'=>'btn_border','name'=>'btn_link','value'=>'Выйти','data'=>['link'=>'logout']]).'
        </div>';
    }else{
        $self = 0;
        $controls = '';
    }
    $company_data = $content_obj->get_company_data($company_id,$self);
    $company_houses = $content_obj->get_company_houses($company_id,$self);
    include 'php/layout/layout_header.php';
?>
<div class="content">
    <div class="panel">
        <?php echo($company_houses); ?>
    </div>
    <div class="panel">
        <div class="section">                             
            <?php echo($company_data) ?>
        </div>
        <?php echo($controls) ?>
    </div>
</div>
<?php include('php/layout/layout_footer.php')?>
<?php include('php/layout/layout_foot.php')?>