<?php 
    include 'php/layout/layout_head.php';
?>
<div class="content">
    <div class="panel">
        <h3>Выбор дома</h3>
        <p class="txt_tiny">Найдите и выберите нужный дом - просто начните вводить адрес.</p>
        <form>
            <div class="section">                             
                <?php echo($gui_obj->input('text',['id'=>'house_search','name'=>'house_search','placeholder'=>'название улицы, номер дома и т.п.','data'=>['search_active'=>'1','search_active'=>'1']],'поиск по адресу дома',0)); ?>
            </div>
            <div class="section" id="house_search_reciever"></div>
        </form>
    </div>
    <div class="panel" id="flat_panel" data-house_controls="0" hidden></div>
</div>
<?php include('php/layout/layout_foot.php')?>