<script type="text/javascript">

    function setView(){
        jQuery('#regionGadget .view-choice .price span').click();

        setTimeout(function(){
            jQuery('#regionGadget .view-choice .price span').click();
        }, 500);
    }

    jQuery(document).on("bookeasyListLoaded", function(){

        if(jQuery(document).data('set-view') != 'done'){
            setView();
            jQuery(document).data('set-view', 'done');
        }
    });

    function maybeSetView(){
        var checkSetView = setInterval(function() {
            if (jQuery('#regionGadget .view-choice .price').length) {
                setView();
                clearInterval(checkSetView)
            }
        }, 100); // check every 100ms
    }

    maybeSetView();


</script>