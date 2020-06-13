$(document).on('click','[name="btn_manage_house"]', function(){
    $(this).toggleClass('btn_green btn_border');
    if($('.content').find('#house_management_panel').length > 0){
        $(this).val('Управлять домом');
        $('#house_management_panel').remove();
    }else{
        $(this).val('Закрыть управление');
        let house_id = $('#house_panel').data("house_id");
        $.when(run_method('content','get_house_management_panel',[house_id])).done(function(data){      
            $('#house_panel').after(data);
            $("html,body").scrollTop($('#house_management_panel').offset().top);
        });
    }
});