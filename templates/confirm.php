<div id="confirm-message-link"></div>
<script type="text/javascript">

$w(function() {
    BE.gadget.confirm("#confirm-message-link", {
        thankYouText:"<?php echo $thank_you_text; ?>",
        demo:false,
        pdfLinkText:'<?php echo $pdf_link_text; ?>'
    });
    jQuery("#confirm-message-link a").html('<?php echo $pdf_link_text; ?>');
    jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();
});
</script>