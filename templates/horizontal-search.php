<div class="horizontal-search">
    <div id="searchGadget" class="hybrid-widget">
        <div class="BE-hybrid-gadget"></div>
    </div>
</div> 

<div id="toolbar-cart"><span id="empty"></span></div>

<?php if(false): ?>
    <div class="specific-prop-search specific-prop-search-ultrasearch jsonly"></div>
    <input type="text" name="operator_ultrasearch" id="operator_ultrasearch" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; width:140px" value="Type to search..." onblur="if (this.value=='') this.value='Type to search...';" onfocus="if (this.value=='Type to search...') this.value='';" />
    <div class="simple-lightbox-cl"></div>
    <div class="specific-prop-popout specific-prop-popout-ultrasearch">
    <ul>

    </ul>
    </div>
<?php endif; ?>

<script type="text/javascript">


/*
BE.gadget.cart("#toolbar-cart", {
    vcID:<?php echo amazingalbany_vcid(); ?>,
    bookingURL:"<?php echo (isset($_SERVER['HTTP_HOST']) ? '//' .$_SERVER['HTTP_HOST'] : '' ) ?>/accommodation/book",
    autoCollapse:true
});
*/
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
                    "period":2,
                    "adults":2,
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

/*
$w(function() {
    BE.gadget.cart("#toolbar-cart", {
        vcID:"10",
        bookingURL:"https://www.amazingalbany.com.au/accommodation/book",
        autoCollapse:true
    });
});
*/

// when the gadget has finished loading, move the advanced search link to the bottom, and move autocomplete box to the inside bottom
$w.event.subscribe("region.refinetools.built", function(data) {
    var advancedSearchLink = this.find("div.showHideRefineTools");
    advancedSearchLink.insertAfter(this.find("div.button"));
    var typeToSearch = this.parent().find("div.specific-prop-search-ultrasearch");
    typeToSearch.insertAfter(this.find("div.showHideRefineTools"));
}); 

</script>