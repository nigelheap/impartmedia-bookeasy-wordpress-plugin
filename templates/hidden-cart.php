<div class="hidden hidden-cart" style="display: none;"></div>
<script>
  if(typeof $w !== 'undefined' && typeof window.hasBookeasyCart === 'undefined') {
    $w(function () {
      BE.gadget.cart(".hidden-cart", {
        vcID:<?php echo $vc_id ?>,
        bookingURL:"<?php echo $bookingurl ?>",
        autoCollapse: false,
        showBookingTimer: false
      });
    });
  }
</script>