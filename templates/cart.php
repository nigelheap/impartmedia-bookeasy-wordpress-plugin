  
  <span id="toolbar-cart"></span>
  <script type="text/javascript">

    
    var $checkCartInterval = 0;
    var $tripPlannerHoverTimeout = 0;
    
    $w(function() {
        BE.gadget.cart("#toolbar-cart", {
            vcID:<?php echo $vc_id ?>,
            bookingURL:"<?php echo $bookingurl ?>",
            autoCollapse:true
        });
    });
    jQuery(document).ready(function() {
        jQuery(".link-tripplanner-hover").hover(function() { showTripPlannerPopout(this); }, function() { hideTripPlannerPopout(); });
        jQuery("#shopping-cart-link").hover(function() { jQuery(this).click(); }, function() { });
    });
    function addToTripPlanner($url) {
        jQuery.get($url, function($numberItems) {
            jQuery("#toolbar-tripplanner a").text(parseInt($numberItems));
            showTripPlannerPopout(jQuery(".link-tripplanner-hover"));
            setTimeout("hideTripPlannerPopout()", 10000);
            if (parseInt($numberItems) > 0) {
                jQuery(".link-tripplanner-hover").show();
            }
        });
    }
    function removeFromTripPlanner($url, $obj) {
        jQuery.get($url, function($numberItems) {
            jQuery("#toolbar-tripplanner a").text(parseInt($numberItems));
            jQuery($obj).parent().parent().slideUp(function() {
                jQuery(this).remove();
            });
            if (parseInt($numberItems) == 0) {
                jQuery(".link-tripplanner-hover").hide();
            }
        });
    }
    function showTripPlannerPopout(obj) {
        if (jQuery(".toolbar-hover-popout").size() > 0) {
            jQuery(".toolbar-hover-popout").stop();
            jQuery(".toolbar-hover-popout").fadeTo(400, 1.0);
            clearTimeout($tripPlannerHoverTimeout);
        } else {
            jQuery(obj).append('<div class="toolbar-hover-popout" style="display:none"><img id="loading-animation" src="http://gadgets.impartmedia.com/img/loading.gif" alt="" /></div>');
            jQuery(".toolbar-hover-popout").fadeTo(400, 1.0);
            jQuery.get("/trip-planner?tmpl=ajaxrequest&amp;layout=ajaxrequest", function(data) {
                jQuery(".toolbar-hover-popout").html(data);
            });
        }
    }
    function hideTripPlannerPopout() {
        jQuery(".toolbar-hover-popout").stop();
        jQuery(".toolbar-hover-popout").fadeTo(400, 0.0, function() {
            jQuery(".toolbar-hover-popout").hide();
        });
        $tripPlannerHoverTimeout = setTimeout(function() { jQuery(".toolbar-hover-popout").remove(); }, 10000);
    }
    

  </script>