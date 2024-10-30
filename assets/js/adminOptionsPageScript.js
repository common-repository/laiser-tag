jQuery(function($) {
    //////////////////////////
    // TAG RELEVANCE SLIDER //
    //////////////////////////
    var input_hidden = $("#ltoc_tag_relevance");
    var handle = $("#custom-handle");
    var $ltoc_tag_relevance_slider = $("#ltoc_tag_relevance_slider");

    $ltoc_tag_relevance_slider.slider({
        create: function() {
            handle.text($(this).slider("value") + '%');
        },
        slide: function(event, ui) {
            handle.text(ui.value + '%');
            input_hidden.val(ui.value);
        },
        value: input_hidden.val(),
        min: 1,
        max: 100,
        range: "min"
    });

    ///////////////////////
    // RUN BATCH PROCESS //
    ///////////////////////
    var $run_batch_process = $('#run_batch_process');
    var $batch_process_pending = $('#batch_process_pending');
    var $batch_process_untagged_posts = $('#batch_process_untagged_posts');

    var batch_process_success = function(resp) {
        if(resp == 'in progress') {
            $batch_process_untagged_posts
                .html('Cannot start a new batch; already in progress. Please review the log output below and wait for the batch to finish processing.');
            setTimeout(function(){
                $.ajax({
                    url: ltoc_untagged_posts_url,
                    success: function(untagged) {

                        // hide loading circle
                        $batch_process_pending
                            .fadeOut();

                        $batch_process_untagged_posts
                            .html('There are <strong>' + untagged + '</strong> untagged posts left.');
                    }
                });
            }, 10000);
        }
    };

    var run_batch_process = function() {
        var batch_posts = $('#ltoc_batch_posts').val();

        $batch_process_untagged_posts
            .html('Batch process now in progress. Please review the log output below.');
        setTimeout(function(){
            $.ajax({
                url: ltoc_untagged_posts_url,
                success: function(untagged) {

                    // hide loading circle
                    $batch_process_pending
                        .fadeOut();

                    $batch_process_untagged_posts
                        .html('There are <strong>' + untagged + '</strong> untagged posts left.');
                }
            });
        }, 11000);

        // send ajax response
        $.ajax({
            data: {
                'posts': batch_posts
            },
            url: ltoc_batch_tagging_url,
            success: batch_process_success,
            timeout: 0 // no timeout
        });
    };

    $run_batch_process.click(run_batch_process);

    if(typeof ltoc_log_output !== 'undefined') {
        setInterval(function(){
            $.ajax({
                url: ltoc_log_output,
                success: function(data) {
                    $("#ltoc-log-output").html(data);
                }
            });
        }, 5000);
    }

});