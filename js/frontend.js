


jQuery(document).ready( function() {

    var cartCheck = setInterval(function(){
        if(jQuery('.shopping-cart').size() < 1){
            return;
        }

        var itemCount = jQuery('.shopping-cart .cartItems .item').length;
        jQuery('.cart-count').show().html(itemCount);
    }, 3000);


    jQuery(".update_operator").click( function(e) {
        e.preventDefault();
        var operator_id = jQuery(this).data("operator-id")
        var element = jQuery(this);
        element.find('i').addClass('fa-spin');
        element.find('span').html('Updating...');

        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : bookeasyAjax.ajaxurl,
            data : {
                action : "refresh_operator", 
                operators : operator_id
            },
            success: function(response) {
                if(response.created > 0 || response.updated > 0 || response.unpublished > 0) {
                    element.find('i').removeClass('fa-spin');
                    element.find('span').html('Updated');

                    setTimeout(function(){ 
                        element.find('span').html('');
                    }, 1000);
                }
                else {
                    alert("Error updating operator");
                }
            }
        });   

   });

});
