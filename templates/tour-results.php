<div id="regionGadget" class="region-tours"></div>
<script type="text/javascript">

var currentCookieObject = $w.json.parse($w.cookie(BE.util.cookieName()));
var debug = 0;
if (debug) console.log("Debugging is on");
if (debug) console.log(currentCookieObject);
if (currentCookieObject != null && currentCookieObject.product != "tours") {
    currentCookieObject.product = "tours";
    currentCookieObject.period = 2;
    currentCookieObject.adults = 1;
    $w.cookie(BE.util.cookieName(), $w.json.stringify(currentCookieObject));
    if (debug) console.log("Set cookie from type defaults");
}

$w(function() {
    BE.gadget.region("#regionGadget",{
        vcID:<?php echo $vc_id ?>,
        period:"<?php echo $period ?>",
        adults:"<?php echo $adults ?>",

        showAllAccom:false,
        listAllMode:false,
        showMap:true,
        showLegend:false,
        defaultSort:"instant",
        toursOnlyMode:true,
        showRefineTools:true,
        collapseRefineTools:true,
        showLocationFilter:true,
        ignoreSearchCookie:false,
        showAllTours:true,
        itemDetailPageURL:"/members/{url}",
        disabledTypes:["accom","events","carhire","packages"],
        //scriptCustomURLs:"/accommodation/script/customurls",
        <?php if(!empty($force_tour_type)): ?>forceTourType: "<?php echo $force_tour_type; ?>",<?php endif; ?>
        <?php if(!empty($google_maps_api)): ?>googleMapsKey : "<?php echo $google_maps_api; ?>",<?php endif; ?>
        <?php if(!empty($limit_locations) && is_array($limit_locations)): ?>limitLocations : <?php echo json_encode($limit_locations); ?>,<?php endif; ?>
        <?php if(!empty($default_region_loc)): ?>defaultRegionLoc : "<?php echo $default_region_loc; ?>",<?php endif; ?>

        vcLocations:[{"name":"Margaret River Visitor Centre","lat":-33.948709,"lng":115.074183}],
        enableRegionSearch:false
    });
    jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();

    jQuery('#regionGadget .view-choice .price span').click();

    setTimeout(function(){
        jQuery('#regionGadget .view-choice .price span').click();
    }, 500);
});
</script>
<?php echo BookEasy_Template::get('templates/_results_adjustments'); ?>
<?php echo BookEasy_Template::get('templates/_platinum_partners', array(
    'platinum_partners_limit' => !empty($platinum_partners_limit) ? $platinum_partners_limit : false,
    'platinum_partners_term' => 'tours',
    'platinum_partners_taxonomy' => $taxonomy,
)); ?>
<?php echo BookEasy_Template::get('templates/_hide_operators'); ?>
<?php echo BookEasy_Template::get('templates/_force_view'); ?>