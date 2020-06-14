<?php 
    include 'php/layout/layout_head.php';
    $flat_id = $content['id'];
    
    $flat_data = $content_obj->get_flat_data($flat_id);
    include 'php/layout/layout_header.php';
?>
<div class="content">
    <div class="panel" id="flat_panel" data-flat_id="<?php echo($flat_id);?>">                          
        <?php echo($flat_data) ?>
    </div>
</div>
<?php include('php/layout/layout_footer.php')?>
<?php include('php/layout/layout_foot.php')?>