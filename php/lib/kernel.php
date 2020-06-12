<?php
    class kernel{
        
        public $config; //gives access to config.ini params
        public $root_path; // site full root path
        public $user_obj;
        
        //initializing base public vars
        function __construct(){
            $this->config = parse_ini_file('config.ini');
            $this->root_path = $_SERVER['DOCUMENT_ROOT'].'/'.$this->config['site_dir'];
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
        }
        
        
        //function sets up connection to app database via '$type' database account
        function create_link(
            string $type // type of database connection
        )
        {
            $host = $this->config['host'];
            $database = $this->config['database_name'];
            $login_link = $this->config[$type.'_login'];
            $password_link = $this->config[$type.'_password'];
            $link = mysqli_connect("$host","$login_link","$password_link","$database");
            mysqli_set_charset($link, "utf8");
            return $link;
        }
        
        
        //function for quick database requests
        function get_table(
            $tbl_name = '',
            $condition = '',
            $query = 0
        )
        {
            $link = $this->create_link('reader');
            $tbl_query = mysqli_query($link, "SELECT * FROM $tbl_name $condition");
            if($query == 0){
                $tbl_result = mysqli_fetch_array($tbl_query);
                $result = $tbl_result;
            }else{
                $result = $tbl_query;
            };
            return $result;
        }
        
        //function for safe creation or update of database records
        function new_query(
            $tbl_name,
            $query_array,
            $condition = ''
        )
        {
            $new_query = "";
            $counter = 0;
            foreach($query_array as $query_key => $query_val){
                if(($query_val != '') && ($query_val != NULL)){
                    ++$counter;
                    $query_val = stripslashes($query_val);
                    $query_val = htmlspecialchars($query_val);
                    $query_val = trim($query_val);
                    if($counter == 1){
                        $new_query .= "SET ";
                    }else{
                        $new_query .= ", ";
                    }
                    if($query_val == 'NULL'){
                        $new_query .= "$query_key=$query_val";
                    }else{
                        $new_query .= "$query_key='$query_val'";
                    }
                }
            }
            $link = $this->create_link('writer');
            if($condition==''){
                $query_action = 'INSERT INTO';
            }else{
                $query_action = 'UPDATE';
            }
            $result  =mysqli_query($link, "$query_action $tbl_name $new_query $condition");
            $row_id = mysqli_insert_id($link);
            return [
                'result' => $result,
                'id' => $row_id
            ];
        }
        
        
        //function to set the first letter of the string uppercase
        function set_uppercase(
            string $string = ''
        ){
            $string=mb_substr(mb_strtoupper($string, 'utf-8'), 0, 1, 'utf-8').mb_substr(mb_strtolower($string, 'utf-8'), 1, mb_strlen($string)-1, 'utf-8');
            return($string);
        }
        
        
        //function for qucik baking of query-conditions from the array       
        function array_to_query(
            $array,
            $param='id'
        )
        {
            $content_arr = explode("%", $array);
            $first_el = 1;
            $content_search_arr = [];
            foreach ($content_arr as $arr_el){
                if($arr_el != ''){
                    if($first_el == 1){
                        $arr_el = "$param LIKE '$arr_el'";
                        $first_el = 0;
                    }else{
                        $arr_el = "OR $param LIKE '$arr_el'";
                    }
                    array_push($content_search_arr, $arr_el);
                }
            }
            $content_search=implode(" ", $content_search_arr);
            return $content_search;
        }
        
        
        //function to remove multiply equal elements from arrays
        function clean_up_array(
            $array
        ){
            foreach($array as $base_val){
                $counter = 0;
                foreach($array as $mask_key => $mask_val){
                    if($base_val == $mask_val){
                        ++$counter;
                        if($counter > 1){
                            unset($array[$mask_key]);
                        }
                    }
                }
            }
            
            return $array;
        }
        
        
        //function to get clean page name
        function get_current_page(
            $url
        )
        {
            $page_arr = explode('/',explode('?',$url)[0]);
            $page = array_pop($page_arr);
            if($page == ''){
                $page='index';
            }
            return($page);
        }
        
        function generate_code(
            $length = 10,
            $char_set='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ){
            $code = '';
            $max_count = mb_strlen($char_set, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $code .= $char_set[random_int(0, $max_count)];
            }
            return $code;
        }
        
    }
?>