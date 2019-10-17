<?php if($environment == 'pvt'): ?>
    <script>
    
    BEurlsOverride = {
        cdn: function() { return "//gadgets-pvt.impartmedia.com/"; },
        sjp: function() { return "//sjp-pvt.impartmedia.com/"; },
        webapi: function() { return "https://webapi-pvt.bookeasy.com.au/"; },
    };
    </script>
    <script type="text/javascript" src="//gadgets-pvt.impartmedia.com/gadgets.jsz?key=<?php echo $api_key; ?>"></script>
<?php else: ?>
    <script type="text/javascript" src="//gadgets.impartmedia.com/gadgets.jsz?key=<?php echo $api_key; ?>"></script>
<?php endif; ?>