
<div id="regionGadget"></div>
<script type="text/javascript">
$w(function() {
    BE.gadget.region("#regionGadget",{ 
        vcID:<?php echo $vc_id ?>,
        period:"<?php echo $period ?>",
        adults:"<?php echo $adults ?>",
        <?php if(!empty($force_accom_type)): ?>forceAccomType: "<?php echo $force_accom_type; ?>",<?php endif; ?>
        <?php if(!empty($limit_locations) && is_array($limit_locations)): ?>limitLocations : <?php echo json_encode($limit_locations); ?>,<?php endif; ?>
        <?php if(!empty($default_region_loc)): ?>defaultRegionLoc : "<?php echo $default_region_loc; ?>",<?php endif; ?>
        <?php if(!empty($google_maps_api)): ?>googleMapsKey : "<?php echo $google_maps_api; ?>",<?php endif; ?>
        showAllAccom:true,
        listAllMode:false,
        showMap:true,
        showLegend:false,
        defaultSort:"instant",
        accomOnlyMode:true,
        showRefineTools:true,
        collapseRefineTools:true,
        showLocationFilter:true,
        ignoreSearchCookie:false,
        itemDetailPageURL:"/members/{url}",
        //scriptCustomURLs:"/accommodation/script/customurls",
        enableRegionSearch:false,
	    disabledTypes:["events", "carhire", "packages", "tours"]
    });

});
</script>

<?php echo \Bookeasy\library\Template::get('templates/_results_adjustments'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_platinum_partners', array(
    'platinum_partners_limit' => $platinum_partners_limit,
    'platinum_partners_term' => 'accommodation',
    'platinum_partners_taxonomy' => $taxonomy,
)); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_hide_operators'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_force_view'); ?>
<script type="text/javascript">

    var $checkCartInterval = 0;
    var $tripPlannerHoverTimeout = 0;
 
    
    $w(function() {
        BE.gadget.cart("#toolbar-cart", {
            vcID:<?php echo $vc_id ?>,
            bookingURL:"<?php echo $bookingurl; ?>",
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