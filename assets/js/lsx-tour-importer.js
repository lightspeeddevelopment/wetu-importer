var LSX_TOUR_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tools_page_lsx-tour-importer')){
			this.watchSearch();
			this.watchImportButton();
			this.watchAddToListButton();
		}
	},	
	watchSearch: function() {
		jQuery('#lsx-tour-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();

			jQuery('#posts-filter tbody').html(jQuery('#lsx-tour-importer-search-form .ajax-loader').html());

			var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');
			var keyword = jQuery('#lsx-tour-importer-search-form input.keyword').val();
			jQuery.post(lsx_tour_importer_params.ajax_url,
	        {
	            'action' 	: 			'lsx_tour_importer',
	            'type'		: 			type,
	            'keyword' 	: 			keyword
	        },
	        function(response) {
	        	jQuery('#posts-filter tbody').html(response);
	        });
			return false;
		});	
	},
	watchAddToListButton: function() {
		jQuery('#posts-filter input.button.add').on('click',function(event){
			
			event.preventDefault();
			jQuery('.import-list-wrapper').show();

			jQuery('#posts-filter tbody tr input:checked').each(function(){
				jQuery('#import-list tbody').append(jQuery(this).parent().parent());
			});	
		});
	},		
	watchImportButton: function() {
		jQuery('#import-list input[type="submit"]').on('click',function(event){
			event.preventDefault();
			
			var post_type = jQuery('.post_type').val();
			
			var array_import = [];
			
			counter = 0;
			
			jQuery('#the-list tr input:checked').each(function(){
				

				var wetu_id = jQuery(this).val();
				var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');
				var current_row = jQuery(this);

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_items',
		            'type'		: 			type,
		            'wetu_id' 	: 			wetu_id,
		            //'post_id'	:			post_id,
		        },
		        function(response) {
		        	current_row.parents('tr').hide();
		        });				
				
			});
		});		
	}

		
}

jQuery(document).ready( function() {
	LSX_TOUR_IMPORTER.init();
});