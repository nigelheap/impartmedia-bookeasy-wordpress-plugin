<div id="bookeasyPackagesWidget"></div>
<script type="text/javascript">
    $w(function () {
        BE.gadget.region("#bookeasyPackagesWidget",{
            vcID:<?php echo $vc_id ?>,
            showLegend:false,
            disabledTypes:["accom","tours","carhire","events"],
            packagesOnlyMode:true,
            showAllPackages:true,
            <?php if(!empty($package_url)): ?>itemDetailPageURL: "<?php echo $package_url; ?>?pid={id}",<?php endif; ?>
            showRefineTools:false,
        });
        jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();
    });

</script>