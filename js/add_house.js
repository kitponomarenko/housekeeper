$(document).on('click','[name="house_search_result"]', function(){
    $('[name="house_search_result"]').removeClass('data_cell_active');
    let house_id = $(this).data("house_id");
    $('#house_panel').data("house_id", house_id);
    $(this).addClass('data_cell_active');
    $.when(run_method('content','get_house_data',[house_id])).done(function(data){      
        $('#house_panel').html(data);
        if($('#house_panel').is(":hidden")){
            $('#house_panel').show(200);
        }
        $("html,body").scrollTop($('#house_panel').offset().top);
    });
});

$(document).on('click','[name="btn_choose_house"]', function(){
    let house_id = $('#house_panel').data("house_id");
    $.when(run_method('content','add_house',[house_id])).done(function(data){      
        if(data['result'] == 1){
            $('#house_panel').html(data['message']);
        }
    });
});

$(document).on('click','[name="btn_remove_house"]', function(){
    let house_id = $('#house_panel').data("house_id");
    $.when(run_method('content','remove_house',[house_id])).done(function(data){      
        if(data['result'] == 1){
            $('#house_panel').html(data['message']);
        }
    });
});