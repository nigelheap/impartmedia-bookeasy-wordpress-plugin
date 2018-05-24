<?php 
    $promote = array();
    if(function_exists('get_field')){
        $promoteIds = get_field('platinum_partners_members', 'option');

        if(isset($platinum_partners_term) && isset($platinum_partners_taxonomy)){
        	$promoteIds = array_filter($promoteIds, function($post_id) use ($platinum_partners_term, $platinum_partners_taxonomy){
        		return has_term( $platinum_partners_term, $platinum_partners_taxonomy, get_post($post_id));
        	});
        }

        if(!empty($platinum_partners_limit)){
        	$promoteIds = array_slice($promoteIds, 0, $platinum_partners_limit);
        }

        foreach($promoteIds as $promoteId){
        	$promote[] = get_field('bookeasy_OperatorID', $promoteId);
        }
        $promote = array_reverse($promote);
    }
    shuffle($promote);
	if(!empty($promote)): ?>
<script type="text/javascript">
	var $toPromote = <?php echo json_encode($promote); ?>;

	function promoteRows(){
		var $table = jQuery('.prices-grid table');
		var $tbody = $table.find('tbody');
		for (var i = 0; i < $toPromote.length; i++) {
			$table.find('#Operator' + $toPromote[i]).prependTo($tbody);
		}
	}

	jQuery(document).on("bookeasyListLoaded", function(){
		promoteRows();
	});

</script>
<?php endif; ?>