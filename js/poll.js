$(document).on('click','[name="btn_get_topics"]', function(){
    let poll_op = $(this).data("poll_op");
    let poll_id = $('#poll_panel').data("poll_id");
    $('[name="btn_get_topics"]').toggleClass('btn_green btn_border');
    if($('.content').find('#poll_topics_panel').length > 0){
        if(poll_op == 0){
            $('[name="btn_get_topics"]').val('Посмотреть вопросы повестки');
        }else{
            $('[name="btn_get_topics"]').val('Участвовать в голосовании');
        }
        $('#poll_topics_panel').remove();
    }else{
        $('[name="btn_get_topics"]').val('Закрыть вопросы');        
        $.when(run_method('poll','get_poll_topics',[poll_id,poll_op])).done(function(data){      
            $('#poll_panel').after(data);
            $("html,body").scrollTop($('#poll_topics_panel').offset().top);
        });
    }
});

$(document).on('click','[name="btn_vote"]', function(){
    let topic_card = $(this).parents('.topic_card');
    let topic_id = $(topic_card).data("topic_id");
    let vote = $(this).data("vote");
    let final = 0;
    if($(topic_card).next('.topic_card').length > 0){
        final = 0;
        $(topic_card).next('.topic_card').show(200);
    }else{
        final = 1;
    }
    $.when(run_method('poll','vote_poll',[topic_id,vote,final])).done(function(data){      
        if(final == 0){
            $(topic_card).next('.topic_card').show(200);
            $(topic_card).remove();
        }else{
            $('#poll_topics_panel').html(data['message']);
        }
    });    
});

$(document).on('click','[name="btn_show_results"]', function(){
    let poll_id = $('#poll_panel').data("poll_id");
    $.when(run_method('poll','get_poll_topics',[poll_id,0])).done(function(data){
        $('#poll_topics_panel').remove();
        $('#poll_panel').after(data);
        $("html,body").scrollTop($('#poll_topics_panel').offset().top);
    });
});