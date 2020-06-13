$(document).on('click','[name="house_search_result"]', function(){
    $('[name="house_search_result"]').removeClass('data_cell_active');
    let house_id = $(this).data("house_id");
    $('#flat_panel').data("house_id", house_id);
    $(this).addClass('data_cell_active');
    get_flat_form(house_id);
});

function get_flat_form(house_id){
    $.when(run_method('content','get_flat_form',[house_id])).done(function(data){      
        $('#flat_panel').html(data);
        if($('#flat_panel').is(":hidden")){
            $('#flat_panel').show();
        }
        $("html,body").scrollTop($('#flat_panel').offset().top);
    });
}

$(document).on('click','[name="btn_repeat_flat"]', function(){
    let house_id = $('#flat_panel').data("house_id");
    get_flat_form(house_id)
});

$(document).on('click','#btn_add_property', function(){
    let form = $(this).parents('form');
    let form_data = serialize_form(form);
    let house_id = $('#flat_panel').data("house_id");
    $.when(run_method('content','add_flat',[house_id,form_data])).done(function(data){      
        update_form_errors(data['inputs']);
        if(data['valid'] == 1){
            $('#flat_panel').html(data['message']);
        }
    });
});