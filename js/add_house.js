$(document).on('click','[name="house_search_result"]', function(){
    let house_id = $(this).data("house_id");
    $.when(run_method('content','get_house_data',[house_id])).done(function(data){
        $('#house_data').html(data);
    });
});