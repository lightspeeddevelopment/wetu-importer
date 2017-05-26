<?php
/**
 * @package   WETU_Importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeed
 **/

class WETU_Importer {
	
	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|Module_Template
	 */
	protected static $instance = null;

	/**
	 * The slug for this plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $plugin_slug = 'wetu-importer';

	/**
	 * The options for the plugin
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $options = false;

	/**
	 * The url to import images from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $import_scaling_url = false;		

	/**
	 * scale the images on import or not
	 *
	 * @since 0.0.1
	 *
	 * @var      boolean
	 */
	public $scale_images = false;

	/**
	 * The WETU API Key
	 */
	public $api_key = false;

	/**
	 * The WETU API Username
	 */
	public $api_username = false;

	/**
	 * The WETU API Password
	 */
	public $api_password = false;

	/**
	 * The post types this works with.
	 */
	public $post_types = array();

	/**
	 * The previously attached images
	 *
	 * @var      array()
	 */
	public $found_attachments = array();

	/**
	 * The gallery ids for the found attachements
	 *
	 * @var      array()
	 */
	public $gallery_meta = array();

	/**
	 * The post ids to clean up (make sure the connected items are only singular)
	 *
	 * @var      array()
	 */
	public $cleanup_posts = array();

	/**
	 * A post => parent relationship array.
	 *
	 * @var      array()
	 */
	public $relation_meta = array();

	/**
	 * the featured image id
	 *
	 * @var      int
	 */
	public $featured_image = false;

	/**
	 * the banner image
	 *
	 * @var      int
	 */
	public $banner_image = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'compatible_version_check' ) );

		// Don't run anything else in the plugin, if we're on an incompatible PHP version
		if ( ! self::compatible_version() ) {
			return;
		}

		$this->set_variables();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array($this,'admin_scripts') ,11 );
		add_action( 'admin_menu', array( $this, 'register_importer_page' ),20 );
	}

	// ACTIVATION FUNCTIONS

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wetu-importer', FALSE, basename( WETU_IMPORTER_PATH ) . '/languages');
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		$this->post_types = array('accommodation','destination','tour');
		$temp_options = get_option('_lsx-to_settings',false);

		if(isset($temp_options[$this->plugin_slug])) {
			$this->options = $temp_options[$this->plugin_slug];

			$this->api_key = false;
			$this->api_username = false;
			$this->api_password = false;
			if (false !== $temp_options) {
				if (isset($temp_options['api']['wetu_api_key']) && '' !== $temp_options['api']['wetu_api_key']) {
					$this->api_key = $temp_options['api']['wetu_api_key'];
				}
				if (isset($temp_options['api']['wetu_api_username']) && '' !== $temp_options['api']['wetu_api_username']) {
					$this->api_username = $temp_options['api']['wetu_api_username'];
				}
				if (isset($temp_options['api']['wetu_api_password']) && '' !== $temp_options['api']['wetu_api_password']) {
					$this->api_password = $temp_options['api']['wetu_api_password'];
				}

				if (isset($temp_options[$this->plugin_slug]) && !empty($temp_options[$this->plugin_slug]) && isset($this->options['image_scaling'])) {
					$this->scale_images = true;
					$width = '800';
					if (isset($this->options['width']) && '' !== $this->options['width']) {
						$width = $this->options['width'];
					}
					$height = '600';
					if (isset($this->options['height']) && '' !== $this->options['height']) {
						$height = $this->options['height'];
					}
					$cropping = 'raw';
					if (isset($this->options['cropping']) && '' !== $this->options['cropping']) {
						$cropping = $this->options['cropping'];
					}
					$this->image_scaling_url = 'https://wetu.com/ImageHandler/' . $cropping . $width . 'x' . $height . '/';
				}
			}
		}
	}

	// COMPATABILITY FUNCTIONS

	/**
	 * On plugin activation
	 *
	 * @since 1.0.0
	 */
	public static function register_activation_hook() {
		self::compatible_version_check_on_activation();
	}
	
	/**
	 * Check if the PHP version is compatible.
	 *
	 * @since 1.0.0
	 */
	public static function compatible_version() {
		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			return false;
		}

		return true;
	}
	
	/**
	 * The backup sanity check, in case the plugin is activated in a weird way,
	 * or the versions change after activation.
	 *
	 * @since 1.0.0
	 */
	public function compatible_version_check() {
		if ( ! self::compatible_version() ) {
			if ( is_plugin_active( plugin_basename( WETU_IMPORTER_CORE ) ) ) {
				deactivate_plugins( plugin_basename( WETU_IMPORTER_CORE ) );
				add_action( 'admin_notices', array( $this, 'compatible_version_notice' ) );
				
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}
	
	/**
	 * Display the notice related with the older version from PHP.
	 *
	 * @since 1.0.0
	 */
	public function compatible_version_notice() {
		$class = 'notice notice-error';
		$message = esc_html__( 'Wetu Importer Plugin requires PHP 5.6 or higher.', 'wetu-importer' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_html( $class ), esc_html( $message ) );
	}
	
	/**
	 * The primary sanity check, automatically disable the plugin on activation if it doesn't
	 * meet minimum requirements.
	 *
	 * @since 1.0.0
	 */
	public static function compatible_version_check_on_activation() {
		if ( ! self::compatible_version() ) {
			deactivate_plugins( plugin_basename( WETU_IMPORTER_CORE ) );
			wp_die( esc_html__( 'Wetu Importer Plugin requires PHP 5.6 or higher.', 'wetu-importer' ) );
		}
	}

	// DISPLAY FUNCTIONS
	/**
	 * Registers the admin page which will house the importer form.
	 */
	public function register_importer_page() {
		add_submenu_page( 'tour-operator',esc_html__( 'Importer', 'tour-operator' ), esc_html__( 'Importer', 'tour-operator' ), 'manage_options', 'wetu-importer', array( $this, 'display_page' ) );
	}

	/**
	 * Enqueue the JS needed to contact wetu and return your result.
	 */
	public function admin_scripts() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$min = '';
		} else {
			$min = '.min';
		}
		$min = '';

		if(is_admin() && isset($_GET['page']) && $this->plugin_slug === $_GET['page']){
			wp_enqueue_script( 'wetu-importers-script', WETU_IMPORTER_URL . 'assets/js/wetu-importer' . $min . '.js', array( 'jquery' ), WETU_IMPORTER_VER, true );
			wp_localize_script( 'wetu-importers-script', 'lsx_tour_importer_params', array(
				'ajax_url' => admin_url('admin-ajax.php'),
			) );
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
        <div class="wrap">
			<?php screen_icon(); ?>

			<?php
            $tab = 'default';
            if(isset($_GET['tab'])) {
				$tab = $_GET['tab'];
			}
            switch($tab){
                case 'accommodation':
                    $wetu_importer_accommodation = new WETU_Importer_Accommodation();
                    $wetu_importer_accommodation->display_page();
                    break;

                case 'destination':

                    $wetu_importer_destination = new WETU_Importer_Destination();
                    $wetu_importer_destination->display_page();
                    break;

                case 'tour':
                    $wetu_importer_tours = new WETU_Importer_Tours();
                    $wetu_importer_tours->display_page();
                    break;

                default:
                    $wetu_importer_admin = new WETU_Importer_Admin();
                    $wetu_importer_admin->display_page();
                    break;
            }
			?>
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
	 * The header of the item list
	 */
	public function table_header() {
		?>
        <thead>
        <tr>
            <th style="" class="manage-column column-cb check-column" id="cb" scope="col">
                <label for="cb-select-all-1" class="screen-reader-text">Select All</label>
                <input type="checkbox" id="cb-select-all-1">
            </th>
            <th style="" class="manage-column column-title " id="title" style="width:50%;" scope="col">Title</th>
            <th style="" class="manage-column column-date" id="date" scope="col">Date</th>
            <th style="" class="manage-column column-ssid" id="ssid" scope="col">WETU ID</th>
        </tr>
        </thead>
		<?php
	}

	/**
	 * The footer of the item list
	 */
	public function table_footer() {
		?>
        <tfoot>
        <tr>
            <th style="" class="manage-column column-cb check-column" id="cb" scope="col">
                <label for="cb-select-all-1" class="screen-reader-text">Select All</label>
                <input type="checkbox" id="cb-select-all-1">
            </th>
            <th style="" class="manage-column column-title" scope="col">Title</th>
            <th style="" class="manage-column column-date" scope="col">Date</th>
            <th style="" class="manage-column column-ssid" scope="col">WETU ID</th>
        </tr>
        </tfoot>
		<?php
	}

	/**
	 * Displays the importers navigation
	 *
	 * @param $tab string
	 */
	public function navigation($tab='') {
		$post_types = array(
			'tour'              => esc_attr('Tours','wetu-importer'),
			'accommodation'     => esc_attr('Accommodation','wetu-importer'),
			'destination'       => esc_attr('Destinations','wetu-importer'),
		);
		echo '<div class="wet-navigation"><div class="subsubsub"><a class="'.$this->itemd($tab,'','current',false).'" href="'.admin_url('admin.php').'?page='.$this->plugin_slug.'">'.esc_attr('Home','wetu-importer').'</a>';
		foreach($post_types as $post_type => $label){
			echo ' | <a class="'.$this->itemd($tab,$post_type,'current',false).'" href="'.admin_url('admin.php').'?page='.$this->plugin_slug.'&tab='.$post_type.'">'.$label.'</a>';
		}
		echo '</div><br clear="both"/></div>';
	}

	/**
	 * set_taxonomy with some terms
	 */
	public function team_member_checkboxes($selected=array()) {
		if(post_type_exists('team')) { ?>
            <ul>
				<?php
				$team_args=array(
					'post_type'	=>	'team',
					'post_status' => 'publish',
					'nopagin' => true,
					'fields' => 'ids'
				);
				$team_members = new WP_Query($team_args);
				if($team_members->have_posts()){
					foreach($team_members->posts as $member){ ?>
                        <li><input class="team" <?php $this->checked($selected,$member); ?> type="checkbox" value="<?php echo $member; ?>" /> <?php echo get_the_title($member); ?></li>
					<?php }
				}else{ ?>
                    <li><input class="team" type="checkbox" value="0" /> <?php _e('None','wetu-importer'); ?></li>
				<?php }
				?>
            </ul>
		<?php }
	}


	// GENERAL FUNCTIONS

	/**
	 * Checks to see if an item is checked.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $echo bool
	 */
	public function checked($haystack=false,$needle='',$echo=true) {
		$return = $this->itemd($haystack,$needle,'checked');
		if('' !== $return) {
			if (true === $echo) {
				echo $return;
			} else {
				return $return;
			}
		}
	}

	/**
	 * Checks to see if an item is checked.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $echo bool
	 */
	public function selected($haystack=false,$needle='',$echo=true) {
		$return = $this->itemd($haystack,$needle,'selected');
		if('' !== $return) {
			if (true === $echo) {
				echo $return;
			} else {
				return $return;
			}
		}
	}

	/**
	 * Checks to see if an item is selected. If $echo is false,  it will return the $type if conditions are true.
	 *
	 * @param $haystack array|string
	 * @param $needle string
	 * @param $type string
	 * @param $wrap bool
	 * @return $html string
	 */
	public function itemd($haystack=false,$needle='',$type='',$wrap=true) {
		$html = '';
		if('' !== $type) {
			if (!is_array($haystack)) {
				$haystack = array($haystack);
			}
			if (in_array($needle, $haystack)) {
				if(true === $wrap || 'true' === $wrap) {
					$html = $type . '"' . $type . '"';
				}else{
					$html = $type;
				}
			}
		}
		return $html;

	}

	/**
	 * grabs any attachments for the current item
	 */
	public function find_attachments($id=false) {
		if(false !== $id){
			if(empty($this->found_attachments)){

				$attachments_args = array(
					'post_parent' => $id,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'order' => 'ASC',
					'nopagin' => 'true',
					'posts_per_page' => '-1'
				);

				$attachments = new WP_Query($attachments_args);
				if($attachments->have_posts()){
					foreach($attachments->posts as $attachment){
						$this->found_attachments[$attachment->ID] = str_replace(array('.jpg','.png','.jpeg'),'',$attachment->post_title);
						$this->gallery_meta[] = $attachment->ID;
					}
				}
			}
		}
	}


	// CUSTOM FIELD FUNCTIONS

	/**
	 * Saves the room data
	 */
	public function save_custom_field($value=false,$meta_key,$id,$decrease=false,$unique=true) {
		if(false !== $value){
			if(false !== $decrease){
				$value = intval($value);
				$value--;
			}
			$prev = get_post_meta($id,$meta_key,true);

			if(false !== $id && '0' !== $id && false !== $prev && true === $unique){
				update_post_meta($id,$meta_key,$value,$prev);
			}else{
				add_post_meta($id,$meta_key,$value,$unique);
			}
		}
	}

	/**
	 * Grabs the custom fields,  and resaves an array of unique items.
	 */
	public function cleanup_posts() {
		if(!empty($this->cleanup_posts)){
			foreach($this->cleanup_posts as $id => $key) {
				$prev_items = get_post_meta($id, $key, false);
				$new_items = array_unique($prev_items);
				delete_post_meta($id, $key);
				foreach($new_items as $new_item) {
					add_post_meta($id, $key, $new_item, false);
				}
			}
		}
	}

	// TAXONOMY FUNCTIONS

	/**
	 * set_taxonomy with some terms
	 */
	public function set_taxonomy($taxonomy,$terms,$id) {
		$result=array();
		if(!empty($data))
		{
			foreach($data as $k)
			{
				if($id)
				{
					if(!$term = term_exists(trim($k), $tax))
					{
						$term = wp_insert_term(trim($k), $tax);
						if ( is_wp_error($term) )
						{
							echo $term->get_error_message();
						}
						else
						{
							wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
						}
					}
					else
					{
						wp_set_object_terms( $id, intval($term['term_id']), $taxonomy,true);
					}
				}
				else
				{
					$result[]=trim($k);
				}
			}
		}
		return $result;
	}

	public function set_term($id=false,$name=false,$taxonomy=false,$parent=false){
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
	 * set_taxonomy with some terms
	 */
	public function taxonomy_checkboxes($taxonomy=false,$selected=array()) {
		$return = '';
		if(false !== $taxonomy){
			$return .= '<ul>';
			$terms = get_terms(array('taxonomy'=>$taxonomy,'hide_empty'=>false));

			if(!is_wp_error($terms)){
				foreach($terms as $term){
					$return .= '<li><input class="'.$taxonomy.'" '.$this->checked($selected,$term->term_id,false).' type="checkbox" value="'.$term->term_id.'" /> '.$term->name.'</li>';
				}
			}else{
				$return .= '<li><input type="checkbox" value="" /> '.__('None','wetu-importer').'</li>';
			}
			$return .= '</ul>';
		}
		return $return;
	}

	// MAP FUNCTIONS
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

	// IMAGE FUNCTIONS

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
$wetu_importer = new WETU_Importer();
