<script type="text/javascript" src="<?php echo BOOKEASY_URL . 'js/helpers.js'  ?>"></script>
<?php if($environment == 'pvt'): ?>
    <script>
    BEcssOverride = "none"; // none | minimal
    BEurlsOverride = {
        cdn: function() { return "//gadgets-pvt.impartmedia.com/"; },
        sjp: function() { return "//sjp-pvt.impartmedia.com/"; },
        webapi: function() { return "https://webapi-pvt.bookeasy.com.au/"; },
    };
    </script>
    <script type="text/javascript" src="//gadgets-pvt.impartmedia.com/gadgets.jsz?key=<?php echo $api_key; ?>"></script>
<?php else: ?>
    <script>
    BEcssOverride = "none"; // none | minimal
    BEurlsOverride = {
        cdn:function() { return "//gadgets.impartmedia.com/"; },
        sjp:function() { return "//sjp.impartmedia.com/"; },
        webapi: function() { return "https://webapi.bookeasy.com.au/"; },
    };
    </script>
    <script type="text/javascript" src="//gadgets.impartmedia.com/gadgets.jsz?key=<?php echo $api_key; ?>"></script>
<?php endif; ?>