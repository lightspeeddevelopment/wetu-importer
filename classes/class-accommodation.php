<?php
/**
 * @package   WETU_Importer_Accommodation
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer_Accommodation extends WETU_Importer_Admin {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'accommodation';

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url = false;

	/**
	 * The query string url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $url_qs = false;

	/**
	 * Options
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;

	/**
	 * The fields you wish to import
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $accommodation_options = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->set_variables();

		add_action( 'lsx_tour_importer_admin_tab_'.$this->tab_slug, array($this,'display_page') );
		add_action('wp_ajax_lsx_tour_importer',array($this,'process_ajax_search'));	
		add_action('wp_ajax_nopriv_lsx_tour_importer',array($this,'process_ajax_search'));		

		add_action('wp_ajax_lsx_import_items',array($this,'process_ajax_import'));	
		add_action('wp_ajax_nopriv_lsx_import_items',array($this,'process_ajax_import'));

		$temp_options = get_option('_lsx-to_settings',false);
		if(false !== $temp_options && isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug])){
			$this->options = $temp_options[$this->plugin_slug];
		}

		$accommodation_options = get_option('wetu_importer_accommodation_settings',false);
		if(false !== $accommodation_options){
			$this->accommodation_options = $accommodation_options;
		}
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables()
	{
		parent::set_variables();

		// ** This request only works with API KEY **
		//if ( false !== $this->api_username && false !== $this->api_password ) {
		//	$this->url    = 'https://wetu.com/API/Pins/';
		//	$this->url_qs = 'username=' . $this->api_username . '&password=' . $this->api_password;
		//} elseif ( false !== $this->api_key ) {
			$this->url    = 'https://wetu.com/API/Pins/' . $this->api_key;
			$this->url_qs = '';
		//}
	}

	/**
	 * search_form
	 */
	public function get_scaling_url($args=array()) {

		$defaults = array(
			'width' => '640',
			'height' => '480',
			'cropping' => 'c'
		);
		if(false !== $this->options){
			if(isset($this->options['width']) && '' !== $this->options['width']){
				$defaults['width'] = $this->options['width'];
			}

			if(isset($this->options['height']) && '' !== $this->options['height']){
				$defaults['height'] = $this->options['height'];
			}

			if(isset($this->options['cropping']) && '' !== $this->options['cropping']){
				$defaults['cropping'] = $this->options['cropping'];
			}	
		}	
		$args = wp_parse_args($args,$defaults);

		$cropping = $args['cropping'];
		$width = $args['width'];
		$height = $args['height'];

		return 'https://wetu.com/ImageHandler/'.$cropping.$width.'x'.$height.'/';

	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
        ?>
        <div class="wrap">
            <?php $this->navigation('accommodation'); ?>

            <?php $this->update_options_form(); ?>

            <?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo $this->tab_slug; ?>" />
				
				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','wetu-importer'); ?>" />
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','wetu-importer'); ?>" />
				</p>				

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>
				
					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php _e('Enter a title to search for and press enter','wetu-importer'); ?></label>
							</th>
							<td class="post-title page-title column-title">
								<strong>
									<?php _e('Enter a title to search for','wetu-importer'); ?>
								</strong>
							</td>
							<td class="date column-date">							
							</td>
							<td class="ssid column-ssid">
							</td>
						</tr>									
					</tbody>

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php _e('Add to List','wetu-importer'); ?>" />
					<input class="button button-primary clear" type="button" value="<?php _e('Clear','wetu-importer'); ?>" />
				</p>
			</form> 

			<div style="display:none;" class="import-list-wrapper">
				<br />        
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php _e('What content to Sync from WETU'); ?></h3>
							<ul>
                                <li><input class="content select-all" <?php $this->checked($this->destination_options,'all'); ?> type="checkbox"name="content[]"  value="all" /> <?php _e('Select All','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'description'); ?>" type="checkbox" name="content[]" value="description" /> <?php _e('Description','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'excerpt'); ?>" type="checkbox" name="content[]" value="excerpt" /> <?php _e('Excerpt','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'gallery'); ?>" type="checkbox" name="content[]" value="gallery" /> <?php _e('Main Gallery','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'category'); ?>" type="checkbox" name="content[]" value="category" /> <?php _e('Category','wetu-importer'); ?></li>
		                        <?php if(class_exists('LSX_TO_Maps')){ ?>
								    <li><input class="content" checked="<?php $this->checked($this->accommodation_options,'location'); ?>" type="checkbox" name="content[]" value="location" /> <?php _e('Location','wetu-importer'); ?></li>
		                        <?php } ?>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'destination'); ?>" type="checkbox" name="content[]" value="destination" /> <?php _e('Connect Destinations','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'checkin'); ?>" type="checkbox" name="content[]" value="checkin" /> <?php _e('Check In / Check Out','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'facilities'); ?>" type="checkbox" name="content[]" value="facilities" /> <?php _e('Facilities','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'friendly'); ?>" type="checkbox" name="content[]" value="friendly" /> <?php _e('Friendly','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'rating'); ?>" type="checkbox" name="content[]" value="rating" /> <?php _e('Rating','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'rooms'); ?>" type="checkbox" name="content[]" value="rooms" /> <?php _e('Rooms','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'special_interests'); ?>" type="checkbox" name="content[]" value="special_interests" /> <?php _e('Special Interests','wetu-importer'); ?></li>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'spoken_languages'); ?>" type="checkbox" name="content[]" value="spoken_languages" /> <?php _e('Spoken Languages','wetu-importer'); ?></li>

		                        <?php if(class_exists('LSX_TO_Videos')){ ?>
								    <li><input class="content" checked="<?php $this->checked($this->accommodation_options,'videos'); ?>" type="checkbox" name="content[]" value="videos" /> <?php _e('Videos','wetu-importer'); ?></li>
		                        <?php } ?>
							</ul>
							<h4><?php _e('Additional Content'); ?></h4>
							<ul>
								<li><input class="content" checked="<?php $this->checked($this->accommodation_options,'featured_image'); ?>" type="checkbox" name="content[]" value="featured_image" /> <?php _e('Set Featured Image','wetu-importer'); ?></li>
		                        <?php if(class_exists('LSX_Banners')){ ?>
								    <li><input class="content" checked="<?php $this->checked($this->accommodation_options,'banner_image'); ?>" type="checkbox" name="content[]" value="banner_image" /> <?php _e('Set Banner Image','wetu-importer'); ?></li>
		                        <?php } ?>
							</ul>
						</div>
						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('Assign a Team Member'); ?></h3> 
							<?php $this->team_member_checkboxes($this->accommodation_options); ?>
						</div>

						<div style="width:30%;display:block;float:left;">
							<h3><?php _e('Assign a Safari Brand'); ?></h3> 
							<?php echo $this->taxonomy_checkboxes('accommodation-brand',$this->accommodation_options); ?>
						</div>	

						<br clear="both" />			
					</div>


					<h3><?php _e('Your List'); ?></h3>
                    <p><input class="button button-primary" type="submit" value="<?php _e('Sync','wetu-importer'); ?>" /></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php _e('Sync','wetu-importer'); ?>" /></p>
				</form>
			</div>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php _e('Completed'); ?> - <small><?php _e('Import your','wetu-importer'); ?> <a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $this->plugin_slug; ?>&tab=destination"><?php _e('destinations'); ?></a> <?php _e('next','wetu-importer'); ?></small></h3>
				<ul>
				</ul>
			</div>
        </div>
        <?php
	}

	/**
	 * search_form
	 */
	public function search_form() {
	?>
        <form class="ajax-form" id="<?php echo $this->plugin_slug; ?>-search-form" method="get" action="tools.php" data-type="<?php echo $this->tab_slug; ?>">
        	<input type="hidden" name="page" value="<?php echo $this->tab_slug; ?>" />

        	<h3><span class="dashicons dashicons-search"></span> <?php _e('Search','wetu-importer'); ?></h3>
        	<div class="normal-search">
        		<input pattern=".{3,}" placeholder="3 characters minimum" class="keyword" name="keyword" value=""> <input class="button button-primary submit" type="submit" value="<?php _e('Search','wetu-importer'); ?>" />
        	</div>
        	<div class="advanced-search hidden" style="display:none;">
        		<p><?php _e('Enter several keywords, each on a new line.','wetu-importer'); ?></p>
        		<textarea rows="10" cols="40" name="bulk-keywords"></textarea>
        		<input class="button button-primary submit" type="submit" value="<?php _e('Search','wetu-importer'); ?>" />
        	</div>    

        	<p>
                <a class="advanced-search-toggle" href="#"><?php _e('Bulk Search','wetu-importer'); ?></a> |
                <a class="published search-toggle" href="#publish"><?php esc_attr_e('Published','wetu-importer'); ?></a> |
                <a class="pending search-toggle"  href="#pending"><?php esc_attr_e('Pending','wetu-importer'); ?></a> |
                <a class="draft search-toggle"  href="#draft"><?php esc_attr_e('Draft','wetu-importer'); ?></a> |
                <a class="import search-toggle"  href="#import"><?php esc_attr_e('WETU','wetu-importer'); ?></a>
            </p>

            <div class="ajax-loader" style="display:none;width:100%;text-align:center;">
            	<img style="width:64px;" src="<?php echo WETU_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>

            <div class="ajax-loader-small" style="display:none;width:100%;text-align:center;">
            	<img style="width:32px;" src="<?php echo WETU_IMPORTER_URL.'assets/images/ajaxloader.gif';?>" />
            </div>            
        </form>	
	<?php 
	}	

	/**
	 * search_form
	 */
	public function update_options_form() {
		echo '<div style="display:none;" class="wetu-status"><h3>'.__('Wetu Status','wetu-importer').'</h3>';
		$accommodation = get_transient('lsx_ti_accommodation');
		if('' === $accommodation || false === $accommodation || isset($_GET['refresh_accommodation'])){
			$this->update_options();
		}
		echo '</div>';
	}


	/**
	 * Save the list of Accommodation into an option
	 */
	public function update_options() {
		$data = file_get_contents( $this->url . '/List?' . $this->url_qs );
		$accommodation = json_decode($data, true);

		if(isset($accommodation['error'])){
		    return $accommodation['error'];
        }elseif (isset($accommodation) && !empty($accommodation)) {
			set_transient('lsx_ti_accommodation',$accommodation,60*60*2);
			return true;
		}
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 */
	public function find_current_accommodation($post_type='accommodation') {
		global $wpdb;
		$return = array();

		$current_accommodation = $wpdb->get_results("
					SELECT key1.post_id,key1.meta_value
					FROM {$wpdb->postmeta} key1

					INNER JOIN  {$wpdb->posts} key2 
    				ON key1.post_id = key2.ID
					
					WHERE key1.meta_key = 'lsx_wetu_id'
					AND key2.post_type = '{$post_type}'

					LIMIT 0,500
		");
		if(null !== $current_accommodation && !empty($current_accommodation)){
			foreach($current_accommodation as $accom){
				$return[$accom->meta_value] = $accom;
			}
		}
		return $return;
	}	

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_tour_importer' && isset($_POST['type']) && $_POST['type'] === 'accommodation'){
			$accommodation = get_transient('lsx_ti_accommodation');

			if ( false !== $accommodation ) {
				$searched_items = false;

				if(isset($_POST['keyword'] )) {
					$keyphrases = $_POST['keyword'];
				}else{
					$keyphrases = array(0);
                }

				if(!is_array($keyphrases)){
					$keyphrases = array($keyphrases);
				}
				foreach($keyphrases as &$keyword){
					$keyword = ltrim(rtrim($keyword));
				}


				$post_status = false;
				if(in_array('publish',$keyphrases)){
					$post_status = 'publish';
				}
				if(in_array('pending',$keyphrases)){
					$post_status = 'pending';
				}
				if(in_array('draft',$keyphrases)){
					$post_status = 'draft';
				}
				if(in_array('import',$keyphrases)){
					$post_status = 'import';
				}

				if (!empty($accommodation)) {

					$current_accommodation = $this->find_current_accommodation();

					foreach($accommodation as $row_key => $row){

						//If this is a current tour, add its ID to the row.
						$row['post_id'] = 0;
						if(false !== $current_accommodation && array_key_exists($row['id'], $current_accommodation)){
							$row['post_id'] = $current_accommodation[$row['id']]->post_id;
						}

						//If we are searching for
						if(false !== $post_status){

							if('import' === $post_status){

								if(0 !== $row['post_id']){
									continue;
								}else{
									$searched_items[sanitize_title($row['name']).'-'.$row['id']] = $this->format_row($row);
								}


							}else{

								if(0 === $row['post_id']){
									continue;
								}else{
									$current_status = get_post_status($row['post_id']);
									if($current_status !== $post_status){
										continue;
									}

								}
								$searched_items[sanitize_title($row['name']).'-'.$row['id']] = $this->format_row($row);
							}

						}else{
							//Search through each keyword.
							foreach($keyphrases as $keyphrase){

								//Make sure the keyphrase is turned into an array
								$keywords = explode(" ",$keyphrase);
								if(!is_array($keywords)){
									$keywords = array($keywords);
								}

								if($this->multineedle_stripos(ltrim(rtrim($row['name'])), $keywords) !== false){
									$searched_items[sanitize_title($row['name']).'-'.$row['id']] = $this->format_row($row);
								}
							}
						}
					}		
				}


				if(false !== $searched_items){
					ksort($searched_items);
					$return = implode($searched_items);
				}
			}
			print_r($return);
			die();
		}
	}

	/**
	 * Does a multine search
	 */	
	public function multineedle_stripos($haystack, $needles, $offset=0) {
		$found = false;
		$needle_count = count($needles);
	    foreach($needles as $needle) {
	    	if(false !== stripos($haystack, $needle, $offset)){
	        	$found[] = true;
	    	}
	    }
	    if(false !== $found && $needle_count === count($found)){ 
	    	return true;
		}else{
			return false;
		}
	}

	/**
	 * Formats the row for output on the screen.
	 */	
	public function format_row($row = false){
		if(false !== $row){

			$status = 'import';
			if(0 !== $row['post_id']){
				$status = '<a href="'.admin_url('/post.php?post='.$row['post_id'].'&action=edit').'" target="_blank">'.get_post_status($row['post_id']).'</a>';
			}

			$row_html = '
			<tr class="post-'.$row['post_id'].' type-tour" id="post-'.$row['post_id'].'">
				<th class="check-column" scope="row">
					<label for="cb-select-'.$row['id'].'" class="screen-reader-text">'.$row['name'].'</label>
					<input type="checkbox" data-identifier="'.$row['id'].'" value="'.$row['post_id'].'" name="post[]" id="cb-select-'.$row['id'].'">
				</th>
				<td class="post-title page-title column-title">
					<strong>'.$row['name'].'</strong> - '.$status.'
				</td>
				<td class="date column-date">
					<abbr title="'.date('Y/m/d',strtotime($row['last_modified'])).'">'.date('Y/m/d',strtotime($row['last_modified'])).'</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					'.$row['id'].'
				</td>
			</tr>';		
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import() {
		$return = false;
		if(isset($_POST['action']) && $_POST['action'] === 'lsx_import_items' && isset($_POST['type']) && $_POST['type'] === 'accommodation' && isset($_POST['wetu_id'])){
			
			$wetu_id = $_POST['wetu_id'];
			if(isset($_POST['post_id'])){
				$post_id = $_POST['post_id'];	
			}else{
				$post_id = 0;
			}

			if(isset($_POST['team_members'])){
				$team_members = $_POST['team_members'];	
			}else{
				$team_members = false;
			}

			if(isset($_POST['safari_brands'])){
				$safari_brands = $_POST['safari_brands'];	
			}else{
				$safari_brands = false;
			}			

			if(isset($_POST['content']) && is_array($_POST['content']) && !empty($_POST['content'])){
				$content = $_POST['content'];
				add_option('wetu_importer_accommodation_settings',$content);
			}else{
				delete_option('wetu_importer_accommodation_settings');
				$content = false;
			}

            $jdata = file_get_contents( $this->url . '/Get?' . $this->url_qs . '&ids=' . $wetu_id );
            if($jdata)
            {
                $adata=json_decode($jdata,true);
                if(!empty($adata))
                {
                	$return = $this->import_row($adata,$wetu_id,$post_id,$team_members,$content,$safari_brands);
                	$this->format_completed_row($return);
                }
            }

			die();
		}

	}	
	/**
	 * Formats the row for the completed list.
	 */
	public function format_completed_row($response){
		echo '<li class="post-'.$response.'"><span class="dashicons dashicons-yes"></span> <a target="_blank" href="'.get_permalink($response).'">'.get_the_title($response).'</a></li>';
	}
	/**
	 * Connect to wetu
	 */
	public function import_row($data,$wetu_id,$id=0,$team_members=false,$importable_content=false,$safari_brands=false) {

        if(trim($data[0]['type'])=='Accommodation')
        {
	        $post_name = $data_post_content = $data_post_excerpt = '';
	        $post = array(
	          'post_type'		=> 'accommodation',
	        );

	        $content_used_general_description = false;

	        //Set the post_content
	        if(false !== $importable_content && in_array('description',$importable_content)){
		        if(isset($data[0]['content']['extended_description']))
		        {
		            $data_post_content = $data[0]['content']['extended_description'];
		        }elseif(isset($data[0]['content']['general_description'])){
		            $data_post_content = $data[0]['content']['general_description'];
		            $content_used_general_description = true;
		        }elseif(isset($data[0]['content']['teaser_description'])){
		        	$data_post_content = $data[0]['content']['teaser_description'];
		        }
	        	$post['post_content'] = wp_strip_all_tags($data_post_content);
	        }

	        //set the post_excerpt
	        if(false !== $importable_content && in_array('excerpt',$importable_content)){
		        if(isset($data[0]['content']['teaser_description'])){
		        	$data_post_excerpt = $data[0]['content']['teaser_description'];
		        }elseif(isset($data[0]['content']['general_description']) && false === $content_used_general_description){
		            $data_post_excerpt = $data[0]['content']['general_description'];
		        }	   
		        $post['post_excerpt'] = $data_post_excerpt;     	
	        }

	        if(false !== $id && '0' !== $id){
	        	$post['ID'] = $id;
				if(isset($data[0]['name'])){
					$post['post_title'] = $data[0]['name'];
	        		$post['post_status'] = 'publish';
					$post['post_name'] = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'accommodation', 0);
				}
	        	$id = wp_update_post($post);
	        	$prev_date = get_post_meta($id,'lsx_wetu_modified_date',true);
	        	update_post_meta($id,'lsx_wetu_modified_date',strtotime($data[0]['last_modified']),$prev_date);
	        }else{

		        //Set the name
		        if(isset($data[0]['name'])){
		            $post_name = wp_unique_post_slug(sanitize_title($data[0]['name']),$id, 'draft', 'accommodation', 0);
		        }
	        	$post['post_name'] = $post_name;
	        	$post['post_title'] = $data[0]['name'];
	        	$post['post_status'] = 'publish';
	        	$id = wp_insert_post($post);

	        	//Save the WETU ID and the Last date it was modified.
	        	if(false !== $id){
	        		add_post_meta($id,'lsx_wetu_id',$wetu_id);
	        		add_post_meta($id,'lsx_wetu_modified_date',strtotime($data[0]['last_modified']));
	        	}
	        }
	        //Setup some default for use in the import
	        if(false !== $importable_content && (in_array('gallery',$importable_content) || in_array('banner_image',$importable_content) || in_array('featured_image',$importable_content))){
				$this->find_attachments($id);
			}

	        //Set the team member if it is there
	        if(post_type_exists('team') && false !== $team_members && '' !== $team_members){
	        	$this->set_team_member($id,$team_members);
	    	}

	        //Set the safari brand
	        if(false !== $safari_brands && '' !== $safari_brands){
	        	$this->set_safari_brands($id,$safari_brands);

	    	}	    	

	        if(class_exists('LSX_TO_Maps')){
	        	$this->set_map_data($data,$id,9);
	        	$this->set_location_taxonomy($data,$id);
	        }

	        if(post_type_exists('destination') && false !== $importable_content && in_array('destination',$importable_content)){
	        	$this->connect_destinations($data,$id);
	        }

	        if(false !== $importable_content && in_array('category',$importable_content)){
	        	$this->set_taxonomy_style($data,$id);
	        }

	        //Set the Room Data
	        if(false !== $importable_content && in_array('rooms',$importable_content)){
	        	$this->set_room_data($data,$id);
	    	}

	    	//Set the rating
	    	if(false !== $importable_content && in_array('rating',$importable_content)){
	       		$this->set_rating($data,$id);
	    	}

	    	//Set the checkin checkout data
	    	if(false !== $importable_content && in_array('checkin',$importable_content)){
	        	$this->set_checkin_checkout($data,$id);
	        }

	    	//Set the Spoken Languages
	    	if(false !== $importable_content && in_array('spoken_languages',$importable_content)){
	       		$this->set_spoken_languages($data,$id);
	    	}

	    	//Set the friendly options
	    	if(false !== $importable_content && in_array('friendly',$importable_content)){
	       		$this->set_friendly($data,$id);
	    	}

	    	//Set the special_interests
	    	if(false !== $importable_content && in_array('special_interests',$importable_content)){
	       		$this->set_special_interests($data,$id);
	    	}	    		    		        

	        //Import the videos
	        if(false !== $importable_content && in_array('videos',$importable_content)){
	        	$this->set_video_data($data,$id);
	        }

	        //Import the facilities
	        if(false !== $importable_content && in_array('facilities',$importable_content)){
	        	$this->set_facilities($data,$id);
	        }	        

	        //Set the featured image
	        if(false !== $importable_content && in_array('featured_image',$importable_content)){
	        	$this->set_featured_image($data,$id);
	        }
	        if(false !== $importable_content && in_array('banner_image',$importable_content)){
	        	$this->set_banner_image($data,$id);
	        }	        
	        //Import the main gallery
	        if(false !== $importable_content && in_array('gallery',$importable_content)){	    	
	    		$this->create_main_gallery($data,$id);
	        }	        	        	        
        }
        return $id;
	}

	/**
	 * Set the team memberon each item.
	 */
	public function set_team_member($id,$team_members) {

		delete_post_meta($id, 'team_to_'.$this->tab_slug);
		foreach($team_members as $team){
        	add_post_meta($id,'team_to_'.$this->tab_slug,$team);			
		}
	}

	/**
	 * Set the safari brand
	 */
	public function set_safari_brands($id,$safari_brands) {
		foreach($safari_brands as $safari_brand){
        	wp_set_object_terms( $id, intval($safari_brand), 'accommodation-brand',true);			
		}
	}	
	
	/**
	 * Saves the longitude and lattitude, as well as sets the map marker.
	 */
	public function set_map_data($data,$id,$zoom = '10') {
		$longitude = $latitude = $address = false;

		if(isset($data[0]['position'])){

			if(isset($data[0]['position']['driving_latitude'])){
				$latitude = $data[0]['position']['driving_latitude'];
			}elseif(isset($data[0]['position']['latitude'])){
				$latitude = $data[0]['position']['latitude'];
			}

			if(isset($data[0]['position']['driving_longitude'])){
				$longitude = $data[0]['position']['driving_longitude'];
			}elseif(isset($data[0]['position']['longitude'])){
				$longitude = $data[0]['position']['longitude'];
			}		

		}
		if(isset($data[0]['content']) && isset($data[0]['content']['contact_information'])){
			if(isset($data[0]['content']['contact_information']['address'])){
				$address = strip_tags($data[0]['content']['contact_information']['address']);

				$address = explode("\n",$address);
				foreach($address as $bitkey => $bit){
					$bit = ltrim(rtrim($bit));
					if(false === $bit || '' === $bit || null === $bit or empty($bit)){
						unset($address[$bitkey]);
					}
				}
				$address = implode(', ',$address);
				$address = str_replace(', , ', ', ', $address);
			}	
		}

		if(false !== $longitude){
			$location_data = array(
				'address'	=>	(string)$address,
				'lat'		=>	(string)$latitude,
				'long'		=>	(string)$longitude,
				'zoom'		=>	(string)$zoom,
				'elevation'	=>	'',
			);
			if(false !== $id && '0' !== $id){
	        	$prev = get_post_meta($id,'location',true);
	        	update_post_meta($id,'location',$location_data,$prev);
	        }else{
	        	add_post_meta($id,'location',$location_data,true);
	        }
		}
	}
	/**
	 * Saves the longitude and lattitude, as well as sets the map marker.
	 */
	public function set_location_taxonomy($data,$id) {
		$taxonomy = 'location';
		$terms = false;
		if(isset($data[0]['position'])){
			$country_id = 0;
			if(isset($data[0]['position']['country'])){

				if(!$term = term_exists(trim($data[0]['position']['country']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['country']), 'location');
		            if ( is_wp_error($term) ){
		            	echo $term->get_error_message();
		            }
		            else {
		            	wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		            }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }
		        $country_id = intval($term['term_id']);
		    }

			if(isset($data[0]['position']['destination'])){

				$tax_args = array('parent'=>$country_id);
				if(!$term = term_exists(trim($data[0]['position']['destination']), 'location'))
		        {
		            $term = wp_insert_term(trim($data[0]['position']['destination']), 'location', $tax_args);
		            if ( is_wp_error($term) ){echo $term->get_error_message();}
		            else { wp_set_object_terms( $id, intval($term['term_id']), 'location',true); }
		        }
		        else
		        {
		            wp_set_object_terms( $id, intval($term['term_id']), 'location',true);
		        }				
			}		
		}
	}

	/**
	 * Connects the destinations post type
	 */
	public function connect_destinations($data,$id) {
		if(isset($data[0]['position'])){
		    $destinations = false;
		    if(isset($data[0]['position']['country'])){
		    	$destinations['country'] = $data[0]['position']['country'];
		    }
		    if(isset($data[0]['position']['destination'])){
		    	$destinations['destination'] = $data[0]['position']['destination'];
		    }
		    
		    if(false !== $destinations){	
		    	$prev_values = get_post_meta($id,'destination_to_accommodation',false);
		    	if(false === $prev_values || !is_array($prev_values)){
		    		$prev_values = array();
		    	}
		    	//print_r($destinations);
				$destinations = array_unique($destinations);
				//print_r($destinations);
			    foreach($destinations as $key => $value){
				    $destination = get_page_by_title(ltrim(rtrim($value)), 'OBJECT', 'destination');
	                if (null !== $destination) {
	                	if(!in_array($destination->ID,$prev_values)){
	                   		add_post_meta($id,'destination_to_accommodation',$destination->ID,false);
	                   		add_post_meta($destination->ID,'accommodation_to_destination',$id,false);
	                	}
	                } 		    	
			    }	
			}
		}
	}	

	/**
	 * Set the Travel Style
	 */
	public function set_taxonomy_style($data,$id) {
		$terms = false;
		if(isset($data[0]['category'])){
			if(!$term = term_exists(trim($data[0]['category']), 'accommodation-type'))
	        {
	            $term = wp_insert_term(trim($data[0]['category']), 'accommodation-type');
	            if ( is_wp_error($term) ){echo $term->get_error_message();}
	            else { wp_set_object_terms( $id, intval($term['term_id']), 'accommodation-type',true); }
	        }
	        else
	        {
	            wp_set_object_terms( $id, intval($term['term_id']), 'accommodation-type',true);
	        }				
		}
	}		

	/**
	 * Saves the room data
	 */
	public function set_room_data($data,$id) {
		if(!empty($data[0]['rooms']) && is_array($data[0]['rooms'])){
			$rooms = false;

			foreach($data[0]['rooms'] as $room){

				$temp_room = array();
				if(isset($room['name'])){
					$temp_room['title'] = $room['name'];
				}
				if(isset($room['description'])){
					$temp_room['description'] = strip_tags($room['description']);
				}			
				$temp_room['price'] = 0;
				$temp_room['type'] = 'room';

				if(!empty($room['images']) && is_array($room['images'])){
			    	$attachments_args = array(
			    			'post_parent' => $id,
			    			'post_status' => 'inherit',
			    			'post_type' => 'attachment',
			    			'order' => 'ASC',
			    	);   	
			    	$attachments = new WP_Query($attachments_args);
			    	$found_attachments = array();

			    	if($attachments->have_posts()){
			    		foreach($attachments->posts as $attachment){
			    			$found_attachments[] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
			    		}
			    	}

					$temp_room['gallery'] = array();
					foreach($room['images'] as $image_data){
			    		$temp_room['gallery'][] = $this->attach_image($image_data,$id,$found_attachments);
			    	}
				}
				$rooms[] = $temp_room;
			}

			if(false !== $id && '0' !== $id){
				delete_post_meta($id, 'units');				
			}
			foreach($rooms as $room){
		        add_post_meta($id,'units',$room,false);			
			}

			if(isset($data[0]['features']) && isset($data[0]['features']['rooms'])){
				$room_count = $data[0]['features']['rooms'];
			}else{
				$room_count = count($data[0]['rooms']);
			}

			if(false !== $id && '0' !== $id){
	        	$prev_rooms = get_post_meta($id,'number_of_rooms',true);
	        	update_post_meta($id,'number_of_rooms',$room_count,$prev_rooms);
	        }else{
	        	add_post_meta($id,'number_of_rooms',$room_count,true);
	        }
		}
	}

	/**
	 * Set the ratings
	 */
	public function set_rating($data,$id) {

		if(!empty($data[0]['features']) && isset($data[0]['features']['star_authority'])){
			$rating_type = $data[0]['features']['star_authority'];	
		}else{
			$rating_type = 'Unspecified2';
		}
		$this->save_custom_field($rating_type,'rating_type',$id);

		if(!empty($data[0]['features']) && isset($data[0]['features']['stars'])){
			$this->save_custom_field($data[0]['features']['stars'],'rating',$id,true);	
		}
	}

	/**
	 * Set the spoken_languages
	 */
	public function set_spoken_languages($data,$id) {
		if(!empty($data[0]['features']) && isset($data[0]['features']['spoken_languages']) && !empty($data[0]['features']['spoken_languages'])){
			$languages = false;
			foreach($data[0]['features']['spoken_languages'] as $spoken_language){
				$languages[] = sanitize_title($spoken_language);
			}
			if(false !== $languages){
				$this->save_custom_field($languages,'spoken_languages',$id);
			}
		}
	}

	/**
	 * Set the friendly
	 */
	public function set_friendly($data,$id) {
		if(!empty($data[0]['features']) && isset($data[0]['features']['suggested_visitor_types']) && !empty($data[0]['features']['suggested_visitor_types'])){
			$friendly_options = false;
			foreach($data[0]['features']['suggested_visitor_types'] as $visitor_type){
				$friendly_options[] = sanitize_title($visitor_type);
			}
			if(false !== $friendly_options){
				$this->save_custom_field($friendly_options,'suggested_visitor_types',$id);
			}
		}		
	}

	/**
	 * Set the special interests
	 */
	public function set_special_interests($data,$id) {
		if(!empty($data[0]['features']) && isset($data[0]['features']['special_interests']) && !empty($data[0]['features']['special_interests'])){
			$interests = false;
			foreach($data[0]['features']['special_interests'] as $special_interest){
				$interests[] = sanitize_title($special_interest);
			}
			if(false !== $interests){
				$this->save_custom_field($interests,'special_interests',$id);
			}
		}		
	}				

	/**
	 * Set the Check in and Check out Date
	 */
	public function set_checkin_checkout($data,$id) {

		if(!empty($data[0]['features']) && isset($data[0]['features']['check_in_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_in_time']);
			$time = date('h:ia',strtotime($time));
			$this->save_custom_field($time,'checkin_time',$id);
		}
		if(!empty($data[0]['features']) && isset($data[0]['features']['check_out_time'])){
			$time = str_replace('h',':',$data[0]['features']['check_out_time']);
			$time = date('h:ia',strtotime($time));
			$this->save_custom_field($time,'checkout_time',$id);
		}
	}	

	/**
	 * Set the Video date
	 */
	public function set_video_data($data,$id) {
		if(!empty($data[0]['content']['youtube_videos']) && is_array($data[0]['content']['youtube_videos'])){
			$videos = false;

			foreach($data[0]['content']['youtube_videos'] as $video){
				$temp_video = array();

				if(isset($video['label'])){
					$temp_video['title'] = $video['label'];
				}
				if(isset($video['description'])){
					$temp_video['description'] = strip_tags($video['description']);
				}	
				if(isset($video['url'])){
					$temp_video['url'] = $video['url'];
				}						
				$temp_video['thumbnail'] = '';
				$videos[] = $temp_video;
			}

			if(false !== $id && '0' !== $id){
				delete_post_meta($id, 'videos');				
			}
			foreach($videos as $video){
		        add_post_meta($id,'videos',$video,false);			
			}
		}
	}	

	/**
	 * Set the Facilities
	 */
	public function set_facilities($data,$id) {

		$parent_facilities = array(
			'available_services' => 'Available Services',
			'property_facilities' => 'Property Facilities',
			'room_facilities' => 'Room Facilities',
			'activities_on_site' => 'Activities on Site'
		);
		foreach($parent_facilities as $key => $label){
			$terms = false;
			if(isset($data[0]['features']) && isset($data[0]['features'][$key])){
				$parent_id = $this->set_term($id,$label,'facility');	
			}
			foreach($data[0]['features'][$key] as $child_facility){
				$this->set_term($id,$child_facility,'facility',$parent_id);
			}
		}
	}

	function set_term($id=false,$name=false,$taxonomy=false,$parent=false){
		if(!$term = term_exists($name, $taxonomy))
        {
        	if(false !== $parent){ $parent = array('parent'=>$parent); }
            $term = wp_insert_term(trim($name), $taxonomy,$parent);
            if ( is_wp_error($term) ){echo $term->get_error_message();}
            else { wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true); }
        }
        else
        {
            wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
        }
        return $term['term_id'];
	}	

	/**
	 * Creates the main gallery data
	 */
	public function set_featured_image($data,$id) {
		if(is_array($data[0]['content']['images']) && !empty($data[0]['content']['images'])){
	    	$this->featured_image = $this->attach_image($data[0]['content']['images'][0],$id);

	    	if(false !== $this->featured_image){
	    		delete_post_meta($id,'_thumbnail_id');
	    		add_post_meta($id,'_thumbnail_id',$this->featured_image,true);

	    		if(!empty($this->gallery_meta) && !in_array($this->featured_image,$this->gallery_meta)){
	    			add_post_meta($id,'gallery',$this->featured_image,false);
	    			$this->gallery_meta[] = $this->featured_image;
	    		}
	    	}			
		}	
	}	

	/**
	 * Sets a banner image
	 */
	public function set_banner_image($data,$id) {
		if(is_array($data[0]['content']['images']) && !empty($data[0]['content']['images'])){
	    	$this->banner_image = $this->attach_image($data[0]['content']['images'][1],$id,array('width'=>'1920','height'=>'800','cropping'=>'c'));

	    	if(false !== $this->banner_image){
	    		delete_post_meta($id,'image_group');
	    		$new_banner = array('banner_image'=>array('cmb-field-0'=>$this->banner_image));
	    		add_post_meta($id,'image_group',$new_banner,true);

	    		if(!empty($this->gallery_meta) && !in_array($this->banner_image,$this->gallery_meta)){
	    			add_post_meta($id,'gallery',$this->banner_image,false);
	    			$this->gallery_meta[] = $this->banner_image;
	    		}
	    	}			
		}	
	}		

	/**
	 * Creates the main gallery data
	 */
	public function create_main_gallery($data,$id) {

		if(is_array($data[0]['content']['images']) && !empty($data[0]['content']['images'])){
	    	$counter = 0;
	    	foreach($data[0]['content']['images'] as $image_data){
	    		if($counter === 0 && false !== $this->featured_image){$counter++;continue;}
	    		if($counter === 1 && false !== $this->banner_image){$counter++;continue;}

	    		$this->gallery_meta[] = $this->attach_image($image_data,$id);
	    		$counter++;
	    	}

	    	if(!empty($this->gallery_meta)){
	    		delete_post_meta($id,'gallery');
				$this->gallery_meta = array_unique($this->gallery_meta);
	    		foreach($this->gallery_meta as $gallery_id){
	    			if(false !== $gallery_id && '' !== $gallery_id && !is_array($gallery_id)){
	    				add_post_meta($id,'gallery',$gallery_id,false);
	    			}
	    		}
	    	}
    	}
	}	

	/**
	 * Attaches 1 image
	 */
	public function attach_image($v=false,$parent_id,$image_sizes=false){
		if(false !== $v){
	   		$temp_fragment = explode('/',$v['url_fragment']);
	    	$url_filename = $temp_fragment[count($temp_fragment)-1];
	    	$url_filename = str_replace(array('.jpg','.png','.jpeg'),'',$url_filename);

	    	if(in_array($url_filename,$this->found_attachments)){
	    		return array_search($url_filename,$this->found_attachments);
	    	}
	    	               
	        $postdata=array();
	        if(empty($v['label']))
	        {
	            $v['label']='';
	        }
	        if(!empty($v['description']))
	        {
	            $desc=wp_strip_all_tags($v['description']);
	            $posdata=array('post_excerpt'=>$desc);
	        }
	        if(!empty($v['section']))
	        {
	            $desc=wp_strip_all_tags($v['section']);
	            $posdata=array('post_excerpt'=>$desc);
	        }

	        $attachID=NULL;  
	        //Resizor - add option to setting if required
	        $fragment = str_replace(' ','%20',$v['url_fragment']);
	        $url = $this->get_scaling_url($image_sizes).$fragment;
	        $attachID = $this->attach_external_image2($url,$parent_id,'',$v['label'],$postdata);

	        //echo($attachID.' add image');
	        if($attachID!=NULL)
	        {
	            return $attachID;
	        }
        }	
        return 	false;
	}
	public function attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
	
		if ( !$url || !$post_id ) { return new WP_Error('missing', "Need a valid URL and post ID..."); }

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		// Download file to temp location, returns full server path to temp file
		//$tmp = download_url( $url );

		//var_dump($tmp);
		$tmp = tempnam("/tmp", "FOO");

		$image = file_get_contents($url);
		file_put_contents($tmp, $image);
		chmod($tmp,'777');

		preg_match('/[^\?]+\.(tif|TIFF|jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|pdf|PDF|bmp|BMP)/', $url, $matches);    // fix file filename for query strings
		$url_filename = basename($matches[0]);
		$url_filename=str_replace('%20','_',$url_filename);
		// extract filename from url for title
		$url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)
		 
		// override filename if given, reconstruct server path
		if ( !empty( $filename ) && " " != $filename )
		{
			$filename = sanitize_file_name($filename);
			$tmppath = pathinfo( $tmp );      

			$extension = '';  
			if(isset($tmppath['extension'])){
				$extension = $tmppath['extension'];
			}   

			$new = $tmppath['dirname'] . "/". $filename . "." . $extension;
			rename($tmp, $new);                                                                 // renames temp file on server
			$tmp = $new;                                                                        // push new filename (in path) to be used in file array later
		}

		// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
		$file_array['tmp_name'] = $tmp;                                                         // full server path to temp file

		if ( !empty( $filename) && " " != $filename )
		{
			$file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
		}
		else
		{
			$file_array['name'] = $url_filename;                                                // just use original URL filename
		}

		// set additional wp_posts columns
		if ( empty( $post_data['post_title'] ) )
		{

			$url_filename=str_replace('%20',' ',$url_filename);

			$post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
		}

		// make sure gets tied to parent
		if ( empty( $post_data['post_parent'] ) )
		{
			$post_data['post_parent'] = $post_id;
		}

		// required libraries for media_handle_sideload

		// do the validation and storage stuff
		$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status
		 
		// If error storing permanently, unlink
		if ( is_wp_error($att_id) )
		{
			unlink($file_array['tmp_name']);   // clean up
			return false; // output wp_error
			//return $att_id; // output wp_error
		}

		return $att_id;
	}			
}
$wetu_importer_accommodation = new WETU_Importer_Accommodation();