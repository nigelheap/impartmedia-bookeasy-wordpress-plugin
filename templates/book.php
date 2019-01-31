<h1>Bookings</h1>
<div id="booking-gadget">
    <div id="cart"></div>
    <div id="gadget"></div>
    <div class="finalising-model" style="display: none">
        <h3>Finalising payment</h3>
        <p>Please do not refresh the page during this process</p>
    </div>
    <div class="booking-loading" id="booking-loading" style="display: none;">
        <div class="loading-animation">
            <div class="mr-loading">
                <div class="mr-loading-icon">
                    <div class="mr-folding-cube">
                        <div class="mr-cube1 mr-cube"></div>
                        <div class="mr-cube2 mr-cube"></div>
                        <div class="mr-cube4 mr-cube"></div>
                        <div class="mr-cube3 mr-cube"></div>
                    </div>
                </div>
            </div>
            <div class="loading-text">
                Loading...
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

$w(function() {
    BE.gadget.book("#gadget", {
        vcID: <?php echo $vc_id ?> ,
        itineraryCSS: "<?php echo $itinerarycss; ?>",
        confirmationURL: "<?php echo $confirmationurl ?>",
        bookedBy: '<?php echo $booked_by ?>'
    });

    jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();

    setTimeout(function(){
        jQuery('.item.accom').each(function() {
            var name = jQuery(this).find('.name a').text();
            var opname = jQuery(this).find('.operator span').text();
            jQuery(this).find('.operator span').text(name);
            jQuery(this).find('.name a').text(opname);
        })
    }, 500);


    jQuery('body').on('click', '.acceptCancellationPolicy label, .receiveENewsletter label', function(){
      jQuery(this).siblings('input[type=checkbox]').click();
    });
    // .finalising
    // jQuery('.ccDetails .button').hasClass('finalising');

    var showFinalising = function(){

      var finalisingCount = jQuery('.ccDetails .button').hasClass('finalising');

      if(finalisingCount){
        jQuery('.finalising-model').show();
      } else {
        jQuery('.finalising-model').hide();
      }
    }

    jQuery('body').on('click', '.personalDetails .button a, .booking-gadget .ccDetails .button a', function() {
        showFinalising();
        var checkFinalising = setInterval(showFinalising, 1000);
    });


    jQuery('#booking-loading').show();

    var checkLoading = setInterval(function(){
        if(jQuery('.personalDetails, .noItems').size() > 0){
          jQuery('#booking-loading').hide();
          clearInterval(checkLoading);
        }
    }, 500);


});

</script>