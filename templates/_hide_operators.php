<?php
    $operatorIds = array();
    if(function_exists('get_field')){
        $operatorIds = get_field('hide_operators', 'option');
        $operatorIds = explode(',', $operatorIds);
    }
?>
<?php if(!empty($operatorIds)): ?>
<script type="text/javascript">
	var $toHide = <?php echo json_encode($operatorIds); ?>;

	function hideRows(){
		var $table = jQuery('.prices-grid table');
		for (var i = 0; i < $toHide.length; i++) {
			$table.find('#Operator' + $toHide[i]).hide();
		}
	}

	jQuery(document).on("bookeasyListLoaded", function(){
		hideRows();
	});

</script>
<?php endif; ?>