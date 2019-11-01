<?php
    $settings = [
         'vcID' => $vc_id,
         'period' => $period,
         'adults' => $adults,
         'showAllAccom' => true,
         'listAllMode' => false,
         'showMap' => true,
         'showLegend' => false,
         'defaultSort' => "instant",
         'accomOnlyMode' => true,
         'showRefineTools' => true,
         'collapseRefineTools' => true,
         'showLocationFilter' => true,
         'ignoreSearchCookie' => false,
         'enableRegionSearch' => false,
         'disabledTypes' => ["events", "carhire", "packages", "tours"],
         'itemDetailPageURL' => "/members/{url}",
    ];

    if(!empty($force_accom_type)){
        $settings['forceAccomType'] = $force_accom_type;
    }

    if(!empty($limit_locations) && is_array($limit_locations)){
        $settings['limitLocations'] = $limit_locations;
    }

    if(!empty($default_region_loc)){
        $settings['defaultRegionLoc'] = $default_region_loc;
    }

    if(!empty($google_maps_api)){
        $settings['googleMapsKey'] = $google_maps_api;
    }

?><div id="regionGadget"></div>
<script type="text/javascript">
$w(function() {
    BE.gadget.region("#regionGadget", <?php echo json_encode($settings, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK); ?>);
});
</script>

<?php echo \Bookeasy\library\Template::get('templates/_results_adjustments'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_hide_operators'); ?>
<?php echo  \Bookeasy\library\Template::get('templates/_force_view'); ?>
<script type="text/javascript">

    $w(function() {
        BE.gadget.cart("#toolbar-cart", {
            vcID:<?php echo $vc_id ?>,
            bookingURL:"<?php echo $bookingurl; ?>",
            autoCollapse:true
        });
    });

    <?php if(false): ?>
    $w.event.subscribe('region.refinetools.built', function () {
      //console.log('build');
    });

    jQuery(document).on("bookeasyListLoaded", function(){
      //console.log('bookeasyListLoaded');
    });
    <?php endif; ?>

</script>