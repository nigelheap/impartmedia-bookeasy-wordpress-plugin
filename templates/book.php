<div id="booking-gadget">
    <div id="cart"></div>
    <div id="gadget"></div>
    <div class="finalising-model" style="display: none">
        <div>
            <h3>Finalising payment</h3>
            <p>Please do not refresh the page during this process</p>
            <div class="booking-spinner"></div>
        </div>
    </div>
</div>
<script type="text/javascript">

window.hasBookeasyCart = true;

$w(function() {
    BE.gadget.book("#gadget", {
        vcID: <?php echo $vc_id ?> ,
        itineraryCSS: "<?php echo $itinerarycss; ?>",
        confirmationURL: "<?php echo $confirmationurl ?>",
        bookedBy: '<?php echo $booked_by ?>'
    });

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

    var showFinalising = function(){

      if(jQuery('.ccDetails .button').hasClass('finalising') || jQuery('.personalDetails > .button').hasClass('finalising')){
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