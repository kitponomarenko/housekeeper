<?php
    class content{
        
        private $kernel_obj;
        private $user_obj;
        private $gui_obj;
        private $valid_obj;
        private $poll_obj;
        
        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->user_obj = new user();
            $this->gui_obj = new gui();
            $this->valid_obj = new validation();
            $this->poll_obj = new poll();
        }
        
        function get_user_id(
                $role
        ){            
            $role_id = [
                'tenant' => 1,
                'company' => 2
            ];
            $this->user_obj->open_session();
            if($_SESSION['role'] == $role_id[$role]){
                $user_id = $_SESSION['id'];
            }else{
                $user_id = '';
            }
            
            return $user_id;
        }
        
        function get_tenant_data(
                $user_id = '',
                $self = 0
        ){
            $tenant_email = '';
            $result = '';
            
            if(empty($user_id)){
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }

            $tenant_data = $this->kernel_obj->get_table('tenant',"WHERE id='$user_id'");
            if($self == 1){
               $tenant_email = '<div><p>Эл. почта</p><p>'.$tenant_data['email'].'</p></div>';
            }
            $result = '
                <h3>'.$tenant_data['lastname'].' '.$tenant_data['firstname'].' '.$tenant_data['secondname'].'</h3>
                <div class="data_grid">
                    <div><p>Дата регистрации</p><p>'.$tenant_data['date_reg'].'</p></div>
                    '.$tenant_email.'
                </div>
            ';
            
            return $result;
        }

        function get_company_data(
                $user_id = '',
                $self = 0
        ){
            $company_email = '';
            $result = '';
            
            if(empty($user_id)){
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }
            
            $company_data = $this->kernel_obj->get_table('company',"WHERE id='$user_id'");
            
            if($self == 1){
               $company_email = '<div><p>Эл. почта</p><p>'.$company_data['email'].'</p></div>';
            }
            
            $result = '
                <h3>'.$company_data['companyname'].'</h3>
                <div class="data_grid">
                    <div><p>Дата регистрации</p><p>'.$company_data['date_reg'].'</p></div>
                    '.$company_email.'
                    <div><p>ИНН</p><p>'.$company_data['reg_num'].'</p></div>
                    <div><p>Адрес</p><p>'.$company_data['adress'].'</p></div>
                </div>
            ';
            
            return $result;
        }
        
        function get_flat_data(
                $flat_id = ''
        ){
            $safe = 0;
            $title_doc = '';
            $result = '';
            
            $this->user_obj->open_session();
            $user_id = $_SESSION['id'];
            $user_role = $_SESSION['role'];
            
            $flat_data = $this->kernel_obj->get_table('tenant_property',"WHERE id='$flat_id'");
            $house_id = $flat_data['house_id'];
            $tenant_id = $flat_data['tenant_id'];
            $house_data = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            $company_id = $house_data['company_id'];
            $company_data = $this->kernel_obj->get_table('company',"WHERE id='$company_id'");
            $tenant_data = $this->kernel_obj->get_table('tenant',"WHERE id='$tenant_id'");
            
            if(($user_role == 2) && ($company_id == $user_id)){
                $safe = 1;
            }else if(($user_role == 1) && ($tenant_id == $user_id)){
                $safe = 1;
                $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_manage_flat','value'=>'Управление квартирой']).'</div>';
                $bottom_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_remove_flat','value'=>'Удалить с аккаунта']).'</div>';
            }
            
            if($safe == 1){
               $title_doc = '<div><p>Правоустанавливающий документ</p><p>'.$flat_data['title_doc'].'</p></div>';
            }
            
            if($flat_data['confirm'] == 1){
                $confirm = 'да';
            }else{
                $confirm = 'нет';
            }
            
            if($flat_data['flat_share'] == $flat_data['share_amount']){
                $share = 1;
            }else{
                $share = ''.$flat_data['flat_share'].'/'.$flat_data['share_amount'].'';
            }
            
            $vote = $this->poll_obj->calc_vote($house_id, $tenant_id);
            
            $result = '
                <h3>Квартира № '.$flat_data['flat_num'].'</h3>
                '.$top_controls.'
                <div class="section">
                    <div class="data_grid">
                        <div><p>Адрес</p><p><a href="house?id='.$house_id.'"><u>'.$house_data['adress'].'</a></u></p></div>
                        <div><p>Управляющая компания</p><p><a href="company?id='.$company_id.'"><u>'.$company_data['companyname'].'</a></u></p></div>
                        <div class="divider"></div>
                        <div><p>Собственник</p><p><a href="tenant?id='.$tenant_id.'"><u>'.$tenant_data['lastname'].' '.$tenant_data['firstname'].' '.$tenant_data['secondname'].'</a></u></p></div>
                        <div><p><b>Голосов в доме</b></p><p><b>'.$vote['actual']['pt'].' ~ '.$vote['actual']['pc'].'%</b></p></div>
                        <div><p>Голосов в доме (неподтвержденных)</p><p>'.$vote['formal']['pt'].' ~ '.$vote['formal']['pc'].'%</p></div>
                        <div class="divider"></div>
                        <div><p>Собственность подтверждена</p><p>'.$confirm.'</p></div>
                        <div><p>Площадь квартиры</p><p>'.$flat_data['flat_area'].'</p></div>
                        <div><p>Доля в квартире</p><p>'.$share.'</p></div>
                        '.$title_doc.'
                    </div>                    
                </div>
                '.$bottom_controls.'
            ';
            
            return $result;
        }
        
        function get_house_data(
                $house_id,
                $controls = 0
        ){
            $result = '';
            $house_company_id = '';
            $top_controls = '';
            $bottom_controls = '';
            
            if(empty($cur_company_id)){
                $cur_company_id = $this->get_user_id('company');
            }
            
            $house_data = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            
            $variable_arr = [
                'company_id' => 'Управляющая компания',
                'area_non_residential' => 'Площадь нежилых помещений',
                'area_common_property' => 'Площадь общей собственности',
                'area_land' => 'Площадь земельного участка',
                'area_basement' => 'Площадь подвальных помещений',
                'living_quarters_count' => 'Жилых помещений',
                'unliving_quarters_count' => 'Нежилых помещений',
                'entrance_count' => 'Подъездов',
                'floor_count_min' => 'Мин. этажей'
            ];
            
            foreach($variable_arr as $key => $val){
                if(empty($house_data[$key])){
                    ${$key} = '';
                }else{
                    if($key == 'company_id'){
                        $house_company_id = $house_data[$key];
                        $companyname = $this->kernel_obj->get_table('company', "WHERE id='$house_company_id'")['companyname'];
                        ${$key} = '<div><p>'.$val.'</p><p><a href="company?id='.$house_company_id.'"><u>'.$companyname.'</u></a></p></div>';
                    }else{
                        $status = '';
                        if(strpos($key, 'area') !== false){
                            $status = 'кв.м.';
                        }
                        ${$key} = '<div><p>'.$val.'</p><p>'.$house_data[$key].' '.$status.'</p></div>';
                    }                    
                }
            }
            
            $this->user_obj->open_session();
            $user_id = $_SESSION['id'];
            $user_role = $_SESSION['role'];
            
            if($user_role == 2){
                if(empty($house_company_id)){
                    $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_choose_house','value'=>'Выбрать этот дом']).'</div>';
                    $bottom_controls = $top_controls;
                }else{
                    if($user_id == $house_company_id){
                        if($controls == 0){
                            $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Управлять домом','data'=>['link'=>'house?id='.$house_id]]).'</div>';
                        }else{
                            $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_manage_house','value'=>'Управление домом']).'</div>';
                        }                        
                        $bottom_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_remove_house','value'=>'Удалить с аккаунта']).'</div>';
                    }
                }
            }else if($user_role == 1){
                $property_query = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$user_id' AND house_id='$house_id' AND confirm='1'");
                if((!empty($property_query)) && (!empty($house_company_id))){
                    $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_manage_house','value'=>'Управление домом']).'</div>';$safe = 1;
                } 
            }           
            
            
            $result = '
                <h3>Информация о доме</h3>
                '.$top_controls.'
                <div class="section"> 
                    <div class="data_grid">
                        <div><p>Адрес</p><p>'.$house_data['adress'].'</p></div>
                        '.$company_id.'
                        <div class="divider"></div>
                        <div><p><b>Общая площадь</b></p><p><b>'.$house_data['area_total'].' кв.м.</b></p></div>
                        <div class="divider"></div>
                        <div><p>Площадь жилых помещений</p><p>'.$house_data['area_residential'].' кв.м.</p></div>    
                        '.$area_non_residential.'
                        '.$area_common_property.'
                        '.$area_land.'
                        '.$area_basement.'
                        <div class="divider"></div>
                        <div><p><b>Всего помещений</b></p><p><b>'.$house_data['quarters_count'].'</b></p></div>
                        '.$living_quarters_count.'
                        '.$unliving_quarters_count.' 
                        <div class="divider"></div>
                        '.$entrance_count.'                
                        <div><p>Макс. этажей</p><p>'.$house_data['floor_count_max'].'</p></div>
                        '.$floor_count_min.'
                        <div class="divider"></div>
                        <div><p>Аварийное состояние</p><p>'.$house_data['is_alarm'].'</p></div>    
                    </div>
                </div>
                '.$bottom_controls.'
            ';
            
            return $result;
        }
        
        function get_company_houses(
                $user_id = '',
                $self = 0
        ){
            $result = '';
            
            if(empty($user_id)){
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }
            
            $houses_query = $this->kernel_obj->get_table('house',"WHERE company_id='$user_id'",1);
            while($house_data = mysqli_fetch_array($houses_query)){
                $house_adress = str_replace('обл. Оренбургская, ', '', $house_data['adress']);
                $result .= '
                    <div class="data_cell" name="btn_link" data-link="house?id='.$house_data['id'].'">
                        <p>'.$house_adress.'</p>
                    </div>
                ';
            }
            
            $add_house_btn = '
                    <div class="section">'.
                        $this->gui_obj->button(['id'=>'btn_add_house','class'=>'btn_green','name'=>'btn_link','value'=>'+ Добавить дом','data'=>['link'=>'add_house']])
                    .'</div>';
            
            if(empty($result)){
                if($self == 1){
                    $result = '
                        <div class="section">
                            <h3>Дома в управлении</h3>
                            <p class="txt_tiny">Для начала работы в системе необходимо добавить хотя бы один дом.</p>
                        </div>'.$add_house_btn;
                }else{
                    $result = '
                        <div class="section">
                            <h3>Дома в управлении</h3>
                            <p class="txt_tiny">Компания еще не добавила домов в сервисе.</p>
                        </div>';
                }
            }else{
                if($self == 1){
                    $result = '<h3>Дома в управлении</h3>'.$add_house_btn.'<div class="section">'.$result.'</div>';
                }else{
                    $result = '<h3>Дома в управлении</h3><div class="section">'.$result.'</div>';
                }
            }
            
            return $result;
            
        }
        
        function get_tenant_property(
                $user_id = '',
                $self = 0
        ){
            $result = '';
            
            if(empty($user_id)){
                $user_id = $this->get_user_id('tenant');
            }
            
            $flat_query = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$user_id'",1);
            while($flat_data = mysqli_fetch_array($flat_query)){
                $house_id = $flat_data['house_id'];
                $house_adress = $this->kernel_obj->get_table('house',"WHERE id='$house_id'")['adress'];
                $flat_adress = str_replace('обл. Оренбургская, ', '', $house_adress);
                $result .= '
                    <div class="data_cell" name="btn_link" data-link="flat?id='.$flat_data['id'].'">
                        <p>квартира № '.$flat_data['flat_num'].'</p>
                        <p>'.$flat_adress.'</p>
                    </div>
                ';
            }
            
            $add_property_btn = '
                    <div class="section">'.
                        $this->gui_obj->button(['id'=>'btn_add_property','class'=>'btn_green','name'=>'btn_link','value'=>'+ Добавить квартиру','data'=>['link'=>'add_property']])
                    .'</div>';
            
            if(empty($result)){
                if($self == 1){
                    $result = '
                        <div class="section">
                            <h3>Квартиры в собственности</h3>
                            <p class="txt_tiny">Для участия в собраниях собственников необходимо добавить в аккаунт хотя бы одну квартиру.</p>
                        </div>'.$add_property_btn;
                }else{
                    $result = '
                        <div class="section">
                            <h3>Квартиры в собственности</h3>
                            <p class="txt_tiny">Собственник еще не добавил квартир в сервисе.</p>
                        </div>';
                }
            }else{
                if($self == 1){
                    $result = '<h3>Квартиры в собственности</h3>'.$add_property_btn.'<div class="section">'.$result.'</div>';
                }else{
                    $result = '<h3>Квартиры в собственности</h3><div class="section">'.$result.'</div>';
                }
            }
            
            return $result;
            
        }
        
        function find_house(
                $needle,
                $active = 0
        ){
            $result = '';
            
            if($active == 0){
                $condition = "WHERE company_id IS NULL";
            }else{
                $condition = "WHERE company_id IS NOT NULL";
            }
            
            $needle_arr = explode(' ', $needle);
            $count = 0;
            foreach($needle_arr as $val){                
                ++$count;
                $val = str_replace(',', '', $val);
                $val = str_replace('.', '', $val);
                if($count == 1){
                    $needle_condition = "adress LIKE '%$val%'";
                }else{
                    $needle_condition .= " AND adress LIKE '%$val%'";
                }
            }

            if(!empty($needle_condition)){
                $condition .= " AND($needle_condition) ORDER BY id LIMIT 5";
            }
            
            $search_query = $this->kernel_obj->get_table('house',$condition,1);
            while($house_data = mysqli_fetch_array($search_query)){
                $house_adress = str_replace('обл. Оренбургская, ', '', $house_data['adress']);
                $house_company = '';
                if($active == 1){
                    $company_id = $house_data['company_id'];
                    $company_name = $this->kernel_obj->get_table('company',"WHERE id='$company_id'")['companyname'];
                    $house_company = '<p>'.$company_name.'</p>';
                }
                $result .= '
                    <div class="data_cell" name="house_search_result" data-house_id="'.$house_data['id'].'">
                        <p>'.$house_adress.'</p>
                        '.$house_company.'
                    </div>
                ';
            }
            
            return $result;
        }
        
        function add_house(
                $house_id,
                $company_id = ''
        ){
            $result = 1;
            $message = '';           
            
            if(empty($company_id)){
                $company_id = $this->get_user_id('company');
            }
            
            $check_house = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            if(!empty($check_house['company_id'])){
                $result = 0;
            }else{
                $query = $this->kernel_obj->new_query('house', ['company_id'=>$company_id], "WHERE id='$house_id'");
                $message = '
                    <h3>Дом успешно добавлен!</h3>
                    <div class="divider"></div>
                    <p>Дом успешно подключен к вашей компании! Вы можете продолжить добавлять другие дома или перейти на страницу дома для управления.<br><br> Если вы добавили этот дом случайно - нажмите "отменить".</p>
                    <div class="divider"></div>
                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Управление домом','data'=>['link'=>'house?id='.$house_id]]).'
                    '.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_remove_house','value'=>'Отменить']).'
                ';
            }
            
            return [
                    'result' => $result,
                    'message' => $message
                ];
        }
        
        function remove_house(
                $house_id,
                $company_id = ''
        ){
            $result = 1;
            $message = '';
            
            if(empty($company_id)){
                $company_id = $this->get_user_id('company');
            }
            
            $check_house = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            if($check_house['company_id'] != $company_id){
                $result = 0;
            }else{
                $this->kernel_obj->new_query('house', ['company_id'=>'NULL'], "WHERE id='$house_id'");
                $message = '
                    <h3>Дом успешно удален!</h3>
                    <div class="divider"></div>
                    <p>Дом успешно удален с аккаунта вашей компании.<br><br>Если удаление произошло случайно - нажмите "отменить".</p>
                    <div class="divider"></div>
                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Вернуться в профиль','data'=>['link'=>'company']]).' 
                    '.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_restore_house','value'=>'Отменить']).'                    
                ';
            }
            
            return [
                    'result' => $result,
                    'message' => $message
                ];
        }
        
        function restore_house(
                $house_id,
                $company_id = '',
                $controls = 0
        ){
            $result = 1;
            $house_data = '';
            
            if(empty($company_id)){
                $company_id = $this->get_user_id('company');
            }
            
            $this->kernel_obj->new_query('house', ['company_id'=>$company_id], "WHERE id='$house_id'");
            $house_data = $this->get_house_data($house_id,$controls);            
            
            return [
                    'result' => $result,
                    'house_data' => $house_data
                ];
        }
        
        function get_flat_form(
                $house_id
        ){
            $house_data = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            
            $result = '
                <h3>Добавление квартиры</h3>                
                <div class="data_grid">
                    <div><p>Адрес</p><p>'.$house_data['adress'].'</p></div>
                </div>
                <form>
                    <div class="section"> 
                        <div class="form_row">
                            <div>'.$this->gui_obj->input('num',['id'=>'flat_num','name'=>'flat_num','required'=>'required'],'номер').'</div> 
                            <div> '.$this->gui_obj->input('num',['id'=>'flat_area','name'=>'flat_area','required'=>'required','placeholder'=>'кв.м.'],'площадь').'</div>                  
                        </div>
                        '.$this->gui_obj->textarea(['id'=>'title_doc','name'=>'title_doc','required'=>'required','placeholder'=>'например, Свидетельство о праве собственности АА 1234567'],'правоустанавливающий документ').'
                    </div>
                    <div class="section">
                        <p class="txt_tiny">Если у квартиры несколько собственников, укажите свое и общее число долей в собственности.</p>
                        <div class="form_row">
                            <div>'.$this->gui_obj->input('num',['id'=>'flat_share','name'=>'flat_share','required'=>'required','value'=>"1"],'ваши доли').'</div>
                            <div>'.$this->gui_obj->input('num',['id'=>'share_amount','name'=>'share_amount','required'=>'required','value'=>"1"],'всего долей').'</div>
                        </div>
                    </div>
                    <div class="section"> 
                        '.$this->gui_obj->button(['id'=>'btn_add_property','class'=>'btn_green','name'=>'btn_submit','value'=>'Добавить квартиру']).'
                    </div>
                </form>
            ';
                    
            return $result;
        }
        
        function add_flat(
                $house_id,
                $form,
                $tenant_id = ''
        ){
            $result = [];
            $valid = 1;
            $message = '';
            $query_arr = [];
            
            foreach($form as $entity){
                $entity_check = $this->valid_obj->validate_entity($entity);                
                $result['inputs'][$entity['id']] = $entity_check;
                $query_arr[$entity['type']] = $entity_check['value'];
                if($valid == 1){
                    $valid = $entity_check['valid'];
                }
            }
            
            if($valid == 1){
                $query_arr['house_id'] = $house_id;
                if(empty($tenant_id)){
                    $tenant_id = $this->get_user_id('tenant');
                }
                $query_arr['tenant_id'] = $tenant_id;
 
                $add_flat = $this->kernel_obj->new_query('tenant_property',$query_arr);
                $flat_id = $add_flat['id'];
                $message = '
                    <h3>Квартира успешно добавлена!</h3>
                    <div class="divider"></div>
                    <p>Квартира успешно добавлена. Как только ваша УК подтвердит подлинность права собственности, вы сможете принимать участие в общих собраниях. Вы также можете сразу добавить еще одну квартиру в этом доме.<br><br>Если вы добавили эту квартиру случайно - нажмите "отменить".</p>
                    <div class="divider"></div>
                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_repeat_flat','value'=>'Добавить еще квартиру']).'
                ';
                $result['message'] = $message;
            }
            $result['valid'] = $valid;
            
            return $result;
        }
        
        function remove_flat(
                $flat_id,
                $tenant_id = ''
        ){
            $result = 1;
            $message = '';
            
            if(empty($tenant_id)){
                $tenant_id = $this->get_user_id('tenant');
            }
            
            $check_flat = $this->kernel_obj->get_table('tenant_property',"WHERE id='$flat_id'");
            if($check_flat['tenant_id'] != $tenant_id){
                $result = 0;
            }else{
                $this->kernel_obj->remove_row('tenant_property', $flat_id);
                $message = '
                    <h3>Квартира успешно удалена!</h3>
                    <div class="divider"></div>
                    <p>Квартира успещно удалена с вашего аккаунта.</p>
                    <div class="divider"></div>
                    '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Вернуться в профиль','data'=>['link'=>'tenant']]).'                    
                ';
            }
            
            return [
                    'result' => $result,
                    'message' => $message
                ];
        }
        
        function confirm_flat(
                $flat_id,
                $state
        ){
            if($state == 'true'){
                $state = 1;
            }else{
                $state = 0;
            }
            
            $this->kernel_obj->new_query('tenant_property', ['confirm'=>$state], "WHERE id='$flat_id'");
            
            return $state;
        }
        
        function get_house_management_panel(
                $house_id
        ){
            $safe = 0;
            $house_initiatives = '';
            $confirmed_tenants = '';
            $unconfirmed_tenants = ''; 
            $tenants_block = '';
            
            $this->user_obj->open_session();
            $user_id = $_SESSION['id'];
            $user_role = $_SESSION['role'];
            
            $house_data = $this->kernel_obj->get_table('house',"WHERE id='$house_id'");
            
            if($user_role == 2){
                if($user_id == $house_data['company_id']){
                    $safe = 2;
                }
            }else if($user_role == 1){
                $property_query = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$user_id' AND house_id='$house_id' AND confirm='1'");
                if(!empty($property_query)){
                    $safe = 1;
                }                
            }
            
            if($safe == 2){
                $house_initiatives .= '
                    <div class="section">
                        '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Инициировать собрание','data'=>['link'=>'create_poll?id='.$house_id]]).'
                    </div>';
                
                $flats_arr = [
                    'confirmed_tenants' => 1,
                    'unconfirmed_tenants' => 0
                ];

                foreach($flats_arr as $key => $val){ 
                    $checked = '';                
                    $flats_query = $this->kernel_obj->get_table('tenant_property',"WHERE house_id='$house_id' AND confirm='$val'",1);
                    while($flat_data = mysqli_fetch_array($flats_query)){
                        $tenant_id = $flat_data['tenant_id'];
                        $tenant_data = $this->kernel_obj->get_table('tenant',"WHERE id='$tenant_id'");
                        $flat_title = 'Квартира №'.$flat_data['flat_num'].' / '.$tenant_data['lastname'].' '.$tenant_data['firstname'];
                        ${$key} .= $this->gui_obj->checkbox(['name'=>'tenant_confirm_cb','placeholder'=>$flat_title,'data'=>['flat_id'=>$flat_data['id']]],$val);
                    }
                    
                    $tenants_block = '
                    <div class="section">
                        <h5>Собственники в доме</h5>
                        '.$this->gui_obj->btn_roll('показать','скрыть','unconfirmed_tenants',['placeholder'=>'Неподтвержденные собственники']).'
                        <div id="unconfirmed_tenants" hidden>'.$unconfirmed_tenants.'</div>
                        '.$this->gui_obj->btn_roll('показать','скрыть','confirmed_tenants',['placeholder'=>'Подтвержденные собственники']).'
                        <div id="confirmed_tenants" hidden>'.$confirmed_tenants.'</div>
                    </div>';
                }
            }
            
            if($safe >= 1){
                $polls = $this->poll_obj->get_actual_polls($house_id);
                $old_polls = $this->poll_obj->get_old_polls($house_id);
                $house_initiatives .= '
                    <div class="section">
                        '.$polls['active'].'
                        '.$polls['upcoming'].'
                    </div>
                    <div class="section">
                        '.$this->gui_obj->btn_roll('показать','скрыть','old_polls',['placeholder'=>'Старые собрания']).'
                        <div id="old_polls" hidden>'.$old_polls.'</div>
                    </div>';      
                
            }
            
            $result = '
                <div class="panel" id="house_management_panel">
                    <h3>Управление домом</h3>                    
                    '.$house_initiatives.'
                    '.$tenants_block.'
                </div>
            ';
            
            return $result;
        }
        
        function get_flat_management_panel(
                $flat_id,
                $tenant_id = ''
        ){
            $house_initiatives = '';
            $tenant_tickets = '';
            
            if(empty($tenant_id)){
                $tenant_id = $this->get_user_id('tenant');
            }
            
            $flat_data = $this->kernel_obj->get_table('tenant_property',"WHERE id='$flat_id'");
            $house_id = $flat_data['house_id'];
            if($flat_data['confirm'] == 1){
                $polls = $this->poll_obj->get_actual_polls($house_id);
                $house_initiatives = '
                    <div class="section">
                        '.$polls['active'].'
                        '.$polls['upcoming'].'
                    </div>
                    <div class="section">
                        <h5>Действия</h5>
                        '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Инициировать собрание','data'=>['link'=>'create_poll?id='.$house_id]]).'
                        '.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Предложить инициативу','data'=>['link'=>'initiative']]).'
                    </div>                    
                ';
            }else{
                $house_initiatives = '
                    <div class="section">
                        <p class="txt_tiny">К сожалению, собственность на данную квартиру еще не была потверждена в нашем сервисе.<br><br>Пожалуйста, обратитесь в свою управляющую компанию.</p>
                    </div>
                ';
            }
            
            $tickets_query = $this->kernel_obj->get_table('ticket',"WHERE flat = '$flat_id'",1);
            while($ticket_data = mysqli_fetch_array($tickets_query)){                
                $tenant_tickets .= '';
            } 
            
            $result = '
                <div class="panel" id="flat_management_panel">
                    <h3>Управление квартирой</h3>
                    '.$house_initiatives.'
                    <div class="section">
                        '.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_link','value'=>'Обратиться в УК','data'=>['link'=>'create_ticket']]).'
                    </div>                    
                    <div class="section">
                        '.$this->gui_obj->btn_roll('показать','скрыть','tenant_tickets',['placeholder'=>'Обращения в УК']).'
                        <div id="tenant_tickets" hidden>'.$tenant_tickets.'</div>
                        
                    </div>
                </div>
            ';
            
            return $result;
        }
            
    }
        
?>