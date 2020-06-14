<?php 
    include 'php/layout/layout_head.php';
    $house_id = $content['id'];
    
    $house_data = $content_obj->get_house_data($house_id,1);
    include 'php/layout/layout_header.php';
?>
<div class="content">
    <div class="panel" id="house_panel" data-house_id="<?php echo($house_id);?>" data-house_controls="1">                          
        <?php echo($house_data) ?>
    </div>
</div>
<?php include('php/layout/layout_footer.php')?>
<?php include('php/layout/layout_foot.php')?>