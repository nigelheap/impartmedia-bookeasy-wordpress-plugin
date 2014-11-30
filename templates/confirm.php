<div id="confirm-message-link"></div>

<script type="text/javascript">

$w(function() {
    BE.gadget.confirm("#confirm-message-link", {
        thankYouText:"",
        demo:false,
        pdfLinkText:'Download your itinerary PDF now.'
    });
    jQuery("#confirm-message-link a").html('Download your itinerary PDF now.');
});

</script>