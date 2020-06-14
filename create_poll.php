<?php 
    include 'php/layout/layout_head.php';
    $house_id = $content['id'];
    include 'php/layout/layout_header.php';
?>
<div class="content">
    <div class="panel" id="reg_panel">
        <form data-house_id="<?php echo($house_id)?>">
            <h3>Инициировать собрание  собственников</h3>                
            <div class="data_grid"><div><p>Адрес</p><p><a href="house?id=<?php echo($house_id)?>"><u><?php echo($content['adress']) ?></u></a></p></div></div>
            <div class="section">
                <?php      
                    echo($gui_obj->input('select',['id'=>'type_id','name'=>'type_id'],'формат собрания',0));
                    $user_types = '';
                    $first = 1;
                    $types_query = $kernel_obj->get_table('poll_type',"",1);
                    while($poll_type = mysqli_fetch_array($types_query)){
                           if($first == 1){
                               $selected = 'selected';
                               $first = 0;
                           }else{
                               $selected = '';
                           }
                           $user_types .= '<option '.$selected.' value="'.$poll_type['id'].'">'.$poll_type['title'].'</option>';
                        }
                    echo($user_types.'</select>');
                    echo($gui_obj->input('text',['id'=>'poll_title','name'=>'poll_title','required'=>'required'],'тема собрания'));
                    echo($gui_obj->textarea(['id'=>'poll_description','name'=>'poll_description'],'краткое описание'));
                ?>
                <div class="form_row">
                    <div>
                        <?php
                            $date = date('Y-m-d', strtotime("+14 day"));
                            echo($gui_obj->input('date',['id'=>'date_start','name'=>'date_start','required'=>'required','min'=>$date,'value'=>$date],'день начала'));
                        ?>
                    </div>
                    <div>
                        <?php echo($gui_obj->input('num',['id'=>'days_amount','name'=>'days_amount','required'=>'required','placeholder'=>'(в днях)','min'=>'7','max'=>'16','value'=>'7'],'продолжительность'));?>
                    </div>
                </div>
            </div>
            <div class="section" id="poll_topics">
                <h5>Вопросы голосования</h5>
                <?php echo($gui_obj->input('textarea',['id'=>'topic_no_1','name'=>'poll_topic'],'тема',0)); ?>
                <div class="btn_roll" id="add_poll_topic"><div>+ добавить вопрос</div></div>
            </div>
            <div class="section">
                <?php
                    echo($gui_obj->button(['id'=>'btn_create_poll','class'=>'btn_green','name'=>'btn_submit','value'=>'Инициировать']));
                    echo($gui_obj->button(['class'=>'btn_border','name'=>'btn_link','value'=>'Отмена','data'=>['link'=>'house?id=<?php echo($house_id)?>']]));
                ?>
            </div>
        </form>
    </div>
</div>
<?php include('php/layout/layout_foot.php')?>
<?php include('php/layout/layout_footer.php')?>