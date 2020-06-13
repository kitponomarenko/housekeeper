<?php 
    include 'php/layout/layout_head.php';
?>
<div class="content">
    <div class="panel">
        <h3>Добавить дом</h3>
        <p class="txt_tiny">Просто начните вводить адрес нужного дома, после чего вы сможете просмотреть его карточку и добавить к своему аккаунту.</p>
        <form>
            <div class="section">                             
                <?php echo($gui_obj->input('text',['id'=>'house_search','name'=>'house_search','placeholder'=>'название улицы, номер дома и т.п.','data'=>['search_active'=>'0']],'поиск по адресу дома',0)); ?>
            </div>
            <div class="section" id="house_search_reciever"></div>
        </form>
        <?php echo($gui_obj->button(['id'=>'btn_logout','class'=>'btn_border','name'=>'btn_link','value'=>'Назад','data'=>['link'=>'company']]))?>
    </div>
    <div class="panel" id="house_panel" data-house_controls="0" hidden></div>
</div>
<?php include('php/layout/layout_foot.php')?>