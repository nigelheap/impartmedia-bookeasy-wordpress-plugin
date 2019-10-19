<div class="booking-widget">
    <!-- ======================================================================
    == ITEM GADGET
    ====================================================================== -->
    <div id="itemGadget" class=""></div>
    <!-- /ITEM GADGET -->
</div>
<script type="text/javascript">


    function formatPrices() {
        console.log('formatted prices');
        if(!!jQuery('.has-affiliate-link .tour-row-item .tour-row-item-occ').size()){
            jQuery('.has-affiliate-link .tour-row-item .tour-row-item-occ').each(function(){
                jQuery(this).after(jQuery(this).clone().removeClass('tour-row-item-occ').addClass('tour-row-price'));
            })
        }
    }

    function sortTable(){

      var table = jQuery('.priceGrid table');
      var switching = true;
      var shouldSwitch = true;

      table.find('tr').each(function(){
        var cost = jQuery(this).find('.cost:eq(0)').text();
        cost = cost.replace('$', '');

        if(cost){
          jQuery(this).data('cost', cost);
        } else {
          jQuery(this).data('cost', 1000);
        }

      });

      while (switching) {
        // Start by saying: no switching is done:
        switching = false;
        rows = table.find("tr");
        /* Loop through all table rows (except the
        first, which contains table headers): */
        for (i = 0; i < (rows.length - 1); i++) {
          // Start by saying there should be no switching:
          shouldSwitch = false;
          /* Get the two elements you want to compare,
          one from current row and one from the next: */
          x = jQuery(rows[i]).data('cost');
          y = jQuery(rows[i + 1]).data('cost');
          // Check if the two rows should switch place:

          if (parseFloat(x) > parseFloat(y)) {
            // If so, mark as a switch and break the loop:
            shouldSwitch = true;
            break;
          }
        }

        if (shouldSwitch && rows[i]) {
          rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
          switching = true;
        }
      }

    }


    var campaignID =  getCampaignID();
    if ( campaignID != ""){
        currentNights = 2;
    }

    var currentCookieObject = $w.json.parse($w.cookie(BE.util.cookieName()));
    var currentNights = "1";
    var currentAdults = "1";

    if ( currentCookieObject != null && currentCookieObject.product != "accom" ) {

        var tmpUserState = {
            product: '<?php echo $operator_id; ?>',
            type:"<?php echo $type; ?>",
            period:currentNights,
            adults:currentAdults,
            children:"0",
            infants:"0",
            date:currentCookieObject.date
        }

        $w.cookie(BE.util.cookieName(),$w.json.stringify(tmpUserState));
    }

    $w(function() {

            <?php if(!empty($_GET['bookingdate'])): 
                $newDate = strtotime($_GET['bookingdate']);
            ?>

            var currentCookieObject = $w.json.parse($w.cookie(BE.util.cookieName()));
            var hasCurrentCookieObject = (typeof currentCookieObject  === 'object');
            var tmpUserState;

            if(currentCookieObject){
                tmpUserState = {
                    product: hasCurrentCookieObject && (currentCookieObject.hasOwnProperty('product')) ? currentCookieObject.product : 'accom',
                    period: hasCurrentCookieObject && (currentCookieObject.hasOwnProperty('period')) ? currentCookieObject.period : 1,
                    adults: hasCurrentCookieObject && (currentCookieObject.hasOwnProperty('adults')) ? currentCookieObject.adults : 1,
                    children: hasCurrentCookieObject && (currentCookieObject.hasOwnProperty('children')) ? currentCookieObject.children : "0",
                    infants: hasCurrentCookieObject && (currentCookieObject.hasOwnProperty('infants')) ? currentCookieObject.children : "0",
                    date:"<?php echo date('D d/m/Y', $newDate); ?>"
                };
            } else {
                tmpUserState = {
                    product: '<?php echo $operator_id; ?>',
                    type:"<?php echo $type; ?>",
                    period: 1,
                    adults: 1,
                    children: "0",
                    infants: "0",
                    date:"<?php echo date('D d/m/Y', $newDate); ?>"
                };
            }

            $w.cookie(BE.util.cookieName(),$w.json.stringify(tmpUserState));
        
        <?php endif; ?>

        // call the gadget with the correct campaign settings
        if (campaignID != null && campaignID != "" ) {

            BE.gadget.details("#itemGadget",{
                vcID:<?php echo $vc_id ?>,
                type:"<?php echo $type; ?>",
                productID:<?php echo $operator_id; ?>,
                campaignID: campaignID,
                showFutureEvents:true,
                showAllAccom: true,
                showAllTours: true,
                showHoverInline: true,
                showHoverInlineToggleButtonContent: "More info"
            });

        } else {

            removeHash();

            <?php if($type == 'tours'): ?>

                BE.gadget.details("#itemGadget",{
                    vcID:<?php echo $vc_id ?>,
                    type:"<?php echo $type; ?>",
                    productID:<?php echo $operator_id; ?>,
                    period:2,
                    adults:2,
                    showAllAccom: true,
                    showAllTours: true,
                    showHoverInline: true,
                    collapseToursMode: true,
                    showHoverInlineToggleButtonContent: "More info"
                });

            <?php else: ?>

                BE.gadget.details("#itemGadget",{
                    vcID:<?php echo $vc_id ?>,
                    type:"<?php echo $type; ?>",
                    productID:<?php echo $operator_id; ?>,
                    showFutureEvents:true,
                    showFutureEventsPeriod:365,
                    showAllAccom: true,
                    showAllTours: true,
                    showHoverInline: true,
                    showHoverInlineToggleButtonContent: "More info"
                });

            <?php endif; ?>

            jQuery('.infants, .concessions, .students, .observers, .family').wrapAll('<div class="concession-type"></div>');
            jQuery(".children").after('<a id="show-concessions-link" style="cursor:pointer;">Show Concessions</a>');

        }


        copyBriefDescription = function(){

            if (jQuery('.priceGrid .noResults').length == 1){
                // No results
                return;
            }

            if (jQuery('.priceGrid .OperatorInfoMore').length == 0){
                // Wait for room info to magicly appear
                setTimeout(copyBriefDescription, 100);
                return;
            }

            jQuery('.priceGrid tbody tr').each(function(){
                var el = jQuery(this);
                var description = jQuery(el).find('.OperatorInfo .Description .OperatorItemContent').text();
                var description_split = description.split("|");

                if(description_split.length >= 2){

                    var first_sentence = description_split.splice(0,1).join("");
                    var remainder = description_split.join("");

                } else {

                    var description_words = description.split(" ");
                    var first_sentence = description_words.splice(0,30).join(" "); // Grab 40 words and join, modify array
                    var remainder = description_words.join(" ");
                }

                jQuery(el).find('.OperatorInfoMore').before('<div class="briefDescription">' + first_sentence + '...</div>');
                jQuery(el).find('.OperatorInfo .Description .OperatorItemContent').text(remainder);

            });


            checkResponsivePriceGrid();

        };

        collapsePrices = function() {

            if (jQuery('.priceGrid .noResults').length == 1){
                // No results
                return;
            }

            // Get header dates
            var dates = [];

            jQuery('.priceGrid thead tr').each(function(){
                jQuery(this).find('td.date').each(function(){
                    var el = jQuery(this);
                    var day = el.find('.day').text();
                    var date = el.find('.date').text();
                    var month = el.find('.month').text();
                    var str = day + ' (' + date + ' ' + month + ')';
                    dates.push(str)
                })
            });

            // For each row in table, grab all the prices and chuck them into a details popup
            jQuery('.priceGrid tbody tr').each(function(){

                var el = jQuery(this);
                var details = '';

                jQuery(el).find('.price').each(function(idx){
                    var date = dates[idx];
                    var price = jQuery(this).text();
                    details += '<div><span class="date">' + date + '</span><span class="price">' + price + '</span></div>';
                });

                el.find('.total').prepend('<div class="left">');
                el.find('.total .left').append('<span class="showDetails">Details</span>');
                jQuery(el.find('a')[0]).addClass('roomName')

                // Now move price out of and before button
                var button = el.find('a.im-pricebutton');
                var total = button.find('.im-pricebutton-amount').text().replace(',', '');

                if(el.find('.total .im-pricebutton').hasClass('sold-out')){
                    el.find('.total .left').prepend('<span class="totalPrice"></span>');
                    button.find('.im-pricebutton-amount').css('display', 'none');
                } else {
                    button.find('.im-pricebutton-amount').css('display', 'none');
                    el.find('.total .left').prepend('<span class="totalPrice">' + total + '</span>');
                }

                var startTime = jQuery(el).find('.start-time').text();
                startTime = startTime.toLowerCase();

                if(startTime.indexOf('12:00am') !== -1){
                    el.find('.start-time').hide();
                }

            });

            if (typeof window.formatPrices !== "undefined") {
                formatPrices();
            }

        };

        var priceGridObserver;

        setupObserver = function() {

            // Don't bind multiple times
            if (priceGridObserver){
                return;
            }

            var grid = jQuery('#itemGadget')[0];

            callback = function(mutations){

                var prices_added = false;
                var descriptions_added = false;

                // look through all mutations that just occured
                for(var i=0; i<mutations.length; ++i) {
                    // look through all added nodes of this mutation
                    for(var j=0; j<mutations[i].addedNodes.length; ++j) {
                        var node = mutations[i].addedNodes[j];

                        if (node.nodeType != Node.ELEMENT_NODE){
                            continue;
                        }

                        // priceGrid has been created
                        if (node.classList.contains('priceGrid')) {
                            prices_added = true;
                        }
                        if (node.classList.contains('OperatorInfo')) {
                            descriptions_added = true;
                        }
                    }
                }


            };

            priceGridObserver = new MutationObserver(callback);
            priceGridObserver.observe(grid, {
                childList: true,
                subtree: true
            });
        };

        setupObserver();

        fixMobilePriceGrid = function() {
            // Fix price table
            jQuery('.priceGrid tbody tr').each(function(){
                var el = jQuery(this);
                var $name = el.find('.name');
                var $total = el.find('.total');

                // Move some stuff around
                $name.find('.thumb').wrap('<div class="nameTop"></div>');
                $name.find('.roomName').insertAfter($name.find('.thumb'));
                $total.find('.left').insertAfter($name.find('.roomName'));

                // Bit of a hack
                $button = $total.find('.im-pricebutton')
                $fakebutton = $button.clone()
                $fakebutton.insertAfter($name.find('.left'));
                $fakebutton.on('click', function(e) {
                    // Find real button and "click" it
                    button = jQuery(e.currentTarget).closest('tr').find('td.total a')[0];
                    button.click();
                });
            });
        };

        checkResponsivePriceGrid = function() {
            if (window.matchMedia) {
                var mq = window.matchMedia("(max-width: 767px)");
                if (mq.matches) {
                    fixMobilePriceGrid();
                }
            }
        };

    });



</script>

