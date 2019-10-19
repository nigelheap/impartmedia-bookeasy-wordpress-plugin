<div id="itemGadget" class=""></div>

<script type="text/javascript">
    $w(function() {

        removeHash();

        BE.gadget.details("#itemGadget",{
            vcID:<?php echo $vc_id ?>,
            type:"packages",
            productID:"<?php echo $product_id ?>",
            showAllPackages:true,
        });

        jQuery('.infants, .concessions, .students, .observers, .family').wrapAll('<div class="concession-type"></div>');
        jQuery(".children").after('<a id="show-concessions-link" style="cursor:pointer;">Show Concessions</a>');
        jQuery('.concession-type').hide();
        jQuery('.booking-widget').on('click', '#show-concessions-link', function() {
          jQuery(this).next('.concession-type').toggle();
          if (jQuery('.concession-type').is(':visible')) {
            jQuery('#show-concessions-link').text('Hide Concessions');
          } else {
            jQuery('#show-concessions-link').text('Show Concessions');
          }

        });

        jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();

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

                // Room image lightbox
                var thumb_el = jQuery(el).find('.thumb img');
                var thumb_rel = thumb_el.attr('rel');
                var title = jQuery(jQuery(el).find('a')[0]).text();
                thumb_el.removeAttr('rel');
                thumb_el.wrap('<a href="http:'+thumb_rel+'" rel="prettyPhoto[rooms]" title="'+title+'" class="roomLightbox"></a>')
            });

            prettyPhotoInit(jQuery, jQuery(".priceGrid a[rel^='prettyPhoto']"));

            checkResponsivePriceGrid();

        }

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

                el.find('.showDetails').qtip({
                     content: {
                         text: details
                     },
                     style: {
                        classes: 'qtip-bootstrap qtip-pricing-details'
                    },
                    position: {
                        my: 'top center',
                        at: 'bottom center'
                    }
                 });

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

                var title = jQuery(el).find('.roomName').text();
                title = title.toLowerCase();

                if(title.indexOf('pass') !== -1 || title.indexOf('combo') !== -1 || title.indexOf('self-guided') !== -1){
                    el.find('.start-time').hide();
                }

            });

        }

        var priceGridObserver;
        setupObserver = function() {

            // Don't bind multiple times
            if (priceGridObserver){
                return;
            }

            // Watch #itemGadget's children
            // to detect when .priceGrid appears
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
                if (prices_added) {
                    collapsePrices();
                }
                if (descriptions_added) {
                    copyBriefDescription();
                }
            }
            priceGridObserver = new MutationObserver(callback);
            priceGridObserver.observe(grid, {
                childList: true,
                subtree: true
            });
        }
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