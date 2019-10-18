<span id="toolbar-cart"><span id="empty"></span></span>
<script type="text/javascript">

    var $checkCartInterval = 0;
    var $tripPlannerHoverTimeout = 0;

    $w(function() {
        BE.gadget.cart("#toolbar-cart", {
            vcID:<?php echo $vc_id ?>,
            bookingURL:"<?php echo $bookingurl ?>",
            autoCollapse:true,
            showBookingTimer: true
        });

        jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();
    });



</script>