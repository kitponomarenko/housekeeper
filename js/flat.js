$(document).on('click','[name="btn_manage_flat"]', function(){
    $(this).toggleClass('btn_green btn_border');
    if($('.content').find('#flat_management_panel').length > 0){
        $(this).val('Управление квартирой');
        $('#flat_management_panel').remove();
    }else{
        $(this).val('Закрыть управление');
        let flat_id = $('#flat_panel').data("flat_id");
        $.when(run_method('content','get_flat_management_panel',[flat_id])).done(function(data){      
            $('#flat_panel').after(data);
            $("html,body").scrollTop($('#flat_management_panel').offset().top);
        });
    }
});