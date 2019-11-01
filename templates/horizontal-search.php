<?php
    $settings = [
        'vcID' => $vc_id,
        'hybridMode' => true,
        'showRefineTools' => true,
        'collapseRefineTools' => true,
        'enableRegionSearch' => false,
    ];

    if(!empty($default_region_loc)){
        $settings['defaultRegionLoc'] = $default_region_loc;
    }

    if($accom == 'true'){
        $settings['hybridOptions']['accom'] = [
            'tabName' => $accom_tabname,
            'period' => 2,
            'adults' => 2,
            'children' => 0,
            'defaultDaysFromToday' => 0,
            'infants' => 0,
            'minDaysFromToday' => 0,
            'searchLocation' => $accom_search_path,
        ];
    }

    if($tours == 'true'){
        $settings['hybridOptions']['tours'] = [
            'tabName' => $tours_tabname,
            'period' => 1,
            'adults' => 1,
            'children' => 0,
            'defaultDaysFromToday' => 0,
            'infants' => 0,
            'minDaysFromToday' => 0,
            'searchLocation' => $tours_search_path,
        ];
    }


?>
<div class="horizontal-search">
    <div id="searchGadget" class="hybrid-widget">
        <div class="BE-hybrid-gadget"></div>
    </div>
</div> 

<div id="toolbar-cart"><span id="empty"></span></div>
<script type="text/javascript">

/**
BE.gadget.cart("#toolbar-cart", {
    vcID:<?php echo $vc_id; ?>,
    bookingURL:"<?php echo (isset($_SERVER['HTTP_HOST']) ? '//' .$_SERVER['HTTP_HOST'] : '' ) ?>/accommodation/book",
    autoCollapse:true
});
**/

// load the search gadget, wait a moment otherwise gadget lib isnt loaded
$w(function() {
    BE.gadget.search("#searchGadget .BE-hybrid-gadget", <?php echo json_encode($settings, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK); ?>);
});

// when the gadget has finished loading, move the advanced search link to the bottom, and move autocomplete box to the inside bottom
$w.event.subscribe("region.refinetools.built", function(data) {
    var advancedSearchLink = this.find("div.showHideRefineTools");
    advancedSearchLink.insertAfter(this.find("div.button"));
    var typeToSearch = this.parent().find("div.specific-prop-search-ultrasearch");
    typeToSearch.insertAfter(this.find("div.showHideRefineTools"));
});
</script>
