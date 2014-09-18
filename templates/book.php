<div id="booking-gadget">
<h1>Bookings</h1>
<!--
<div class="step-arrows">
<div class="step-arrow"><span>Cart</span></div>
<div class="step-arrow highlight"><span>Payment</span></div>
<div class="step-arrow"><span>Confirmation</span></div>
<div class="clear"></div>
</div>
-->
<div id="cart"></div>
<div id="gadget"></div>
</div>
</div>
<script type="text/javascript">

$w(function() {
    BE.gadget.book("#gadget",{
        vcID:<?php echo $vc_id ?>,
        itineraryCSS:"<?php echo $itinerarycss; ?>",
        confirmationURL:"<?php echo $confirmationurl ?>",
        bookedBy: 'Online' 
    });
});

</script>
