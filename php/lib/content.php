<?php
    class content{
        
        private $kernel_obj;
        private $user_obj;
        private $gui_obj;
        
        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->user_obj = new user();
            $this->gui_obj = new gui();
        }
        
        function get_company_id(){            
            $this->user_obj->open_session();
            if($_SESSION['role'] == 2){
                $company_id = $_SESSION['id'];
            }else{
                $company_id = '';
            }
            
            return $company_id;
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
        
        function get_house_data(
                $house_id,
                $cur_company_id = '',
                $controls = 0
        ){
            $result = '';
            $house_company_id = '';
            $top_controls = '';
            $bottom_controls = '';
            
            if(empty($cur_company_id)){
                $cur_company_id = $this->get_company_id();
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
                        if(strpos($key, 'area')){
                            $status = 'кв.м.';
                        }
                        ${$key} = '<div><p>'.$val.'</p><p>'.$house_data[$key].' '.$status.'</p></div>';
                    }                    
                }
            }
            
            if(!empty($cur_company_id)){
                if(empty($house_company_id)){
                    $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_choose_house','value'=>'Выбрать этот дом']).'</div>';
                    $bottom_controls = $top_controls;
                }else{
                    if($cur_company_id == $house_company_id){
                        if($controls == 0){
                            $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_link','value'=>'Управлять домом','data'=>['link'=>'house?id='.$house_id]]).'</div>';
                        }else{
                            $top_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_green','name'=>'btn_manage_house','value'=>'Управлять домом']).'</div>';
                        }                        
                        $bottom_controls = '<div class="section">'.$this->gui_obj->button(['class'=>'btn_border','name'=>'btn_remove_house','value'=>'Удалить с аккаунта']).'</div>';
                    }
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
                $this->user_obj->open_session();
                $user_id = $_SESSION['id'];
            }
            
            $flat_query = $this->kernel_obj->get_table('tenant_property',"WHERE tenant_id='$user_id'",1);
            while($flat_data = mysqli_fetch_array($flat_query)){
                $house_id = $flat_data['house_id'];
                $house_adress = $this->kernel_obj->get_table('house',"WHERE id='$flat_data'");
                $flat_adress = str_replace('обл. Оренбургская, ', '', $house_adress);
                $result .= '
                    <div class="data_cell" name="tenant_property" data-property_id="'.$flat_data['id'].'">
                        <p>квартира №'.$flat_data['num'].'</p>
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
                    $result = $add_property_btn.'<div class="section>'.$result.'</div>';
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
                    $company_id = $house_data['id'];
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
                $company_id = $this->get_company_id();
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
                $company_id = $this->get_company_id();
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
                $company_id = $this->get_company_id();
            }
            
            $this->kernel_obj->new_query('house', ['company_id'=>$company_id], "WHERE id='$house_id'");
            $house_data = $this->get_house_data($house_id, $company_id,$controls);            
            
            return [
                    'result' => $result,
                    'house_data' => $house_data
                ];
        }
        
    }
        
?>