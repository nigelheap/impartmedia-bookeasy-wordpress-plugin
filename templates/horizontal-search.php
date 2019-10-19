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
setTimeout(function(){
    $w(function() {
        BE.gadget.search("#searchGadget .BE-hybrid-gadget",{
            vcID:<?php echo $vc_id ?>,
            hybridMode:true,
            showRefineTools:true,
            collapseRefineTools:true,
            hybridOptions:{
                <?php if($accom == 'true'): ?>
                "accom":{
                    "tabName":"<?php echo $accom_tabname; ?>",
                    "period":2,
                    "adults":2,
                    "children":0,
                    "defaultDaysFromToday":0,
                    "infants":0,
                    "minDaysFromToday":0,
                    "searchLocation":"<?php echo $accom_search_path ?>"
                }
                <?php endif; ?>
                <?php if($tours == 'true' && $accom == 'true'): ?>,<?php endif; ?>
                <?php if($tours == 'true'): ?>
                "tours":{
                    "tabName":"<?php echo $tours_tabname; ?>",
                    "period":1,
                    "adults":1,
                    "children":0,
                    "defaultDaysFromToday":0,
                    "infants":0,
                    "minDaysFromToday":0,
                    "searchLocation":"<?php echo $tours_search_path ?>"
                }<?php endif; ?>},
            enableRegionSearch:false
        });
    });
},500);

// when the gadget has finished loading, move the advanced search link to the bottom, and move autocomplete box to the inside bottom
$w.event.subscribe("region.refinetools.built", function(data) {
    var advancedSearchLink = this.find("div.showHideRefineTools");
    advancedSearchLink.insertAfter(this.find("div.button"));
    var typeToSearch = this.parent().find("div.specific-prop-search-ultrasearch");
    typeToSearch.insertAfter(this.find("div.showHideRefineTools"));
});
</script>
