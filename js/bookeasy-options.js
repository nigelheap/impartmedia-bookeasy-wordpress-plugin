
/**
 * Only show results when needed
 * @return {Void} 
 */
jQuery(document).ready(function(){
    jQuery('#sync-results, #sync-results-categorise').hide();
    jQuery('#sync-form').submit(function(){
        jQuery('#sync-message').html('Syncing...');
        jQuery('#sync-results').show();
    });

    jQuery('#sync-form-categorise').submit(function(){
        jQuery('#sync-message-categorise').html('Syncing...');
        jQuery('#sync-results-categorise').show();
    });
});