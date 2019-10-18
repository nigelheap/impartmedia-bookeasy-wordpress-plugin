



jQuery(document).ready( function() {
 
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

/**
 * 

    var hasList = setInterval(function(){
        if(jQuery('.prices-grid tr.has-specials').length > 0){
            jQuery('.prices-grid tr.has-specials').each(function(){
                if(jQuery(this).data('specials-copied') != 'yes'){
                    
                    var link = jQuery(this).find('.total a').attr('href');
                    jQuery(this).find('.total').prepend('<a href="'+link+'" class="specials-button"><span class="price im-pricebutton im-specials-button"><span class="number im-pricebutton-amount">Specials</span><span class="book im-pricebutton-label">Available Here</span></span></a> ');
                    jQuery(this).data('specials-copied', 'yes');
                }
            });
        }
        //clearInterval(hasList);
    }, 500);

jQuery('td.property').each(function(){
    var $img = jQuery(this).find('.thumb img');
    var $link = jQuery(this).find('.name').clone().text('');
    $img.wrap($link);
});

var checkResults = setInterval(function(){
    if(jQuery('td.property').length > 0){
        jQuery('td.property').find('.thumb img').on('click', function(){
            window.location = jQuery(this).parent().parent().find('.name').attr('href');
        });
        clearInterval(checkResults);
    }
}, 100);

*/

