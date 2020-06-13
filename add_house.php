<?php 
    include 'php/layout/layout_head.php';
?>
<div class="content">
    <div class="panel">
        <h3>Добавить дом</h3>
        <form>
            <div class="section">                             
                <?php echo($gui_obj->input('text',['id'=>'house_search','name'=>'house_search','placeholder'=>'название улицы, номер дома и т.п.','data'=>['search_active'=>'0']],'поиск по адресу дома',0)); ?>
            </div>
            <div class="section" id="house_search_reciever"></div>
        </form>
    </div>
    <div class="panel">
        <h3>Информация</h3>
        <div class="section" id="house_data"></div>
        
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>