
/**
 * Only show results when needed
 * @return {Void} 
 */
jQuery(document).ready(function(){
    jQuery('#sync-results').hide();
    jQuery('#sync-form').submit(function(){
        jQuery('#sync-message').html('Syncing...');
        jQuery('#sync-results').show();
    });
});