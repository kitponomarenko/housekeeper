//----- VARIABLES BLOCK -----

var pop_up_state = 0; // handles state of any pop-up window on the page

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
    })
}

//----- INPUTS BLOCK -----

//----- inputs validation -----

$(document).on('change','input:not([id="password_auth"])', function(){
    validate_input($(this));
});

$(document).on('change','input[id="password_auth"]', function(){
    let form = $(this).parents('form')
    validate_password(form);
});

$(document).on('click','[name="btn_submit"]', function(){
    serialize_form($(this).parent('form'));
});

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
    form_inputs = $(form).find('input:not([type="button"])');
    form_data = [];
    $.each(form_inputs, function(key, value) {
        if($(value).attr('type') != 'button'){
            input_data = serialize_input($(value));
            form_data.push(input_data);
        }
    })

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

//----- numeric inputs ------
function numeric_input_op(btn,action){
    let num_input = $(btn).parent('div').prev('input');
    let counter_base_val = parseFloat($(num_input).val());
    if(isNaN(counter_base_val)){
        counter_base_val = 0;
    }
    let counter_step = parseFloat($(num_input).attr('step'));
    if(isNaN(counter_step)){
        counter_step = 1;
    }
    if(action == 'plus'){
        counter_base_val = parseFloat(counter_base_val + counter_step);
        if(counter_base_val>parseFloat($(num_input).attr('max'))){
            counter_base_val=parseFloat($(num_input).attr('max'));
        }
    }else if(action == 'minus'){
        counter_base_val = parseFloat(counter_base_val - counter_step);
        if(counter_base_val<parseFloat($(num_input).attr('min'))){
            counter_base_val=parseFloat($(num_input).attr('min'));
        }
    }
    counter_base_val = counter_base_val.toFixed(1);
    
    $(num_input).val(counter_base_val);
    $(num_input).trigger('change');
}

$(document).on('click','.input_num>div>button:contains("-")', function(){
    if(pop_up_state===0){
        numeric_input_op($(this),'minus');
    }
});

$(document).on('click','.input_num>div>button:contains("+")', function(){
    if(pop_up_state===0){
        numeric_input_op($(this),'plus');
    }
});

$(document).on('change','.input_counter>input', function(){
    let min = parseFloat($(this).attr('min'));
    let max = parseFloat($(this).attr('max'));
    let val = parseFloat($(this).val());
    if((val ==='') || (val < min)){
        $(this).val(min)
    }else if(val > max){
        $(this).val(max)
    }
});

$('.password_switch').click(function(){
    if($(this).hasClass('password_shown')){
        $(this).removeClass('password_shown');
        $(this).prev('input').attr('type','password');
    }else{
        $(this).addClass('password_shown');
        $(this).prev('input').attr('type','text');
    }
})


$(document).on('click','#user_types>.btn_radio', function(){
    btn_radio($(this));
    let current_type = $(this).data("user_type");
    $('#login_auth').data("input_src",current_type);
    $('#password_auth').data("input_src",current_type);
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



//----- POP UP BLOCK -----
//----- pop up base functions -----

function fetch_pop_up(lib,method,params){
    $.when(run_method(lib,method,params)).done(function(data){
        if(data != ''){
            if(pop_up_state == 0){
                if(method != 'message'){
                    $('#pop_up').css('height','100vh');
                    $('.pop_up_fader').css('top',$('html').scrollTop());
                    $('.pop_up_fader').fadeIn(200);
                }
                $('#pop_up').html(data);
                $('#pop_up').slideDown(200);
                $('#pop_up').css('display','flex');
                $('html').css('overflow','hidden');
                $('html').css('width','100%');
                pop_up_state = 1;
            }
        }
    });
}

function close_pop_up(){
    if(pop_up_state == 1){
        $('#pop_up').slideUp(200);
        $('#pop_up').html('');
        $('#pop_up').css('height','auto');
        $('.pop_up_fader').fadeOut(200);
        $('html').css('overflow','auto');
        $('html').css('position','static');
        pop_up_state = 0;
    }
}

//----- pop up stock calls -----
$('#pop_up').on('click','[name="close_pop_up"]', function(){
    close_pop_up();
});