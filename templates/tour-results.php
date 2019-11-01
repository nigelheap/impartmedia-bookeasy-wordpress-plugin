<?php
    $settings = [
        'vcID' => $vc_id,
        'period' => $period,
        'adults' => $adults,
        'showAllAccom' => false,
        'listAllMode' => false,
        'showMap' => true,
        'showLegend' => false,
        'defaultSort' => "instant",
        'toursOnlyMode' => true,
        'showRefineTools' => true,
        'collapseRefineTools' => true,
        'showLocationFilter' => true,
        'ignoreSearchCookie' => false,
        'showAllTours' => true,
        'itemDetailPageURL' => "/members/{url}",
        'disabledTypes' => [
            "accom",
            "events",
            "carhire",
            "packages"
        ],
        'enableRegionSearch' => false,
    ];

    if(!empty($force_tour_type)){
        $settings['forceTourType'] = $force_tour_type;
    }

    if(!empty($google_maps_api)){
        $settings['googleMapsKey'] = $google_maps_api;
    }

    if(!empty($limit_locations) && is_array($limit_locations)){
        $settings['limitLocations'] = $limit_locations;
    }

    if(!empty($default_region_loc)){
        $settings['defaultRegionLoc'] = $default_region_loc;
    }

?>
<div id="regionGadget" class="region-tours"></div>
<script type="text/javascript">

var currentCookieObject = $w.json.parse($w.cookie(BE.util.cookieName()));

if (currentCookieObject != null && currentCookieObject.product != "tours") {
    currentCookieObject.product = "tours";
    currentCookieObject.period = 2;
    currentCookieObject.adults = 1;
    $w.cookie(BE.util.cookieName(), $w.json.stringify(currentCookieObject));
}

$w(function() {
    BE.gadget.region("#regionGadget", <?php echo json_encode($settings, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK); ?>);
});
</script>
<?php echo  \Bookeasy\library\Template::get('templates/_results_adjustments'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_hide_operators'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_force_view'); ?>