<script type="text/javascript">

    jQuery(document).on("bookeasyListLoaded", function(){
        jQuery('.prices-grid table tr').each(function(){
            if(jQuery(this).find('.thumb a').length < 1){
                var href = jQuery(this).find('.name').attr('href');
                jQuery(this).find('.thumb img').wrap('<a href="'+href+'"></a>');
            }
        });
    });

    function maybeHideRows(){
        var checkExistHide = setInterval(function() {
           if (jQuery('.prices-grid tbody tr').length) {
              setTimeout(function(){
                jQuery.event.trigger({
                   type: "bookeasyListLoaded",
                   time: new Date()
                });
              }, 300);
              clearInterval(checkExistHide);
           }
        }, 100); // check every 100ms
    }

    maybeHideRows();

    function waitForHideLoad(){
        var checkLoadExistHide = setInterval(function() {
           if (jQuery('.search-gadget .spinner:visible').length == 0) {
              maybeHideRows();
              clearInterval(checkLoadExistHide);
           }
        }, 100); // check every 100ms
    }


    var checkFormExistHide = setInterval(function() {
       if (jQuery('.search-gadget .tourTypes').length) {
          var $searchForm = jQuery('.search-gadget');
          $searchForm.find('.tourTypes select, .locationFilter select, .adults select, .children select, .infants select').change(function(){
            setTimeout(waitForHideLoad, 400);
          });
          clearInterval(checkFormExistHide);
       }
    }, 100); // check every 100ms

    var checkCalendarExistsHide = setInterval(function() {
       if (jQuery('.wdDatePicker-Outer').length) {
          jQuery('.wdDatePicker-Outer').click(function(){
            setTimeout(waitForHideLoad, 400);
          });
          clearInterval(checkCalendarExistsHide);
       }
    }, 100); // check every 100ms
</script>        