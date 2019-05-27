<?php
/**
 * @package   WETU_Importer_Tours
 * @author    LightSpeed
 * @license   GPL-3+
 * @link
 * @copyright 2017 LightSpeed
 **/

class WETU_Importer_Tours extends WETU_Importer {

	/**
	 * The url to list items from WETU
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $tab_slug = 'tour';

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
	 * Holds a list of any current accommodation
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $current_accommodation = false;

	/**
	 * Holds a list of any current destinations
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $current_destinations = false;

	/**
	 * Holds a list of the destination and the image it needs to grab.
	 *
	 * @since 0.0.1
	 *
	 * @var      string
	 */
	public $destination_images = false;

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
	public $tour_options = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->set_variables();
	}

	/**
	 * Sets the variables used throughout the plugin.
	 */
	public function set_variables() {
		parent::set_variables();

		//https://wetu.com/API/Itinerary/V8/Get
		if ( false !== $this->api_username && false !== $this->api_password ) {
			$this->url    = 'https://wetu.com/API/Itinerary/';
			$this->url_qs = 'username=' . $this->api_username . '&password=' . $this->api_password;
		} elseif ( false !== $this->api_key ) {
			$this->url    = 'https://wetu.com/API/Itinerary/' . $this->api_key;
			$this->url_qs = '';
		}

		$temp_options = get_option( '_lsx-to_settings',false );

		if ( false !== $temp_options && isset( $temp_options[ $this->plugin_slug ] ) && ! empty( $temp_options[ $this->plugin_slug ] ) ) {
			$this->options = $temp_options[ $this->plugin_slug ];
		}

		$tour_options = get_option( 'wetu_importer_tour_settings',false );

		if ( false !== $tour_options ) {
			$this->tour_options = $tour_options;
		}
	}

	/**
	 * Display the importer administration screen
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<?php $this->navigation( 'tour' ); ?>

			<?php $this->update_options_form(); ?>

			<?php $this->search_form(); ?>

			<form method="get" action="" id="posts-filter">
				<input type="hidden" name="post_type" class="post_type" value="<?php echo esc_attr( $this->tab_slug ); ?>" />

				<p><input class="button button-primary add" type="button" value="<?php esc_html_e( 'Add to List','wetu-importer' ); ?>" />
					<input class="button button-primary clear" type="button" value="<?php esc_html_e( 'Clear','wetu-importer' ); ?>" />
				</p>

				<table class="wp-list-table widefat fixed posts">
					<?php $this->table_header(); ?>

					<tbody id="the-list">
						<tr class="post-0 type-tour status-none" id="post-0">
							<th class="check-column" scope="row">
								<label for="cb-select-0" class="screen-reader-text"><?php esc_html_e( 'Enter a title to search for and press enter','wetu-importer' ); ?></label>
							</th>
							<td class="date column-date column-ref" colspan="4">
								<strong>
									<?php esc_html_e( 'Search for tours using the search form above','wetu-importer' ); ?>
								</strong>							
							</td>
						</tr>
					</tbody>

					<?php $this->table_footer(); ?>

				</table>

				<p><input class="button button-primary add" type="button" value="<?php esc_html_e( 'Add to List','wetu-importer' ); ?>" />
					<input class="button button-primary clear" type="button" value="<?php esc_html_e( 'Clear','wetu-importer' ); ?>" />
				</p>
			</form>

			<div style="display:none;" class="import-list-wrapper">
				<br />
				<form method="get" action="" id="import-list">

					<div class="row">
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'What content to Sync from WETU' ); ?></h3>
							<ul>
								<li><input class="content select-all" <?php $this->checked( $this->tour_options,'all' ); ?> type="checkbox"name="content[]"  value="all" /> <?php esc_html_e( 'Select All','wetu-importer' ); ?></li>

								<?php if ( isset( $this->options ) && ! isset( $this->options['disable_tour_descriptions'] ) ) { ?>
									<li><input class="content" <?php $this->checked( $this->tour_options,'description' ); ?> type="checkbox" name="content[]" value="description" /> <?php esc_html_e( 'Description','wetu-importer' ); ?></li>
								<?php } ?>

								<li><input class="content" <?php $this->checked( $this->tour_options,'price' ); ?> type="checkbox" name="content[]" value="price" /> <?php esc_html_e( 'Price','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'duration' ); ?> type="checkbox" name="content[]" value="duration" /> <?php esc_html_e( 'Duration','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'group_size' ); ?> type="checkbox" name="content[]" value="group_size" /> <?php esc_html_e( 'Group Size','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'category' ); ?> type="checkbox" name="content[]" value="category" /> <?php esc_html_e( 'Category','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'tags' ); ?> type="checkbox" name="content[]" value="tags" /> <?php esc_html_e( 'Tags','wetu-importer' ); ?></li>

								<li><input class="content" <?php $this->checked( $this->tour_options,'itineraries' ); ?> type="checkbox" name="content[]" value="itineraries" /> <?php esc_html_e( 'Itinerary Days','wetu-importer' ); ?></li>

								<?php
								/*
								if ( class_exists( 'LSX_TO_Maps' ) ) { ?>
								<li><input class="content" <?php $this->checked($this->tour_options,'map'); ?> type="checkbox" name="content[]" value="map" /> <?php esc_html_e('Map Coordinates (generates a KML file)','wetu-importer'); ?></li>
								<?php } 
								*/
								?>
							</ul>
						</div>
						<div class="settings-all" style="width:30%;display:block;float:left;">
							<h3><?php esc_html_e( 'Itinerary Info' ); ?></h3>
							<ul>
								<li><input class="content" <?php $this->checked( $this->tour_options,'itinerary_description' ); ?> type="checkbox" name="content[]" value="itinerary_description" /> <?php esc_html_e( 'Description','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'itinerary_included' ); ?> type="checkbox" name="content[]" value="itinerary_included" /> <?php esc_html_e( 'Included','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'itinerary_excluded' ); ?> type="checkbox" name="content[]" value="itinerary_excluded" /> <?php esc_html_e( 'Excluded','wetu-importer' ); ?></li>
							</ul>

							<h4><?php esc_html_e( 'Additional Content' ); ?></h4>
							<ul>
								<li><input class="content" <?php $this->checked( $this->tour_options,'accommodation' ); ?> type="checkbox" name="content[]" value="accommodation" /> <?php esc_html_e( 'Sync Accommodation','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'destination' ); ?> type="checkbox" name="content[]" value="destination" /> <?php esc_html_e( 'Sync Destinations','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'featured_image' ); ?> type="checkbox" name="content[]" value="featured_image" /> <?php esc_html_e( 'Featured Image','wetu-importer' ); ?></li>
								<li><input class="content" <?php $this->checked( $this->tour_options,'banner_image' ); ?> type="checkbox" name="content[]" value="banner_image" /> <?php esc_html_e( 'Banner Image','wetu-importer' ); ?></li>
							</ul>
						</div>
						<?php if ( class_exists( 'LSX_TO_Team' ) ) { ?>
							<div style="width:30%;display:block;float:left;">
								<h3><?php esc_html_e( 'Assign a Team Member' ); ?></h3>
								<?php $this->team_member_checkboxes( $this->tour_options ); ?>
							</div>
						<?php } ?>

						<br clear="both" />
					</div>

					<h3><?php esc_html_e( 'Your List' ); ?></h3>
					<p><input class="button button-primary" type="submit" value="<?php esc_html_e( 'Sync','wetu-importer' ); ?>" /></p>
					<table class="wp-list-table widefat fixed posts">
						<?php $this->table_header(); ?>

						<tbody>

						</tbody>

						<?php $this->table_footer(); ?>

					</table>

					<p><input class="button button-primary" type="submit" value="<?php esc_html_e( 'Sync','wetu-importer' ); ?>" /></p>
				</form>
			</div>

			<div style="display:none;" class="completed-list-wrapper">
				<h3><?php esc_html_e( 'Completed', 'wetu-importer' ); ?> - <small><?php esc_html_e( 'Import your', 'wetu-importer' ); ?> <a href="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=<?php echo esc_attr( $this->plugin_slug ); ?>&tab=accommodation"><?php esc_html_e( 'accommodation' ); ?></a> <?php esc_html_e( 'next','wetu-importer' ); ?></small></h3>
				<ul>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * search_form
	 */
	public function update_options_form() {
		$tours = get_transient( 'lsx_ti_tours' );

		echo '<div class="wetu-status tour-wetu-status"><h3>' . esc_html__( 'Wetu Status','wetu-importer' ) . ' - ';

		if ( '' === $tours || false === $tours || isset( $_GET['refresh_tours'] ) ) {
			$result = $this->update_options();

			if ( true === $result ) {
				echo '<span style="color:green;">' . esc_attr( 'Connected','wetu-importer' ) . '</span>';
				echo ' - <small><a href="#">' . esc_attr( 'Refresh','wetu-importer' ) . '</a></small>';
			} else {
				echo '<span style="color:red;">' . wp_kses_post( $result ) . '</span>';
			}
		} else {
			echo '<span style="color:green;">' . esc_attr( 'Connected','wetu-importer' ) . '</span> - <small><a href="#">' . esc_attr( 'Refresh','wetu-importer' ) . '</a></small>';
		}

		echo '</h3>';

		$form_options = get_option( 'lsx_ti_tours_api_options' );
		if ( false === $form_options ) {
			$form_options = array( 0 );
		}
		?>
		<form method="get" class="tour-refresh-form" action="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->plugin_slug ); ?>" />
			<input type="hidden" name="tab" value="tour" />
			<input type="hidden" name="refresh_tours" value="true" />

			<p class="tour-search-options">
				<label for="own"><input class="content" <?php if ( in_array( 'own',$form_options ) ) { echo 'checked'; } ?> type="checkbox" name="own" value="true" /> <?php esc_html_e( 'Own Tours','wetu-importer' ); ?> </label>
			</p>

			<p class="tour-search-options">
				<label for="type"><input class="content" <?php if ( in_array( 'allitineraries',$form_options ) ) { echo 'checked'; } ?> type="radio" name="type[]" value="allitineraries" /> <?php esc_html_e( 'All','wetu-importer' ); ?></label>
				<label for="type"><input class="content" <?php if ( in_array( 'sample',$form_options ) ) { echo 'checked'; } ?> type="radio" name="type[]" value="sample" /> <?php esc_html_e( 'Sample','wetu-importer' ); ?></label>
				<label for="type"><input class="content" <?php if ( in_array( 'personal',$form_options ) ) { echo 'checked'; } ?> type="radio" name="type[]" value="personal" /> <?php esc_html_e( 'Personal','wetu-importer' ); ?></label>
			</p>

			<p><input class="button button-primary submit" type="submit" value="<?php echo esc_attr( 'Refresh Tours','wetu-importer' ); ?>"></p>
		</form>
		<br />
		<?php
		echo '</div>';
	}

	/**
	 * Save the list of Tours into an option
	 */
	public function update_options() {
		$own = '';
		$options = array();

		delete_option( 'lsx_ti_tours_api_options' );

		if ( isset( $_GET['own'] ) ) {
			$this->url_qs .= '&own=true';
			$options[] = 'own';
		}

		if ( isset( $_GET['type'] ) ) {
			$this->url_qs .= '&type=' . implode( '',$_GET['type'] );
			$options[] = implode( '',$_GET['type'] );
		}

		$this->url_qs .= '&results=2000';

		add_option( 'lsx_ti_tours_api_options',$options );

		$data = wp_remote_get( $this->url . '/V8/List?' . $this->url_qs );
		$tours = json_decode( wp_remote_retrieve_body( $data ), true );

		if ( isset( $tours['error'] ) ) {
			return $tours['error'];
		} elseif ( isset( $tours['itineraries'] ) && ! empty( $tours['itineraries'] ) ) {
			set_transient( 'lsx_ti_tours',$tours['itineraries'],60 * 60 * 2 );
			return true;
		}
	}

	/**
	 * Grab all the current tour posts via the lsx_wetu_id field.
	 */
	public function find_current_tours() {
		global $wpdb;
		$return = array();

		$current_tours = $wpdb->get_results("
			SELECT key1.post_id,key1.meta_value
			FROM {$wpdb->postmeta} key1

			INNER JOIN  {$wpdb->posts} key2
			ON key1.post_id = key2.ID

			WHERE key1.meta_key = 'lsx_wetu_id'
			AND key2.post_type = 'tour'

			LIMIT 0,500
		");

		if ( null !== $current_tours && ! empty( $current_tours ) ) {
			foreach ( $current_tours as $tour ) {
				$return[ $tour->meta_value ] = $tour;
			}
		}

		return $return;
	}

	/**
	 * Run through the accommodation grabbed from the DB.
	 */
	public function process_ajax_search() {
		$return = false;

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_tour_importer' && isset( $_POST['type'] ) && $_POST['type'] === $this->tab_slug ) {
			$tours = get_transient( 'lsx_ti_tours' );

			if ( false !== $tours ) {

				$searched_items = false;

				// @codingStandardsIgnoreLine
				if ( isset( $_POST['keyword'] ) ) {
					// @codingStandardsIgnoreLine
					$keyphrases = $_POST['keyword'];
				} else {
					$keyphrases = array( 0 );
				}

				if ( ! is_array( $keyphrases ) ) {
					$keyphrases = array( $keyphrases );
				}
				foreach ( $keyphrases as &$keyword ) {
					$keyword = ltrim( rtrim( $keyword ) );
				}

				$post_status = false;
				if ( in_array( 'publish',$keyphrases ) ) {
					$post_status = 'publish';
				}
				if ( in_array( 'pending',$keyphrases ) ) {
					$post_status = 'pending';
				}
				if ( in_array( 'draft',$keyphrases ) ) {
					$post_status = 'draft';
				}
				if ( in_array( 'import',$keyphrases ) ) {
					$post_status = 'import';
				}

				if ( ! empty( $tours ) ) {
					$current_tours = $this->find_current_tours();

					foreach ( $tours as $row_key => $row ) {
						if ( isset( $row['is_disabled'] ) && true === $row['is_disabled'] ) {
							continue;
						}

						/*if('Sample' === $row['type']){
							continue;
						}*/

						//If this is a current tour, add its ID to the row.
						$row['post_id'] = 0;

						if ( false !== $current_tours && array_key_exists( $row['identifier'], $current_tours ) ) {
							$row['post_id'] = $current_tours[ $row['identifier'] ]->post_id;
						}

						//If we are searching for
						if ( false !== $post_status ) {
							if ( 'import' === $post_status ) {

								if ( 0 !== $row['post_id'] ) {
									continue;
								} else {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row );
								}
							} else {
								if ( 0 === $row['post_id'] ) {
									continue;
								} else {
									$current_status = get_post_status( $row['post_id'] );

									if ( $current_status !== $post_status ) {
										continue;
									}
								}

								$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row );
							}
						} else {
							//Search through each keyword.
							foreach ( $keyphrases as $keyphrase ) {

								//Make sure the keyphrase is turned into an array
								$keywords = explode( ' ',$keyphrase );
								if ( ! is_array( $keywords ) ) {
									$keywords = array( $keywords );
								}

								if ( $this->multineedle_stripos( ltrim( rtrim( $row['name'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row );
								} else if ( $this->multineedle_stripos( ltrim( rtrim( $row['reference_number'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row );
								}else if ( $this->multineedle_stripos( ltrim( rtrim( $row['identifier_key'] ) ), $keywords ) !== false ) {
									$searched_items[ sanitize_title( $row['name'] ) . '-' . $row['identifier'] ] = $this->format_row( $row );
								}

								//Add in the year and ref
							}
						}
					}
				}

				if ( false !== $searched_items ) {
					ksort( $searched_items );
					$return = implode( $searched_items );
				}
			}
			print_r( $return );
			die();
		}
	}

	/**
	 * Formats the row for output on the screen.
	 */
	public function format_row( $row = false ) {
		if ( false !== $row ) {
			$status = 'import';

			if ( 0 !== $row['post_id'] ) {
				$status = '<a href="' . admin_url( '/post.php?post=' . $row['post_id'] . '&action=edit' ) . '" target="_blank">' . get_post_status( $row['post_id'] ) . '</a>';
			}

			$row_html = '
			<tr class="post-' . $row['post_id'] . ' type-tour" id="post-' . $row['post_id'] . '">
				<th class="check-column" scope="row">
					<label for="cb-select-' . $row['identifier'] . '" class="screen-reader-text">' . $row['name'] . '</label>
					<input type="checkbox" data-identifier="' . $row['identifier'] . '" value="' . $row['post_id'] . '" name="post[]" id="cb-select-' . $row['identifier'] . '">
				</th>			
				<td class="post-title page-title column-title">
					<strong>' . $row['name'] . '</strong> - ' . $status . '
				</td>
				<td class="date column-date">
					' . $row['reference_number'] . '
				</td>				
				<td class="date column-date">
					<abbr title="' . date( 'Y/m/d',strtotime( $row['last_modified'] ) ) . '">' . date( 'Y/m/d',strtotime( $row['last_modified'] ) ) . '</abbr><br>Last Modified
				</td>
				<td class="ssid column-ssid">
					' . $row['identifier'] . '
				</td>
			</tr>';
			return $row_html;
		}
	}

	/**
	 * Connect to wetu
	 */
	public function process_ajax_import( $force = false ) {
		$return = false;

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'lsx_import_items' && isset( $_POST['type'] ) && $_POST['type'] === $this->tab_slug && isset( $_POST['wetu_id'] ) ) {

			// @codingStandardsIgnoreLine
			$wetu_id = $_POST['wetu_id'];

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['post_id'] ) ) {
				// @codingStandardsIgnoreLine
				$post_id = $_POST['post_id'];
			} else {
				$post_id = 0;
			}

			delete_option( 'wetu_importer_tour_settings' );

			// @codingStandardsIgnoreLine
			if ( isset( $_POST['content'] ) && is_array( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
				// @codingStandardsIgnoreLine
				$content = $_POST['content'];
				add_option( 'wetu_importer_tour_settings',$content );
			} else {
				$content = false;
			}
			$jdata = wp_remote_get( 'https://wetu.com/API/Itinerary/V8/Get?id=' . $wetu_id );

			if ( $jdata ) {
				$jdata = json_decode( $jdata['body'], true );
				if ( ! empty( $jdata ) && isset( $jdata['response'] ) && isset( $jdata['response']['code'] ) && '200' === $jdata['response']['code'] ) {
					$return = $this->import_row( $jdata, $wetu_id, $post_id, $content );
					$this->format_completed_row( $return );
					$this->save_queue();
					$this->cleanup_posts();
					$this->attach_destination_images( $content );
					$this->clean_attached_destinations( $return );
				} else {
					$this->format_error( esc_html__( 'There was a problem importing your tour, please try refreshing the page.','wetu-importer' ) );
				}
			} else {
				$this->format_error( esc_html__( 'There was a problem importing your tour, please contact support.', 'wetu-importer' ) );
			}
		}
	}

	/**
	 * Amends the tours destinations instead of replace.
	 *
	 * @param $id string
	 * @return void
	 */
	public function clean_attached_destinations( $id ) {
		$current_connections = get_post_meta( $id, 'destination_to_tour', false );
		delete_post_meta( $id,'destination_to_tour' );
		$current_connections = array_unique( $current_connections );

		foreach ( $current_connections as $connection ) {
			add_post_meta( $id, 'destination_to_tour',$connection, false );
		}
	}

	/**
	 * Connect to wetu
	 *
	 * @param $data array
	 * @param $wetu_id string
	 */
	public function import_row( $data, $wetu_id, $id = 0, $importable_content = false, $old1 = false, $old2 = false ) {
		$post_name = '';
		$data_post_content = '';
		$data_post_excerpt = '';

		$current_post = get_post( $id );

		$post = array(
			'post_type' => 'tour',
		);

		$content_used_general_description = false;

		if ( false !== $importable_content && in_array( 'description', $importable_content ) ) {
			$data_post_content = $current_post->post_content;
			if ( isset( $data['summary'] ) && ! empty( $data['summary'] ) ) {
				$data_post_content = $data['summary'];
			}
			$post['post_content'] = $data_post_content;
		}

		//Create or update the post
		if ( false !== $id && '0' !== $id ) {
			$post['ID'] = $id;
			$post['post_status'] = 'publish';
			$id = wp_update_post( $post );
			$prev_date = get_post_meta( $id,'lsx_wetu_modified_date',true );
			update_post_meta( $id,'lsx_wetu_modified_date',strtotime( $data['last_modified'] ),$prev_date );

			//If the logger is enabled then log the data being saved.
			if ( $this->debug_enabled ) {
				$this->logger->log( 'wetu-importer', 'Creating Tour', print_r( $post, true ) );
			}

		} else {
			//Set the name
			if ( isset( $data['name'] ) ) {
				$post_name = wp_unique_post_slug( sanitize_title( $data['name'] ),$id, 'draft', 'tour', 0 );
			}

			if ( ! isset( $post['post_content'] ) ) {
				$post['post_content'] = " ";
			}

			$post['post_name'] = $post_name;
			$post['post_title'] = $data['name'];
			$post['post_status'] = 'publish';

			//If the logger is enabled then log the data being saved.
			if ( $this->debug_enabled ) {
				$this->logger->log( 'wetu-importer', 'Creating Tour', print_r( $post, true ) );
			}
			$id = wp_insert_post( $post );

			//Save the WETU ID and the Last date it was modified.
			if ( false !== $id ) {
				add_post_meta( $id,'lsx_wetu_id',$wetu_id );
				add_post_meta( $id,'lsx_wetu_modified_date',strtotime( $data['last_modified'] ) );
			}
		}

		//Set the price
		if ( false !== $importable_content && in_array( 'price',$importable_content ) ) {
			$this->set_price( $data,$id );
		}

		//Set the Duration
		if ( false !== $importable_content && in_array( 'duration',$importable_content ) ) {
			$this->set_duration( $data,$id );
		}

		//Set the Group Size
		if ( false !== $importable_content && in_array( 'group_size',$importable_content ) ) {
			$this->set_group_size( $data,$id );
		}		

		if ( false !== $importable_content && in_array( 'itineraries',$importable_content ) && isset( $data['legs'] ) && ! empty( $data['legs'] ) ) {
			$this->process_itineraries( $data,$id,$importable_content );
		}

		if ( in_array( 'map',$importable_content ) && isset( $data['routes'] ) && ! empty( $data['routes'] ) ) {
			$this->set_map_data( $data,$id );
		}

		return $id;
	}

	/**
	 * A loop which runs through each leg on the tour.
	 */
	public function process_itineraries( $data, $id, $importable_content ) {
		$day_counter = 1;
		$leg_counter = 0;

		delete_post_meta( $id,'itinerary' );

		if ( false !== $importable_content && in_array( 'accommodation',$importable_content ) ) {
			delete_post_meta( $id,'accommodation_to_tour' );
		}
		if ( false !== $importable_content && in_array( 'destination',$importable_content ) ) {
			//delete_post_meta($id,'destination_to_tour');
			delete_post_meta( $id,'departs_from' );
			delete_post_meta( $id,'ends_in' );
		}

		$departs_from = false;
		$ends_in = false;

		foreach ( $data['legs'] as $leg ) {
			//Itinerary Accommodation
			$current_accommodation = false;
			if ( false !== $importable_content && in_array( 'accommodation',$importable_content ) ) {
				$current_accommodation = $this->set_accommodation( $leg,$id );
			}

			//Itinerary Destination
			$current_destination = false;
			if ( false !== $importable_content && in_array( 'destination',$importable_content ) ) {
				$current_destination = $this->set_destination( $leg,$id,$leg_counter );
			}

			//If the Nights are the same mount of days in the array,  then it isnt "By Destination"
			if ( ((1 <= (int) $leg['nights'] && isset( $leg['periods'] ))) || 0 === $leg['itinerary_leg_id'] ) {
				foreach ( $leg['periods'] as $day ) {
					$current_day = array();
					$current_day['title'] = esc_attr( 'Day ', 'wetu-importer' ) . $day_counter;

					//print_r('<pre>');print_r($day['notes']);print_r('</pre>');

					//Description
					if ( false !== $importable_content && in_array( 'itinerary_description',$importable_content ) && isset( $day['notes'] ) ) {
						$current_day['description'] = $day['notes'];
					} else {
						$current_day['description'] = '';
					}

					//Itinerary Gallery
					if ( false !== $importable_content && in_array( 'itinerary_gallery',$importable_content ) && isset( $day['images'] ) ) {
						$current_day['featured_image'] = '';
					} else {
						$current_day['featured_image'] = '';
					}

					//Accommodation
					if ( false !== $current_accommodation ) {
						$current_day['accommodation_to_tour'] = array( $current_accommodation );
					} else {
						$current_day['accommodation_to_tour'] = array();
					}

					//Destination
					if ( false !== $current_destination ) {
						$current_day['destination_to_tour'] = array( $current_destination );
					} else {
						$current_day['destination_to_tour'] = array();
					}

					//Included
					if ( false !== $importable_content && in_array( 'itinerary_included',$importable_content ) && isset( $day['included'] ) && '' !== $day['included'] ) {
						$current_day['included'] = $day['included'];
					} else {
						$current_day['included'] = '';
					}

					//Excluded
					if ( false !== $importable_content && in_array( 'itinerary_excluded',$importable_content ) && isset( $day['excluded'] ) && '' !== $day['excluded'] ) {
						$current_day['excluded'] = $day['excluded'];
					} else {
						$current_day['excluded'] = '';
					}

					$this->set_itinerary_day( $current_day,$id );
					$day_counter++;
				}
			} else {
				// This is for the by destination

				$current_day = array();
				$next_day_count = $day_counter + (int) $leg['nights'];
				$day_count_label = $next_day_count -1;

				$current_day['title'] = esc_attr( 'Day ','wetu-importer' ) . $day_counter;

				if ( 0 !== (int) $leg['nights'] ) {
					$current_day['title'] .= ' - ' . $day_count_label;
				}

				//Description
				if ( false !== $importable_content && in_array( 'itinerary_description',$importable_content ) && isset( $leg['notes'] ) ) {
					$current_day['description'] = $leg['notes'];
				} else {
					$current_day['description'] = '';
				}

				//Itinerary Gallery
				if ( false !== $importable_content && in_array( 'itinerary_gallery',$importable_content ) && isset( $leg['images'] ) ) {
					$current_day['featured_image'] = '';
				} else {
					$current_day['featured_image'] = '';
				}

				//Accommodation
				if ( false !== $current_accommodation ) {
					$current_day['accommodation_to_tour'] = array( $current_accommodation );
				} else {
					$current_day['accommodation_to_tour'] = array();
				}

				//Destination
				if ( false !== $current_destination ) {
					$current_day['destination_to_tour'] = array( $current_destination );
				} else {
					$current_day['destination_to_tour'] = array();
				}

				//Included
				if ( false !== $importable_content && in_array( 'itinerary_included',$importable_content ) && isset( $leg['included'] ) && '' !== $leg['included'] ) {
					$current_day['included'] = $leg['included'];
				} else {
					$current_day['included'] = '';
				}

				//Excluded
				if ( false !== $importable_content && in_array( 'itinerary_excluded',$importable_content ) && isset( $leg['excluded'] ) && '' !== $leg['excluded'] ) {
					$current_day['excluded'] = $leg['excluded'];
				} else {
					$current_day['excluded'] = '';
				}

				$this->set_itinerary_day( $current_day,$id );
				$day_counter = $next_day_count;
			}

			//If we are in the first leg,  and the destination was attached then save it as the departure field.
			if ( 0 === $leg_counter && false !== $current_destination ) {
				$departs_from = $current_destination;
			}

			//If its the last leg then save it as the ends in.
			// @codingStandardsIgnoreLine
			if ( $leg_counter === (count( $data['legs'] ) -2) && false !== $current_destination ) {
				$ends_in = $current_destination;
			}

			$leg_counter++;
		}

		if ( false !== $departs_from ) {
			add_post_meta( $id,'departs_from',$departs_from,true );
		}
		if ( false !== $ends_in ) {
			add_post_meta( $id,'ends_in',$ends_in,true );
		}
	}

	/**
	 * Run through your routes and save the points as a KML file.
	 */
	public function set_map_data( $data, $id, $zoom = 9 ) {
		if ( ! empty( $data['routes'] ) ) {
			delete_post_meta( $id,'wetu_map_points' );

			$points = array();

			foreach ( $data['routes'] as $route ) {

				if ( isset( $route['points'] ) && '' !== $route['points'] ) {

					$temp_points = explode( ';',$route['points'] );
					$point_counter = count( $temp_points );

					for ( $x = 0; $x <= $point_counter; $x++ ) {
						$y = $x + 1;
						$points[] = $temp_points[ $x ] . ',' . $temp_points[ $y ];
						$x++;
					}
				}
			}

			if ( ! empty( $points ) ) {
				$this->save_custom_field( implode( ' ',$points ),'wetu_map_points',$id,false,true );
			}
		}

	}

	// CLASS SPECIFIC FUNCTIONS

	/**
	 * Set the Itinerary Day
	 */
	public function set_itinerary_day( $day, $id ) {
		$this->save_custom_field( $day,'itinerary',$id,false,false );
	}

	/**
	 * Set the price
	 */
	public function set_price( $data, $id ) {
		//Price
		if ( isset( $data['price'] ) && '' !== $data['price'] ) {
			$price = preg_replace( '/[^0-9,.]/', '', $data['price'] );
			$this->save_custom_field( $price,'price',$id );
		}

		//Price includes
		if ( isset( $data['price_includes'] ) && '' !== $data['price_includes'] ) {
			$this->save_custom_field( $data['price_includes'],'included',$id );
		}

		//Price Excludes
		if ( isset( $data['price_excludes'] ) && '' !== $data['price_excludes'] ) {
			$this->save_custom_field( $data['price_excludes'],'not_included',$id );
		}
	}

	/**
	 * Set the duration
	 */
	public function set_duration( $data, $id ) {
		if ( isset( $data['days'] ) && ! empty( $data['days'] ) ) {
			$price = $data['days'];
			$price = preg_replace( '/[^0-9,.]/', '', $price );
			$this->save_custom_field( $price,'duration',$id );
		}
	}

	/**
	 * Set the group size
	 */
	public function set_group_size( $data, $id ) {
		if ( isset( $data['group_size'] ) && ! empty( $data['group_size'] ) ) {
			$group_size = $data['group_size'];
			$this->save_custom_field( $group_size,'group_size',$id );
		}
	}	

	/**
	 * Connects the Accommodation if its available
	 */
	public function set_accommodation( $day, $id ) {
		$ac_id = false;
		$this->current_accommodation = $this->find_current_accommodation();

		if ( isset( $day['content_entity_id'] ) && ! empty( $day['content_entity_id'] ) ) {
			if ( false !== $this->current_accommodation && ! empty( $this->current_accommodation ) && array_key_exists( $day['content_entity_id'],$this->current_accommodation ) ) {
				$ac_id = $this->current_accommodation[ $day['content_entity_id'] ];
			} else {
				$ac_id = wp_insert_post(array(
					'post_type' => 'accommodation',
					'post_status' => 'draft',
					'post_title' => $day['content_entity_id'],
				));

				$this->save_custom_field( $day['content_entity_id'],'lsx_wetu_id',$ac_id );
			}

			if ( '' !== $ac_id && false !== $ac_id ) {
				$this->save_custom_field( $ac_id,'accommodation_to_tour',$id,false,false );
				$this->save_custom_field( $id,'tour_to_accommodation',$ac_id,false,false );
				$this->queue_item( $ac_id );
			}
		}
		return $ac_id;
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 *
	 * @param $post_type string
	 * @return boolean / array
	 */
	public function find_current_accommodation( $post_type = 'accommodation' ) {
		global $wpdb;
		$accommodation = parent::find_current_accommodation( $post_type );
		$return = false;

		if ( ! empty( $accommodation ) ) {
			foreach ( $accommodation as $key => $acc ) {
				$return[ $acc->meta_value ] = $acc->post_id;
			}
		}

		return $return;
	}

	/**
	 * Grab all the current accommodation posts via the lsx_wetu_id field.
	 * @return boolean / array
	 */
	public function find_current_destinations() {
		return $this->find_current_accommodation( 'destination' );
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $day array
	 * @param $id string
	 * @return boolean / string
	 */
	public function set_destination( $day, $id, $leg_counter ) {
		$dest_id = false;
		$country_id = false;
		$this->current_destinations = $this->find_current_destinations();

		if ( isset( $day['destination_content_entity_id'] ) && ! empty( $day['destination_content_entity_id'] ) ) {
			if ( false !== $this->current_destinations && ! empty( $this->current_destinations ) && array_key_exists( $day['destination_content_entity_id'],$this->current_destinations ) ) {
				$dest_id = $this->current_destinations[ $day['destination_content_entity_id'] ];

				//TODO Check for attachments here.
				$this->destination_images[ $id ][] = array( $dest_id, $day['destination_content_entity_id'] );

				//Check if there is a country asigned.
				$potential_id = wp_get_post_parent_id( $dest_id );
				$country_wetu_id = get_post_meta( $potential_id,'lsx_wetu_id',true );

				if ( false !== $country_wetu_id ) {
					$country_id = $this->set_country( $country_wetu_id, $id );
				}
			} else {
				$destination_json = file_get_contents( 'https://wetu.com/API/Pins/' . $this->api_key . '/Get?ids=' . $day['destination_content_entity_id'] );

				if ( $destination_json ) {
					$destination_data = json_decode( $destination_json, true );

					if ( ! empty( $destination_data ) && ! isset( $destination_data['error'] ) ) {
						$destination_title = $day['destination_content_entity_id'];

						if ( isset( $destination_data[0]['name'] ) ) {
							$destination_title = $destination_data[0]['name'];
						}

						if ( isset( $destination_data[0]['map_object_id'] ) && isset( $destination_data[0]['position']['country_content_entity_id'] )
							&& $destination_data[0]['map_object_id'] !== $destination_data[0]['position']['country_content_entity_id'] ) {

							$country_id = $this->set_country( $destination_data[0]['position']['country_content_entity_id'], $id );
							// Save the destination so we can grab the tour featured image and banner from them
						}

						$dest_post = array(
							'post_type' => 'destination',
							'post_status' => 'draft',
							'post_title' => $destination_title,
						);

						if ( false !== $country_id ) {
							$dest_post['post_parent'] = $country_id;
						}
						$dest_id = wp_insert_post( $dest_post );

						//Make sure we register the
						$this->current_destinations[ $day['destination_content_entity_id'] ] = $dest_id;

						//If there are images attached then use the destination
						if ( isset( $destination_data[0]['content']['images'] ) && ! empty( $destination_data[0]['content']['images'] ) ) {
							$this->destination_images[ $id ][] = array( $dest_id, $day['destination_content_entity_id'] );
						}

						$this->save_custom_field( $day['destination_content_entity_id'], 'lsx_wetu_id', $dest_id );
					}
				}
			}

			if ( '' !== $dest_id && false !== $dest_id ) {
				$this->save_custom_field( $dest_id, 'destination_to_tour', $id, false, false );
				$this->save_custom_field( $id, 'tour_to_destination', $dest_id, false, false );

				//Save the item to display in the queue
				$this->queue_item( $dest_id );

				//Save the item to clean up the amount of connections.
				$this->cleanup_posts[ $dest_id ] = 'tour_to_destination';

				//Add this relation info so we can make sure certain items are set as countries.
				if ( 0 !== $country_id && false !== $country_id ) {
					$this->relation_meta[ $dest_id ] = $country_id;
					$this->relation_meta[ $country_id ] = 0;
				} else {
					$this->relation_meta[ $dest_id ] = 0;
				}
			}
		}
		return $dest_id;
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $dest_id string
	 * @param $country_id array
	 * @param $id string
	 *
	 * @return string
	 */
	public function set_country( $country_wetu_id, $id ) {
		$country_id = false;
		$this->current_destinations = $this->find_current_destinations();

		if ( false !== $this->current_destinations && ! empty( $this->current_destinations ) && array_key_exists( $country_wetu_id, $this->current_destinations ) ) {
			$country_id = $this->current_destinations[ $country_wetu_id ];
			$this->destination_images[ $id ][] = array( $country_id, $country_wetu_id );
		} else {
			$country_json = file_get_contents( 'https://wetu.com/API/Pins/' . $this->api_key . '/Get?ids=' . $country_wetu_id );

			if ( $country_json ) {
				$country_data = json_decode( $country_json, true );

				if ( ! empty( $country_data ) && ! isset( $country_data['error'] ) ) {

					//Format the title of the destination if its available,  otherwise default to the WETU ID.
					$country_title = $country_wetu_id;

					if ( isset( $country_data[0]['name'] ) ) {
						$country_title = $country_data[0]['name'];
					}

					$country_id = wp_insert_post(array(
						'post_type' => 'destination',
						'post_status' => 'draft',
						'post_title' => $country_title,
					));

					//add the country to the current destination stack
					$this->current_destinations[ $country_wetu_id ] = $country_id;

					// Check if there are images and save fore use later.
					if ( isset( $country_data[0]['content']['images'] ) && ! empty( $country_data[0]['content']['images'] ) ) {
						$this->destination_images[ $id ][] = array( $country_id,$country_wetu_id );
					}

					//Save the wetu field
					$this->save_custom_field( $country_wetu_id, 'lsx_wetu_id', $country_id );
				}
			}
		}

		if ( '' !== $country_id && false !== $country_id ) {
			$this->save_custom_field( $country_id, 'destination_to_tour', $id, false, false );
			$this->save_custom_field( $id, 'tour_to_destination', $country_id, false, false );
			$this->queue_item( $country_id );
			$this->cleanup_posts[ $country_id ] = 'tour_to_destination';

			return $country_id;
		}
	}

	/**
	 * Connects the destinations post type
	 *
	 * @param $dest_id string
	 * @param $country_id array
	 * @param $id string
	 *
	 * @return string
	 */
	public function attach_destination_images( $importable_content = array() ) {
		if ( false !== $this->destination_images ) {
			$this->shuffle_assoc( $this->destination_images );

			foreach ( $this->destination_images as $tour => $destinations ) {
				//$this->shuffle_assoc( $destinations );

				$image_set = false;
				$forced = false;

				foreach ( $destinations as $destination ) {
					if ( false === $image_set && false === $forced ) {
						$url = 'https://wetu.com/API/Pins/' . $this->api_key;
						$url_qs = '';

						$jdata = file_get_contents( $url . '/Get?' . $url_qs . '&ids=' . $destination[1] );

						if ( $jdata ) {
							$adata = json_decode( $jdata, true );

							if ( ! empty( $adata ) && ! empty( $adata[0]['content']['images'] ) ) {
								$this->find_attachments( $destination[0] );

								//Set the featured image
								if ( false !== $importable_content && in_array( 'featured_image', $importable_content ) ) {
									$image_set = $this->set_featured_image( $adata, $tour );
									if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
										$image_set = $this->set_banner_image( $adata, $tour );
										$forced = true;
									}
									continue;
								}
								if ( false !== $importable_content && in_array( 'banner_image', $importable_content ) ) {
									$image_set = $this->set_banner_image( $adata, $tour );
								}
							}
						}
					} else {
						continue;
					}
				}
			}
		}
	}

	/**
	 * Creates the main gallery data
	 */
	public function set_featured_image( $data, $id ) {
		$image_set = false;
		$counter = 0;

		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {
			foreach ( $data[0]['content']['images'] as $v ) {
				/*print_r('<pre>');
				print_r( $v );
				print_r('</pre>');*/

				if ( true === $image_set ) {
					$counter++;
					continue;
				}

				if ( ! $this->check_if_image_is_used( $v ) ) {
					$temp_featured_image = $this->attach_image( $v , $id );

					if ( false !== $temp_featured_image ) {
						$this->featured_image = $temp_featured_image;
						delete_post_meta( $id, '_thumbnail_id' );
						add_post_meta( $id, '_thumbnail_id', $this->featured_image, true );
						$image_set = true;
					}
				}

				$counter++;
			}
		}
		return $image_set;
	}

	/**
	 * Sets a banner image
	 */
	public function set_banner_image( $data, $id, $content = array( 'none' ) ) {
		$image_set = false;
		$counter = 0;

		if ( is_array( $data[0]['content']['images'] ) && ! empty( $data[0]['content']['images'] ) ) {

			foreach ( $data[0]['content']['images'] as $v ) {
				/*print_r('<pre>');
				print_r( $v );
				print_r('</pre>');*/

				if ( true === $image_set || 0 === $counter ) {
					$counter++;
					continue;
				}

				if ( ! $this->check_if_image_is_used( $v ) ) {
					$temp_banner = $this->attach_image( $v, $id, array(
						'width' => '1920',
						'height' => '600',
						'cropping' => 'c',
					) );

					if ( false !== $temp_banner ) {
						$this->banner_image = $temp_banner;

						delete_post_meta( $id,'image_group' );

						$new_banner = array(
							'banner_image' => array(
								'cmb-field-0' => $this->banner_image,
							),
						);
						add_post_meta( $id,'image_group',$new_banner,true );
						$image_set = true;
					}
				}
				$counter++;
			}
		}

		return $image_set;
	}

	/**
	 * Grabs all of the current used featured images on the site.
	 */
	public function check_if_image_is_used( $v ) {
		global $wpdb;

		$temp_fragment = explode( '/',$v['url_fragment'] );
		$url_filename = $temp_fragment[ count( $temp_fragment ) -1 ];
		$url_filename = str_replace( array( '.jpg', '.png', '.jpeg' ),'',$url_filename );
		$url_filename = trim( $url_filename );
		$url_filename = str_replace( ' ','_',$url_filename );

		if ( in_array( $url_filename,$this->found_attachments ) ) {
			//check to see if there is a featured image set with this ID.
			$found_id = array_search( $url_filename,$this->found_attachments );

			$querystring = "
				SELECT      post_id
				FROM        {$wpdb->postmeta}
				WHERE       meta_value = '{$found_id}'
				AND 		meta_key = '_thumbnail_id'
			";
			// @codingStandardsIgnoreLine
			$results = $wpdb->get_results( $querystring );

			if ( ! empty( $results ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			$querystring = "
				SELECT      ID
				FROM        {$wpdb->posts}
				WHERE       post_name = '{$url_filename}'
			";
			// @codingStandardsIgnoreLine
			$results = $wpdb->get_results( $querystring );
			if ( ! empty( $results ) ) {
				$querystring = "
					SELECT      post_id
					FROM        {$wpdb->postmeta}
					WHERE       meta_value = '{$results[0]->ID}'
					AND 		meta_key = '_thumbnail_id'
				";
				// @codingStandardsIgnoreLine
				$results = $wpdb->get_results( $querystring );
				if ( ! empty( $results ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Que an item to be saved.
	 *
	 * @param   $id     int
	 */
	public function queue_item( $id ) {
		if ( is_array( $this->import_queue ) && ! in_array( $id,$this->import_queue ) ) {
			$this->import_queue[] = $id;
		} else {
			$this->import_queue[] = $id;
		}
	}

	/**
	 * Saves the queue to the option.
	 */
	public function save_queue() {
		if ( ! empty( $this->import_queue ) ) {
			if ( ! empty( $this->queued_imports ) ) {
				$saved_imports = array_merge( $this->queued_imports,$this->import_queue );
			} else {
				$saved_imports = $this->import_queue;
			}

			delete_option( 'wetu_importer_que' );

			if ( ! empty( $saved_imports ) ) {
				$saved_imports = array_unique( $saved_imports );
				update_option( 'wetu_importer_que',$saved_imports );
			}
		}
	}

	/**
	 * The header of the item list
	 */
	public function table_header() {
		?>
		<thead>
		<tr>
			<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th style="" class="manage-column column-title " id="title" style="width:50%;" scope="col"><?php esc_attr_e( 'Title', 'wetu-importer' ); ?></th>
			<th style="" class="manage-column column-date" id="ref" style="width:10%;" scope="col"><?php esc_attr_e( 'Ref', 'wetu-importer' ); ?></th>
			<th style="" class="manage-column column-date" id="date" scope="col"><?php esc_attr_e( 'Date', 'wetu-importer' ); ?></th>
			<th style="" class="manage-column column-ssid" id="ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'wetu-importer' ); ?></th>
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
				<label for="cb-select-all-1" class="screen-reader-text"><?php esc_attr_e( 'Select All', 'wetu-importer' ); ?></label>
				<input type="checkbox" id="cb-select-all-1">
			</th>
			<th style="" class="manage-column column-title" scope="col"><?php esc_attr_e( 'Title', 'wetu-importer' ); ?></th>
			<th style="" class="manage-column column-date" id="ref" style="width:10%;" scope="col"><?php esc_attr_e( 'Ref', 'wetu-importer' ); ?></th>			
			<th style="" class="manage-column column-date" scope="col"><?php esc_attr_e( 'Date', 'wetu-importer' ); ?></th>
			<th style="" class="manage-column column-ssid" scope="col"><?php esc_attr_e( 'WETU ID', 'wetu-importer' ); ?></th>
		</tr>
		</tfoot>
		<?php
	}	

}