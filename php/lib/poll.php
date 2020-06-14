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
            
        }
    
    }
?>