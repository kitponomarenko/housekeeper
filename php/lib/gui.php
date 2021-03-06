<?php
    class gui{
        
        private $kernel_obj;
        
        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
        }
        

        function input(
            $type = 'text', // type of input to construct, default - 'text'
            $params = [],
            $label = '', // keep empty to skip
            $validate = 1 // 0 for no-validation input, 1 for input that need validation before sent
        ){
            
            //example of $params array
            //$params = [
            //    'class' => 'class_name',
            //    'id' => 'input_id',
            //    'name' => 'input_name',
            //    'value' => 'input_value',
            //    'placeholder' => 'input_placeholder',
            //    'data' => [
            //        'param_a' => 'param_a_value'    
            //    ]
            //];
            
            
            // validating chosen type of input trough avaible types array
            $valid = 0;
            $types_avaible = explode(', ', $this->kernel_obj->config['input_types']);
            
            foreach($types_avaible as $type_avaible){
                if($type == $type_avaible){
                    $valid = 1;
                }
            }
            
            if ($valid == 0){
                $result = 'invalid input type';
            }else{
                $input_params = '';
                $input_value = '';
                $input_placeholder = '';
                $input_label = '';
                
                foreach($params as $param_key => $param_val){
                    if($param_key == 'data'){
                        foreach($param_val as $data_key => $data_val){
                            $input_params .= 'data-'.$data_key.'="'.$data_val.'"';
                        }
                    }else if(($param_key == 'value') || ($param_key == 'placeholder')){
                        if(!empty($param_val)){
                            $input_params .= ''.$param_key.'="'.$param_val.'"';
                            ${'input_'.$param_key} = $param_val;
                        }
                    }else if(($param_key == 'value') && ($type == 'checkbox')){
                        if($param_val == 1){
                            $input_params .= 'checked';
                        }
                    }else{
                        if(($param_val == '') || ($param_key == $param_val)){
                            $input_params .= ' '.$param_key.'  ';
                        }else{
                            $input_params .= ''.$param_key.'="'.$param_val.'"';
                        }
                    }
                }
                
                if(empty($params['name'])){
                    $input_params .= 'name="'.$type.'"';
                }
                
                if(!empty($label)){                    
                    $label_for = '';
                    if(!empty($params['id'])){
                        $label_for = 'for="'.$params['id'].'"';
                    }                    
                    
                    $input_label = '<label '.$label_for.'>'.$label.'</label>';
                }
                
                $input_params .= 'data-input_validate="'.$validate.'"';
                
                // constructing chosen input type
                if($type == 'textarea'){
                    $result = '
                        '.$input_label.'
                        <textarea '.$input_params.'>'.$input_value.'</textarea>
                    ';
                }else if($type == 'checkbox'){
                    $result = '
                        <label class="checkbox">
                            <input type="checkbox" class="checkbox_input" '.$input_params.' hidden>
                            <div class="checkbox_mark"></div>
                            <p class="checkbox_label">'.$input_placeholder.'</p>
                        </label>
                    ';
                }else if($type == 'num'){
                    $result = '
                        '.$input_label.'
                        <input type="number" '.$input_params.'>
                    ';                
                }else if($type == 'btn_radio'){
                    $result = '
                        <div '.$input_params.'>'.$input_value.'</div>
                        ';
                }else if($type == 'btn_roll'){
                    $result = '
                        <div '.$input_params.'><p>'.$input_placeholder.'</p><div>'.$input_value.'</div></div>
                        ';
                }else if($type == 'password'){
                    $result = '
                        <div class="input_password">
                            '.$input_label.'
                            <input type="'.$type.'" '.$input_params.'>
                            <div class="password_switch"></div>
                        </div>
                    ';
                }else if($type == 'select'){
                    $result = '
                        '.$input_label.'
                        <select '.$input_params.'>
                    ';
                }else{
                    if($type == 'name'){
                        $type = 'text';
                    }
                    
                    $result = '
                        '.$input_label.'
                        <input type="'.$type.'" '.$input_params.'>
                    ';
                }
                
            }
            return $result;
        }
        
        //quick-access function to generate buttons via input function
        function button(
            $params = []
        ){
            return $this->input('button',$params);
        }
        
        //quick-access function to generate buttons for roll-up content via input function
        function btn_roll(
            $value = '',
            $value_alt = '',
            $block_id = '',
            $params = []
        ){
            $params['value'] = $value;
            $params['name'] = 'btn_roll';
            $params['class'] = 'btn_roll';
            $params['data']['value'] = $value;
            $params['data']['value_alt'] = $value_alt;
            $params['data']['roll_id'] = $block_id;
            $params['data']['btn_state'] = 0;
            return $this->input('btn_roll',$params);
        }       
       
        function btn_radio(
            $params = [],
            $active = 0
        ){
            $params['class'] = 'btn_radio';
            if($active == 1){
                $params['class'] = 'btn_radio btn_radio_active';
            }
            return $this->input('btn_radio',$params);
        }
        
        //quick-access function to generate numeric inputs via input function
        function input_num(
            $params = [],
            $label = '',
            $validate = 1
        ){
            return $this->input('input_num',$params,$label,$validate);
        }
        
        //quick-access function to generate checkbox via input function
        function checkbox(
            $params = [],
            $checked = 0,
            $validate = 0
        ){
            if($checked == 1){
                $params['checked'] = 'checked';
            }
            return $this->input('checkbox',$params,'',$validate);
        }
        
        //quick-access function to generate textarea via input function
        function textarea(
            $params = [],
            $label = '',
            $validate = 1
        ){
            return $this->input('textarea',$params,$label,$validate);
        }
        
    }

?>