var $=jQuery.noConflict();

jQuery(document).ready(function($){



    function update_creative_status(){
        console.log('enter update status');
        $thisbutton = $(".update_creature_status_wrapper .form-row .input_wrapper");
        order_num_to_update = $('.update_creature_status_wrapper form').find('.form-row input#order_num_to_update').val();
        console.log("🚀 ~ file: custom-script.js:10 ~ $ ~ order_num:", order_num_to_update);
        
        $(".update_creature_status_wrapper").css("background-color","#00ADEE");
        $.ajax({
            type:"POST",
            url: ajax_obj.ajax_url,
            data: {
                'action': 'update_order_creative_status',
                'order_num_to_update' : order_num_to_update
            },
            beforeSend: function (response) {
                $thisbutton.addClass('loader_active');
            },
            success: function (results) {
                console.log('success');
                console.log(results);
                row_details =  results.order_data;
                current_time = row_details.scan_time;
                console.log("🚀 ~ file: ajax-scripts.js:45 ~ $ ~ current_time:", current_time);
                order_num = row_details.order_num;
                order_msg = row_details.message;
                //if(!$("tr."+order_num).length){
                    $(".update_creature_status_wrapper form table tbody").prepend("<tr class="+order_num+"><td>"+order_num+"</td><td>"+current_time+"</td><td>"+order_msg+"</td></tr>");
                //}
                if(results.update_status == 'success'){
                    //$("tr."+order_num).css("background-color","#00ADEE");
                    $(".update_creature_status_wrapper").css("background-color","green");
                    setTimeout(function() {
                        $(".update_creature_status_wrapper").css("background-color","#fff");
                    },2000);
                }
                else{
                    $(".update_creature_status_wrapper").css("background-color","orange");
                    setTimeout(function() {
                        $(".update_creature_status_wrapper").css("background-color","#fff");
                    },2000);
                    //$("tr."+order_num).css("background-color","orange");
                }
            },
            complete: function (data) {
                console.log('complete');
                $thisbutton.removeClass('loader_active');
                $('#order_num_to_update').val('');    
                $('#order_num_to_update').focus();
            },
            error: function (errorThrown) {
                console.log('error');
                
            }
        });
        
        

        

    }

    $('.check_order_update_status').on('click', function() {
        $(".update_creature_status_wrapper form table tbody tr").css("background-color","#fff");
        $thisbutton = $(this);
        order_num_to_update = $(this).closest('form').find('.form-row input#order_num_to_update').val();
        console.log("🚀 ~ file: custom-script.js:10 ~ $ ~ order_num:", order_num_to_update);
        var $span_num_error =  $('#order_num_to_update').next('span');
        if($span_num_error.length > 0){
            $span_num_error.remove();
        }

        if (order_num_to_update.length == 0) {
            $('#order_num_to_update').after('<span class="error">מספר הזמנה שדה חובה</span>');
            validate_order_num = false;
        }
        else{
            validate_order_num = true;
        }

        if(validate_order_num == true){

            if($(".send_msg_wrapper").length){
                $(".send_msg_wrapper").empty();
            }
            $.ajax({
                type:"POST",
                url: ajax_obj.ajax_url,
                data: {
                    'action': 'update_order_creative_status',
                    'order_num_to_update' : order_num_to_update
                },
                beforeSend: function (response) {
                    $thisbutton.addClass('loader_active');
                },
                success: function (results) {
                    console.log('success');
                    console.log(results);
                    row_details =  results.order_data;
                    current_time = row_details.scan_time;
                    console.log("🚀 ~ file: ajax-scripts.js:45 ~ $ ~ current_time:", current_time);
                    order_num = row_details.order_num;
                    order_msg = row_details.message;
                    if(!$("tr."+order_num).length){
                        $(".update_creature_status_wrapper form table tbody").append("<tr class="+order_num+"><td>"+order_num+"</td><td>"+current_time+"</td><td>"+order_msg+"</td></tr>");
                    }
                    $('#order_num_to_update').val('');    
                    $('#order_num_to_update').focus();
                    if(results.update_status == 'success'){
                        $("tr."+order_num).css("background-color","#00ADEE");
                    }
                    else{
                        $("tr."+order_num).css("background-color","orange");
                    }
                },
                complete: function (data) {
                    console.log('complete');
                    $thisbutton.removeClass('loader_active');
                },
                error: function (errorThrown) {
                    console.log('error');
                    
                }
            });
        }

        
    });

    $('#order_num_to_update').focusout(function() {
        var inputValue = $(this).val();
        if (inputValue !== '') {
            console.log('Focus moved out of the input field');
            update_creative_status();
        }  
    });

      // Function to handle keypress (Enter) event
    $('#order_num_to_update').keypress(function(event) {
        if (event.which === 13) { // Check if the pressed key is Enter (key code 13)
            event.preventDefault();
            var inputValue = $(this).val();
            if (inputValue !== '') {
                console.log('press enter the input field');
                update_creative_status();
            }
        }
    });
    
});