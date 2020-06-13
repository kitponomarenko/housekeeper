<?php
    class constructor{
        
        private $kernel_obj;
        private $user_obj;

        function __construct(){
            spl_autoload_register(function ($class_name){include 'lib/'.$class_name . '.php';});
            $this->kernel_obj = new kernel();
            $this->user_obj = new user();
        }
        
        
        function get_page_data(){
            $page_title = '';
            $page_description = '';
            $page_keywords = '';
            $page_css = '';
            $page_scripts = '';
            $content = '';
            $redirect = '';
            
            $page = $this->kernel_obj->get_current_page($_SERVER['REQUEST_URI']);
            if(empty($page)){
                $page = 'index';
            }
            
            $page_data = $this->kernel_obj->get_table('page', "WHERE url='$page'");
            $user_session = $this->user_obj->check_session();
            $user_access = $this->user_obj->check_access($user_session['role'],$page_data['access']);
            $access_id = $user_session['role'];
            $user_tbl_name = $this->kernel_obj->get_table('user', "WHERE id='$access_id'")['tbl_name'];
            
            if($user_access == true){
                if(!empty($page_data)){
                    $page_title = $page_data['title'];
                    if(empty($page_data['parent_url'])){
                        $page_active = $page;
                        $page_active_title = $page_title;

                        $page_css_arr = explode(' ', $page_data['css']);
                        $page_scripts_arr = explode(' ', $page_data['js']);
                    }else{
                        $page_active_result = $kernel_obj->get_table('table',"WHERE url = '$page_data[parent_url]'");
                        $page_active = $page_active_data['url'];
                        $page_active_title = $page_active_data['title'];

                        $page_base_css_arr = explode(' ', $page_data['css']);
                        $page_parent_css_arr = explode(' ', $page_parent_data['css']);
                        $page_css_arr = array_merge($page_parent_css_arr,$page_base_css_arr);
                        $page_css_arr = $kernel_obj->clean_up_array($page_css_arr);

                        $page_base_scripts_arr = explode(' ', $page_data['js']);
                        $page_parent_scripts_arr = explode(' ', $page_parent_data['js']);
                        $page_scripts_arr = array_merge($page_parent_scripts_arr,$page_base_scripts_arr);
                        $page_scripts_arr = $kernel_obj->clean_up_array($page_scripts_arr);
                    }

                    if(file_exists($this->kernel_obj->root_path.'/css/'.$page.'.css')){
                        $page_css = '<link rel="stylesheet" href="css/'.$page.'.css">';
                    }

                    if($page_css_arr){
                        foreach($page_css_arr as $css){
                            if(($css != '') && ($css != $page)){
                                $page_css .= '<link rel="stylesheet" href="css/'.$css.'.css">';
                            }
                        }
                    }

                    if(file_exists($this->kernel_obj->root_path.'/js/'.$page.'.js')){
                        $page_scripts='<script src="js/'.$page.'.js"></script>';
                    }

                    if($page_scripts_arr){
                        foreach($page_scripts_arr as $script){
                            if(($script != '') && ($script != $page)){
                                $page_scripts .= '<script src="js/'.$script.'.js"></script>';
                            }
                        }
                    }                   
                    
                }            
                
                if(!empty($page_data['content_src'])){
                    $content_state = 1;
                    if(isset($_GET['id'])){
                        $content_id = $_GET['id'];
                        $content = $this->kernel_obj->get_table($page_data['content_src'],"WHERE id='$content_id'");
                        if(empty($content)){
                            $content_state = 0;
                        }
                    }else{
                        $content_state = 0;
                    }
                    
                    if($content_state == 0){
                        if(($page == 'tenant') || ($page == 'company')){
                            if($user_tbl_name == $page){
                                $content_state = 1;
                                $content_id = $user_session['user']['id'];
                                $content = $this->kernel_obj->get_table($page_data['content_src'],"WHERE id='$content_id'");
                            }else{
                                $content_state = 0;
                                $redirect = $user_tbl_name;
                            }
                        }else{
                           if(!empty($page_data['parent_url'])){
                                $redirect = $page_active;
                            }else{
                                $redirect = $user_tbl_name;
                            } 
                        }                    
                    }else{
                        if($page_data['meta_inherit'] == 1){
                            $page_title = $content['title'];
                            $page_description = $content['description'];
                            $page_keywords = $content['keywords'];
                        }
                    } 
                    
                }
                
            }else{                    
                $redirect = $user_tbl_name;
            }  
            
            return [
                'title' => $page_title,
                'description' => $page_description,
                'keywords' => $page_keywords,
                'css' => $page_css,
                'scripts' => $page_scripts,
                'session' => $user_session, 
                'content' => $content,
                'redirect' => $redirect
                ];
        }
        
    }

?>