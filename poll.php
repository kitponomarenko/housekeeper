<?php 
    include 'php/layout/layout_head.php';
    $poll_id = $content['id'];
    $poll_obj = new poll();

    $poll_data = $poll_obj->get_poll_data($poll_id);
    include 'php/layout/layout_header.php';
?>
<div class="content">
    <div class="panel" id="poll_panel" data-poll_id="<?php echo($poll_id);?>">                          
        <?php echo($poll_data) ?>
    </div>
</div>
<?php include('php/layout/layout_footer.php')?>
<?php include('php/layout/layout_foot.php')?>