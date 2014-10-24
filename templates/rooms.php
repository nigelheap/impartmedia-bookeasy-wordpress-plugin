<div class="booking-widget">
    <!-- ======================================================================
    == ITEM GADGET
    ====================================================================== -->
    <div id="itemGadget" class=""></div>
    <!-- /ITEM GADGET -->
</div>
<script type="text/javascript">

    function getCampaignID() {
        
        var windowHash = window.location.hash;
        var count = windowHash.match(/\//g);  
        var $campaignID = "";
        
        if (count != null && count.length == 3 ) {
            
            var $hashValue = window.location.hash;
            var $strLastPos = $hashValue.lastIndexOf("/")+1;
            $campaignID = $hashValue.slice($strLastPos, $hashValue.length);
        }
        return $campaignID;
    }
    var campaignID =  getCampaignID();
    if ( campaignID != "") currentNights=2; // force two nights for campaigns
    // reassign default BE cookie when browsing between areas
    var currentCookieObject = $w.json.parse($w.cookie(BE.util.cookieName()));
    var currentNights = "2";
    var currentAdults = "2";

    if ( currentCookieObject != null && currentCookieObject.product != "accom" ) {

        var tmpUserState = { 
            product:"accom", 
            period:currentNights, 
            adults:currentAdults, 
            children:"0", 
            infants:"0",
            date:currentCookieObject.date
        }

        $w.cookie(BE.util.cookieName(),$w.json.stringify(tmpUserState));
    }

    $w(function() {
        // call the gadget with the correct campaign settings

        if (campaignID != null && campaignID != "" ) {
            BE.gadget.details("#itemGadget",{
                vcID:<?php echo $vc_id ?>,
                type:"<?php echo $type; ?>",
                productID:<?php echo $operatorid; ?>
                ,campaignID: campaignID
            });
        } else {
            BE.gadget.details("#itemGadget",{
                vcID:<?php echo $vc_id ?>,
                type:"<?php echo $type; ?>",
                productID:<?php echo $operatorid; ?>
            });
        }

    });

  </script>