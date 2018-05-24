<div id="booking-gadget">
    <h1>Bookings</h1>
    <div id="cart"></div>
    <div id="gadget"></div>
    <div class="finalising-model" style="display: none">
        <h3>Finalising payment</h3>
        <p>Please do not refresh the page during this process</p>
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
});

</script>