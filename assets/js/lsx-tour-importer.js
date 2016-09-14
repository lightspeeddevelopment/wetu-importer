var LSX_TOUR_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tools_page_lsx-tour-importer')){
			this.myAccommodationSearch();
			this.watchSearch();
			this.watchAdvancedSearch();
			this.watchImportButton();
			this.watchAddToListButton();
			this.watchClearButton();
			this.watchBannerButton();
			this.watchConnectButton();
		}
	},
	myAccommodationSearch: function() {
		jQuery('#lsx-tour-importer-search-form .my-accommodation-search-toggle').on( 'click', function(event) {
			event.preventDefault();
			jQuery('#lsx-tour-importer-search-form input.keyword').val('my-posts');
			jQuery('#lsx-tour-importer-search-form').submit();
			jQuery('#lsx-tour-importer-search-form input.keyword').val('');
		});
	},		
	watchSearch: function() {
		jQuery('#lsx-tour-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();

			jQuery('#posts-filter tbody').html('<tr><td style="text-align:center;" colspan="4">'+jQuery('#lsx-tour-importer-search-form .ajax-loader').html()+'</td></tr>');

			var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');
			var keywords = [];

			if('' != jQuery('#lsx-tour-importer-search-form input.keyword').val()){
				keywords.push(jQuery('#lsx-tour-importer-search-form input.keyword').val());
			}

			if('' != jQuery('#lsx-tour-importer-search-form .advanced-search textarea').val()){
				var bulk_keywords = jQuery('#lsx-tour-importer-search-form .advanced-search textarea').val().split('\n');
				var arrayLength = bulk_keywords.length;
				for (var i = 0; i < arrayLength; i++) {
				    keywords.push(bulk_keywords[i]);
				}				
			}		

			console.log(keywords);	

			jQuery.post(lsx_tour_importer_params.ajax_url,
	        {
	            'action' 	: 			'lsx_tour_importer',
	            'type'		: 			type,
	            'keyword' 	: 			keywords
	        },
	        function(response) {
	        	jQuery('#posts-filter tbody').html(response);
	        });
			return false;
		});	
	},
	watchAdvancedSearch: function() {
		jQuery('#lsx-tour-importer-search-form .advanced-search-toggle').on( 'click', function(event) {
			event.preventDefault();
			if(jQuery('#lsx-tour-importer-search-form .advanced-search').hasClass('hidden')){
				jQuery('#lsx-tour-importer-search-form .advanced-search').fadeIn('fast').removeClass('hidden');
				jQuery('#lsx-tour-importer-search-form .normal-search').hide('fast');
				jQuery('#lsx-tour-importer-search-form .normal-search input.keyword').val('');

			}else{
				jQuery('#lsx-tour-importer-search-form .advanced-search').fadeOut('fast').addClass('hidden');
				jQuery('#lsx-tour-importer-search-form .advanced-search textarea').val('');
				jQuery('#lsx-tour-importer-search-form .normal-search').fadeIn('fast');

			}
		});	
	},	
	watchClearButton: function() {
		jQuery('#posts-filter input.button.clear').on('click',function(event){
			event.preventDefault();
			jQuery('#posts-filter tbody').html('');	
			jQuery('#lsx-tour-importer-search-form input[type="text"]').val('');	
		});
	},	
	watchAddToListButton: function() {
		jQuery('#posts-filter input.button.add').on('click',function(event){
			
			event.preventDefault();
			jQuery('.import-list-wrapper').fadeIn('fast');	

			jQuery('#posts-filter tbody tr input:checked').each(function(){
		        jQuery('#import-list tbody').append(jQuery(this).parent().parent());
			});	

			jQuery('#import-list tbody tr input:checked').each(function(){
				jQuery(this).parent().parent().fadeIn('fast');
			});
		});
	},	

	newImport: function(args,row) {
		var $this = this;
		var $row = row;

	    jQuery.ajax( {
	        url : lsx_tour_importer_params.ajax_url,
	        data : args,
	    } )
        .done( function( data ) {
			if('none' == jQuery('.completed-list-wrapper').css('display')){
				jQuery('.completed-list-wrapper').fadeIn('fast');
			}
			jQuery('.completed-list-wrapper ul').append(data);
        	$row.fadeOut('fast', 
        	function(here){ 
	            jQuery(this).fadeOut('fast').remove();
	        });
        } )
        .fail( function( reason ) {
            // Handles errors only
            console.debug( reason );
        } )
        .always( function( data, textStatus, response ) {
            // If you want to manually separate stuff
            // response becomes errorThrown/reason OR jqXHR in case of success
        } );
	},
	importDone: function() {
	},
	importFail: function() {
	},
	importAlways: function() {
	},
	importThen: function() {
	},			

	watchImportButton: function() {
		var $this = this;

		jQuery('#import-list input[type="submit"]').on('click',function(event){

			event.preventDefault();
			var post_type = jQuery('.post_type').val();
			var array_import = [];
			var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');

			var team_members = [];
			if('undefined' != jQuery('#import-list input.team').length){
				jQuery('#import-list input.team').each(function(){
					if(jQuery(this).attr('checked')){
						team_members.push(jQuery(this).val());
					}
				});
			}
			var content = [];
			if('undefined' != jQuery('#import-list input.content').length){
				jQuery('#import-list input.content').each(function(){
					if(jQuery(this).attr('checked')){
						content.push(jQuery(this).val());
					}
				});
			}	
			var safari_brands = [];
			if('undefined' != jQuery('#import-list input.accommodation-brand').length){
				jQuery('#import-list input.accommodation-brand').each(function(){
					if(jQuery(this).attr('checked')){
						safari_brands.push(jQuery(this).val());
					}
				});
			}	

			counter = 0;
			jQuery('#import-list tr input:checked').each(function(){

				var wetu_id = jQuery(this).attr('data-identifier');
				var post_id = jQuery(this).val();


				jQuery(this).hide();
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#lsx-tour-importer-search-form .ajax-loader-small').html());
				var current_row = jQuery(this).parents('tr');

				//Removes the checkbox
				jQuery(this).remove();

				var data = {
		            'action' 	: 			'lsx_import_items',
		            'type'		: 			type,
		            'wetu_id' 	: 			wetu_id,
		            'post_id'	:			post_id,
		            'team_members' : 		team_members,
		            'safari_brands' : 		safari_brands,
		            'content'	: 			content
		        };

		        $this.newImport(data,current_row);
			});
		});
	},
	watchBannerButton: function() {
		jQuery('#banners-filter input.button.download').on('click',function(event){

			event.preventDefault();
			jQuery('#banners-filter tbody tr input:checked').each(function(){
				var post_id = jQuery(this).val();
				var current_row = jQuery(this).parents('tr');

				jQuery(this).hide();
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#banners-filter .ajax-loader-small').html());

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_sync_banners',
		            'post_id'	:			post_id,
		        },
		        function(response) {
		        	current_row.fadeOut('fast', 
		        	function(here){ 
			            jQuery(this).fadeOut('fast').remove();
			        });
		        });				
			});	
		});
	},
	watchConnectButton: function() {
		jQuery('#connect-accommodation-filter input.button.connect').on('click',function(event){

			event.preventDefault();
			jQuery('#connect-accommodation-filter tbody tr input:checked').each(function(){
				var post_id = jQuery(this).val();
				var type = 'connect_accommodation';
				var wetu_id = jQuery(this).attr('data-identifier');

				var current_row = jQuery(this).parents('tr');

				jQuery(this).hide();
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#connect-accommodation-filter .ajax-loader-small').html());

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_connect_accommodation',
		            'post_id'	:			post_id,
		            'type'		:			type,
		            'wetu_id'	:			wetu_id,
		        },
		        function(response) {
					if('none' == jQuery('.completed-list-wrapper').css('display')){
						jQuery('.completed-list-wrapper').fadeIn('fast');
					}
					jQuery('.completed-list-wrapper ul').append(response);		        	
		        	current_row.fadeOut('fast', 
		        	function(here){ 
			            jQuery(this).fadeOut('fast').remove();
			        });
		        });				
			});	
		});
	},	

}
jQuery(document).ready( function() {
	LSX_TOUR_IMPORTER.init();
});