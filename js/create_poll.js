$(document).on('click','#add_poll_topic', function(){
    let topics = $('#poll_topics').children('[name="poll_topic"]');
    let valid = 1;
    let idno = 1;
    let idold = 1;
    let newid = 0;
    $.each(topics, function(key, value){
        idold = idno;
        idno = parseInt($(value).attr('id').split('_')[2]);
        if(idno > idold){
            newid = idno + 1;
        }else{
            newid = idold + 1;
        }
        if($(value).val().length < 1){
            valid = 0;
        }
    });
    if(valid == 1){
        $(this).before('<label for="topic_no_'+ newid +'">тема</label><textarea id="topic_no_'+ newid +'" name="poll_topic" data-input_validate="1"></textarea>');
    }
});

$(document).on('change','[name="poll_topic"]', function(){
    if(($(this).val().length == 0) && ($('#poll_topics').children('[name="poll_topic"]').length > 1)){
        $(this).prev('label').remove();
        $(this).remove();
    }
});

$(document).on('click','#btn_create_poll', function(){
    let form = $(this).parents('form');
    let form_data = serialize_form(form);
    let house_id = $(form).data("house_id");
    $.when(run_method('poll','create_poll',[house_id,form_data])).done(function(data){
        update_form_errors(data['inputs']);
        if(data['valid'] == 1){            
            $('.content').html(data['message']);
        }
    });
});