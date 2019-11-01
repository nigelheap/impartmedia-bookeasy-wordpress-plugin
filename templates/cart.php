<span id="toolbar-cart"><span id="empty"></span></span>
<script type="text/javascript">

    window.hasBookeasyCart = true;

    $w(function() {
        BE.gadget.cart("#toolbar-cart", {
            vcID:<?php echo $vc_id ?>,
            bookingURL:"<?php echo $bookingurl ?>",
            autoCollapse:true,
            showBookingTimer: true
        });
    });

</script>