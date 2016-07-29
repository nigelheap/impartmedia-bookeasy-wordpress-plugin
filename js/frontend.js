
BEurlsOverride = {
    cdn:function() { 
        return "//gadgets.impartmedia.com/"; 
    },    
    sjp:function() { 
        return "//sjp.impartmedia.com/"; 
    }
};


jQuery(document).ready( function() {
 
    jQuery(".update_operator").click( function(e) {
        e.preventDefault();
        var operator_id = jQuery(this).data("operator-id")
        var element = jQuery(this);
        element.html('Updating...');

        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : bookeasyAjax.ajaxurl,
            data : {
                action : "refresh_operator", 
                operators : operator_id
            },
            success: function(response) {
                if(response.created > 0 || response.updated > 0) {
                    element.html('Updated');
                }
                else {
                    alert("Error updating operator");
                }
            }
        });   

   });

});