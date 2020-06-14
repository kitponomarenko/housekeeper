<?php
    class poll{
        
        private $kernel_obj;
        private $user_obj;
        private $gui_obj;
        private $valid_obj;
        
        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->user_obj = new user();
            $this->gui_obj = new gui();
            $this->valid_obj = new validation();
        }

        function calc_vote(
                $house_id,
                $tenant_id
        ){
                $vote_pt = 0;
                $pote_pc = 0;
                $vote_pt_a = 0;
                $pote_pc_a = 0;
                
                $house_data = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
                $house_area = $house_data['area_total'];
                
                $property_query = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$tenant_id' AND house_id='$house_id'", 1);
                while($property_data = mysqli_fetch_array($property_query)){
                    $actual_area = $property_data['flat_area'] * $property_data['flat_share'] / $property_data['share_amount'];
                    if($property_data['confirm'] == 1){
                        $vote_pt = round($vote_pt + $actual_area, 2);
                    }else{
                        $vote_pt_a = round($vote_pt_a + $actual_area, 2);
                    }
                }
                $vote_pc = floor($vote_pt * 100 / $house_area);
                $vote_pc_a = floor($vote_pt_a * 100 / $house_area);
                
                return [
                    'actual' => [
                        'pt' => $vote_pt,
                        'pc' => $vote_pc
                    ],
                    'formal' => [
                        'pt' => $vote_pt_a,
                        'pc' => $vote_pc_a
                    ],
                ];
        }
        
        function create_poll(
                $house_id,
                $form
        ){
            $result = [];
            $valid = 1;
            $message = '';
            $query_arr = [];
            $topics_arr = [];
            
            foreach($form as $entity){
                $entity_check = $this->valid_obj->validate_entity($entity);                
                $result['inputs'][$entity['id']] = $entity_check;
                $query_arr[$entity['type']] = $entity_check['value'];
                if(($entity['type'] == 'poll_topic') && !empty($entity_check['value'])){
                    array_push($topics_arr, $entity_check['value']);
                }
                if($valid == 1){
                    $valid = $entity_check['valid'];
                }
            }
            
            if($valid == 1){
                $query_arr['house_id'] = $house_id;
                
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
                $user_role = $_SESSION['role'];
                
                
                $query_arr['user_id'] = $user_role;
                $query_arr['initiator_id'] = $user_id;
                $query_arr['date_created'] = date('Y-m-d');
                $query_arr['state'] = 1;
 
                $add_poll = $this->kernel_obj->new_query('poll',$query_arr);
                $poll_id = $add_poll['id'];
                
                if(!empty($topics_arr)){
                    $topic_query = ['poll_id' => $poll_id];
                    foreach($topics_arr as $topic){
                        $topic_query['topic'] = $topic;
                        $add_topic = $this->kernel_obj->new_query('poll_topic',$topic_query);
                    }
                }
                
                $message = '
                    <div class="panel">
                        <h3>Собрание успешно инициировано!</h3>
                        <div class="divider"></div>
                        <p>Теперь все собственники в указанном доме будут оповещены о проведении собрания.</p>
                        <div class="divider"></div>
                        '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'На страницу собрания','data'=>['link'=>'poll?id='.$poll_id]]).'
                    </div>
                    ';
                $result['message'] = $message;
            }
            $result['valid'] = $valid;
            
            return $result;
        }
        
        function get_actual_polls(
                $house_id
        ){
            $polls_active = '';
            $polls_upcoming = '';
            
            $polls_query = $this->kernel_obj->get_table('poll', "WHERE house_id='$house_id' AND state<3 ORDER BY date_start",1);
            while($poll_data = mysqli_fetch_array($polls_query)){
                if($poll_data['state'] == 1){
                    $polls_upcoming .= '
                        <div class="poll_card" name="btn_link" data-link="poll?id='.$poll_data['id'].'">
                            <p>'.$poll_data['poll_title'].'</p>
                            <p>'.$poll_data['date_start'].'</p>
                        </div>
                    ';
                }else if($poll_data['state'] == 2){
                    $days_left = $poll_data['days_amount'] - (strtotime(date('Y-m-d')) - strtotime($poll_data['date_start']))/(60*60*24);
                    $polls_active .= '
                        <div class="poll_card_active" name="btn_link" data-link="poll?id='.$poll_data['id'].'">
                            <p>'.$poll_data['poll_title'].'</p>
                            <p>Осталось дней: '.$days_left.'</p>
                        </div>
                    ';
                }
            }
            
            if(!empty($polls_active)){
                $polls_active = '<h5>Активные собрания</h5>'.$polls_active;
            }
            
            if(!empty($polls_upcoming)){
                $polls_upcoming = '<h5>Ближайшие собрания</h5>'.$polls_upcoming;
            }
            
            return [
                'active' => $polls_active,
                'upcoming' => $polls_upcoming,
            ];
        }
        
        function get_old_polls(
                $house_id
        ){
            $polls_old = '';
            
            $polls_query = $this->kernel_obj->get_table('poll', "WHERE house_id='$house_id' AND state=3 ORDER BY date_start",1);
            while($poll_data = mysqli_fetch_array($polls_query)){
                $new_date = ($poll_data['days_amount']*(60*60*24)) + strtotime($poll_data['date_start']);
                $last_date = date('Y-m-d', $new_date);
                $polls_old .= '
                    <div class="poll_card_old" name="btn_link" data-link="poll?id='.$poll_data['id'].'">
                        <p>'.$poll_data['poll_title'].'</p>
                        <p>Завершено: '.$last_date.'</p>
                    </div>
                    ';
                
            }          

            return $polls_old;
        }
        
        function get_poll_data(
                $poll_id
        ){
            $poll_description = '';
            $top_controls = '';
            $safe = 0;
            
            $this->user_obj->open_session();
            $user_id = $_SESSION['id'];
            $user_role = $_SESSION['role'];
            $user_tbl = $this->kernel_obj->get_table('user', "WHERE id='$user_role'")['tbl_name'];
            $poll_data = $this->kernel_obj->get_table('poll', "WHERE id='$poll_id'");
            $house_id = $poll_data['house_id'];
            $type_id = $poll_data['type_id'];
            $house_data = $this->kernel_obj->get_table('house', "WHERE id='$house_id'");
            $type_data = $this->kernel_obj->get_table('poll_type', "WHERE id='$type_id'");
            
            if($user_role == 2){
                if($user_id == $house_data['company_id']){
                    $safe = 1;
                }
            } if($user_role == 1){
                $property_data = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$user_id' AND house_id='$house_id' AND confirm='1'");
                if(!empty($property_data)){
                    $safe = 2;
                }
            }
            
            if($safe == 0){
                header('Location:'.$user_tbl);
            }

            if($poll_data['state'] == 1){
                $state = 'готовится';
            }else if($poll_data['state'] == 2){
                $state = 'идет';
            }else if($poll_data['state'] == 3){
                $state = 'завершен';
            }
            
            if(!empty($poll_data['poll_description'])){
                $poll_description = '<div><p>Краткое описание</p><p>'.$poll_data['poll_description'].'</p></div>';
            }
            $last_date = date('Y-m-d', ($poll_data['days_amount']*(60*60*24)) + strtotime($poll_data['date_start']));
            
            if($safe > 0){
                $topics_check = $this->kernel_obj->get_table('poll_topic', "WHERE poll_id='$poll_id'");
                if(!empty($topics_check)){
                    $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_get_topics','value'=>'Посмотреть вопросы повестки','data'=>['poll_op'=>0]]).'</div>';
                    if(($poll_data['state'] == 2) && ($safe == 2)){
                        $votes_check = $this->kernel_obj->get_table('poll_topic',"WHERE poll_id='$poll_id' AND yes_tenants NOT LIKE '%#$user_id#%' AND no_tenants NOT LIKE '%#$user_id#%' AND hold_tenants NOT LIKE '%#$user_id#%'");
                        if(!empty($votes_check)){
                            $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_get_topics','value'=>'Участвовать в голосовании','data'=>['poll_op'=>1]]).'</div>';
                        }
                    }
                    $bottom_controls = $top_controls;
                }
            }
            
            $result = '
                <h3>Собрание собственников</h3>
                '.$top_controls.'
                <div class="section"> 
                    <div class="data_grid">
                        <div><p>Адрес</p><p><a href="house?id='.$house_id.'"><u>'.$house_data['adress'].'</u></a></p></div>
                        <div><p><b>Статус</b></p><p><b>'.$state.'</b></p></div>
                        <div class="divider"></div>
                        <div><p>Формат собрания</p><p>'.$type_data['title'].'</p></div>
                        <div><p><b>Тема собрания</b></p><p><b>'.$poll_data['poll_title'].'</b></p></div>
                        '.$poll_description.'
                        <div class="divider"></div>
                        <div><p>Дата начала</p><p>'.$poll_data['date_start'].'</p></div>    
                        <div><p>Продолжительность</p><p>'.$poll_data['days_amount'].' дней</p></div>
                        <div><p>Дата завершения</p><p>'.$last_date.'</p></div>   
                        <div class="divider"></div>
                    </div>
                </div>
                '.$bottom_controls.'
            ';
            
            return $result;
        }
        
        function get_poll_topics(
                $poll_id,
                $poll_op
        ){
            $topics_block = '';
            
            $poll_data = $this->kernel_obj->get_table('poll', "WHERE id='$poll_id'");
            $house_id = $poll_data['house_id'];
            $house_data = $this->kernel_obj->get_table('house', "WHERE id='$house_id'");
            $topics_query = $this->kernel_obj->get_table('poll_topic',"WHERE poll_id='$poll_id'",1);
            
            if($poll_data['state'] == 1){
                while($topic_data = mysqli_fetch_array($topics_query)){
                    $topics_block .= '<div class="topic_card">'.$topic_data['topic'].'</div>';
                }                
            }else if(($poll_data['state'] == 2) && ($poll_op == 1)){
                $votes_query = $this->kernel_obj->get_table('poll_topic',"WHERE poll_id='$poll_id' AND yes_tenants NOT LIKE '%#$user_id#%' AND no_tenants NOT LIKE '%#$user_id#%' AND hold_tenants NOT LIKE '%#$user_id#%'",1);
                $first = 1;
                while($topic_data = mysqli_fetch_array($votes_query)){
                    $hidden = 'hidden';
                    if($first == 1){
                        $first = 0;
                        $hidden = '';
                    }
                    
                    $topics_block .= '
                        <div class="topic_card" '.$hidden.' data-topic_id="'.$topic_data['id'].'">
                            <p>'.$topic_data['topic'].'</p>
                            <div class="form_row">
                                <div>
                                    '.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_vote','value'=>'Против','data'=>['vote'=>'no']]).'
                                </div>
                                <div>
                                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_vote','value'=>'За','data'=>['vote'=>'yes']]).'
                                </div>
                            </div>
                            '.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_vote','value'=>'Воздержаться','data'=>['vote'=>'hold']]).'
                        </div>
                        ';
                }
            }else if(($poll_data['state'] == 3) || (($poll_data['state'] == 2) && ($poll_op == 0))){                
                while($topic_data = mysqli_fetch_array($topics_query)){
                    $topics_block .= '<div class="topic_card">
                        <p>'.$topic_data['topic'].'</p>
                        <div class="progress_block">
                            <p>Голоса за: '.$topic_data['yes_votes'].'</p>
                            <progress class="progress_bar" max="'.$house_data['area_total'].'" value="'.$topic_data['yes_votes'].'"></progress>
                        </div>
                        <div class="progress_block">
                            <p>Голоса против: '.$topic_data['no_votes'].'</p>
                            <progress class="progress_bar" max="'.$house_data['area_total'].'" value="'.$topic_data['no_votes'].'"></progress>
                        </div>    
                        <div class="progress_block">
                            <p>Воздержались: '.$topic_data['hold_votes'].'</p>
                            <progress class="progress_bar" max="'.$house_data['area_total'].'" value="'.$topic_data['hold_votes'].'"></progress>
                        </div>
                    </div>';
                    
                }
            }
            
            $result = '
                <div class="panel" id="poll_topics_panel">
                    <h3>Вопросы повестки</h3>                    
                    '.$topics_block.'
                </div>
            ';
            
            return $result;
        }
        
        function vote_poll(
                $topic_id,
                $vote,
                $final = 0
        ){
            $message = '';
            $this->user_obj->open_session();
            $user_id = $_SESSION['id'];
            
            $topic_data = $this->kernel_obj->get_table('poll_topic',"WHERE id='$topic_id'");
            $poll_id = $topic_data['poll_id'];
            $poll_data = $this->kernel_obj->get_table('poll', "WHERE id='$poll_id'");
            $house_id = $poll_data['house_id'];
            
            $weight = $this->calc_vote($house_id, $user_id);            
            $vote_weight = $weight['actual']['pt'];
            
            $cur_vote = $topic_data[$vote.'_votes'];
            $new_vote = $cur_vote + $vote_weight;
            
            $new_tenants = '';
            $cur_tenants = $topic_data[$vote.'_tenants'];
            if(empty($cur_tenants)){
                $new_tenants = '#'.$user_id.'#';
            }else{
                $new_tenants = $cur_tenants.$user_id.'#';
            }            
            
            $query_arr = [
                $vote.'_votes' => $new_vote,
                $vote.'_tenants' => $new_tenants
            ];
            
            $update_topic = $this->kernel_obj->new_query('poll_topic', $query_arr,"WHERE id='$topic_id'");
            
            if($final == 1){
                $message = '
                    <h3>Спасибо за участие в голосовании!</h3>
                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_show_results','value'=>'Показать результаты']).'
                ';
            }
            
            return [
                'result' => $update_topic,
                'message' => $message
            ];
        }
    
    }
?>