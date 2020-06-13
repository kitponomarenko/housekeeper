//----- REQUEST BLOCK -----

function run_method(lib,method,params){
    return $.ajax({
        type: "POST",
        url: "php/reciever_method",
        data: {
            'class':lib,
            'method':method,
            'params':params
        },
        dataType: 'json',
        success: function(data){}
    });
}

//----- INPUTS BLOCK -----

//----- inputs validation -----

$(document).on('change','input:not([id="password_auth"])', function(){
    validate_input($(this));
});

$(document).on('change','textarea', function(){
    validate_input($(this));
});

$(document).on('change','input[id="password_auth"]', function(){
    let form = $(this).parents('form');
    validate_password(form);
});

$(document).on('click','#btn_auth', function(){
    let params = bake_form($(this));
    $.when(run_method('user','user_auth',params)).done(function(data){
        update_form_errors(data['inputs']);
        if(data['valid'] == 1){
            $(location).attr('href',data['redirect']);
        }
    });
});

$(document).on('click','#btn_reg', function(){
    let params = bake_form($(this));
    $.when(run_method('user','user_reg',params)).done(function(data){
        update_form_errors(data['inputs']);
        if(data['valid'] == 1){            
            $('.content').html(data['message']);
        }
    });
});

function bake_form(btn){
    let form = $(btn).parents('form');
    let src = $(form).data("form_src");
    let form_data = serialize_form(form);
    let params = [src, form_data];
    
    return params;
}

function serialize_input(input){
    input_type = $(input).attr('name');
    input_src = $(input).data('input_src');
    input_id = $(input).attr('id');
    input_validate = $(input).data('input_validate');
    input_required = $(input).prop('required');
    if($(input).attr('type') == 'checkbox'){
        input_value = $(input).prop('checked');
    }else{
       input_value = $(input).val(); 
    }
    
    input_data = {
        'type' : input_type,
        'src' : input_src,
        'id' : input_id,
        'required' : input_required,
        'validate' : input_validate,
        'value' : input_value
    };
    
    return input_data;
}

function serialize_form(form){
    form_inputs = $(form).find('input:not([type="button"]):visible, textarea:visible');
    form_data = [];
    $.each(form_inputs, function(key, value) {
        if($(value).attr('type') != 'button'){
            input_data = serialize_input($(value));
            form_data.push(input_data);
        }
    });

    return form_data;
}

function validate_input(input){
    input_data = serialize_input($(input));
    $.when(run_method('validation','validate_entity',[input_data])).done(function(data){
        update_input_errors(data['error'],$(input));
    });
}

function validate_password(form){
    form_data = serialize_form(form);
    $.when(run_method('validation','validate_auth',[form_data])).done(function(data){        
        update_form_errors(data['inputs']);
    });
}

function update_input_errors(error,input){
    let pos_el = input;
    if($(input).prev('label:not(.checkbox)').length > 0){
        pos_el = $(input).prev('label:not(.checkbox)');
    }
    if(error!==''){
        $(pos_el).prev('.error_bar').remove();
        $(pos_el).before('<p class="error_bar">'+error+'</p>');
        $(input).addClass('error_input');
    }else{
        $(pos_el).prev('.error_bar').remove();
        $(input).removeClass('error_input');
    }
}

function update_form_errors(data){
    $.each(data, function(key, value) {
        update_input_errors(value['error'],$('#'+key+''));
    });
}

$('.password_switch').click(function(){
    if($(this).hasClass('password_shown')){
        $(this).removeClass('password_shown');
        $(this).prev('input').attr('type','password');
    }else{
        $(this).addClass('password_shown');
        $(this).prev('input').attr('type','text');
    }
});

$(document).on('click','[name="btn_link"]', function(){
    $(location).attr('href',$(this).data("link"));
});

$(document).on('click','#user_types>.btn_radio', function(){
    btn_radio($(this));
    let current_type = $(this).data("user_type");
    $('#login_auth').data("input_src",current_type);
    $('#password_auth').data("input_src",current_type);
    let form = $(this).parents('form');
    $(form).data("form_src",current_type);
    validate_password(form);
});

$(document).on('click','#user_reg_types>.btn_radio', function(){
    btn_radio($(this));
    let current_type = $(this).data("user_type");
    $('#login_reg').data("input_src",current_type);
    let form = $(this).parents('form');
    $(form).data("form_src",current_type);
    $('[name="reg_inputs"]').hide(100);
    $('#reg_'+current_type+'').show(200);
});

function btn_radio(btn){
    $(btn).siblings().removeClass('btn_radio_active');
    $(btn).addClass('btn_radio_active');
}

$('[name="btn_roll"]').click(function(){
    if ($(this).data("btn_state") == 0){
        $(this).html($(this).data("name_alt"));
        $('#'+$(this).data("roll_id")).show(200);
        $(this).data("btn_state", 1);
    } else{
        $(this).html($(this).data("name"));
        $('#'+$(this).data("roll_id")).hide(200);
        $(this).data("btn_state", 0);
    }
});

$("#house_search").keyup(function() {    
    let needle = $(this).val();
    let length = needle.length;
    
    if (length > 2){
        let active = $(this).data("search_active");
        $.when(run_method('content','find_house',[needle,active])).done(function(data){        
            $('#house_search_reciever').html(data);
        });
    } else{
        $('#house_search_reciever').html('');
    }
});