<?php
/**
 * The Settings Screen for the Importer Plugin
 *
 * @package   lsx_wetu_importer
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link
 * @copyright 2019 LightSpeed
 **/

namespace lsx_wetu_importer\classes;

/**
 * The Welcome Screen for the Importer Plugin
 */
class Settings {

	/**
	 * Holds instance of the class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds the default settings.
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * Holds the settings fields available.
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->defaults = array(
			'api_key'                            => '',
			'disable_tour_descriptions'          => '',
			'disable_accommodation_descriptions' => '',
			'disable_accommodation_excerpts'     => '',
			'disable_destination_descriptions'   => '',
			'image_replacing'                    => 'on',
			'image_limit'                        => '15',
			'image_scaling'                      => 'on',
			'width'                              => '800',
			'height'                             => '600',
			'scaling'                            => 'h',
		);
		$this->fields   = array_keys( $this->defaults );
		add_action( 'admin_init', array( $this, 'save_options' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return  object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Display the importer welcome screen
	 */
	public function display_page() {
		$options = \lsx_wetu_importer\includes\helpers\get_options();
		foreach ( $options as $key => $value ) {
			$value = trim( $value );
		}
		$options = wp_parse_args( $options, $this->defaults );
		?>
		<div class="wrap">
			<form method="post" class="">
				<?php wp_nonce_field( 'lsx_wetu_importer_save', 'lsx_wetu_importer_save_options' ); ?>
				<h1><?php esc_html_e( 'General', 'lsx-wetu-importer' ); ?></h1>
				<table class="form-table">
					<tbody>
						<tr class="form-field">
							<th scope="row">
								<label for="wetu_api_key"> <?php esc_html_e( 'API Key', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="text" value="
								<?php
								if ( isset( $options['api_key'] ) ) {
									echo esc_attr( $options['api_key'] );
								}
								?>
								" name="api_key" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_tour_descriptions"><?php esc_html_e( 'Disable Tour Descriptions', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['disable_tour_descriptions'] ) && '' !== $options['disable_tour_descriptions'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="disable_tour_descriptions" />

								<small><?php esc_html_e( 'If you are going to manage your tour descriptions on this site and not on WETU then enable this setting.', 'lsx-wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_accommodation_descriptions"><?php esc_html_e( 'Disable Accommodation Descriptions', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['disable_accommodation_descriptions'] ) && '' !== $options['disable_accommodation_descriptions'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="disable_accommodation_descriptions" />
								<small><?php esc_html_e( 'If you are going to edit the accommodation descriptions imported then enable this setting.', 'lsx-wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_accommodation_excerpts"><?php esc_html_e( 'Disable Accommodation Excerpts', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['disable_accommodation_excerpts'] ) && '' !== $options['disable_accommodation_excerpts'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="disable_accommodation_excerpts" />
								<small><?php esc_html_e( 'If you are going to edit the accommodation excerpts then enable this setting.', 'lsx-wetu-importer' ); ?></small>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="disable_destination_descriptions"><?php esc_html_e( 'Disable Destinations Descriptions', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['disable_destination_descriptions'] ) && '' !== $options['disable_destination_descriptions'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="disable_destination_descriptions" />
								<small><?php esc_html_e( 'If you are going to edit the destination descriptions on this site then enable this setting.', 'lsx-wetu-importer' ); ?></small>
							</td>
						</tr>					
					</tbody>
				</table>

				<h1><?php esc_html_e( 'Images', 'lsx-wetu-importer' ); ?></h1>

				<table class="form-table">
					<tbody>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_replacing"><?php esc_html_e( 'Replace Images', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['image_replacing'] ) && '' !== $options['image_replacing'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="image_replacing" />
								<p><?php esc_html_e( 'Do you want your images to be replaced on each import.', 'lsx-wetu-importer' ); ?></p>
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_limit"> <?php esc_html_e( 'Limit the amount of images imported to the gallery', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="" type="text" value="
								<?php
								if ( isset( $options['image_limit'] ) && '' !== $options['image_limit'] ) {
									echo esc_attr( $options['image_limit'] );
								}
								?>
								"
								name="image_limit" />
							</td>
						</tr>

						<tr class="form-field -wrap">
							<th scope="row">
								<label for="image_scaling"><?php esc_html_e( 'Enable Image Scaling', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="checkbox"
								<?php
								if ( isset( $options['image_scaling'] ) && '' !== $options['image_scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="image_scaling" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="width"> <?php esc_html_e( 'Width (px)', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="800" type="text" value="
								<?php
								if ( isset( $options['width'] ) && '' !== $options['width'] ) {
									echo esc_attr( $options['width'] );
								}
								?>
								"
								name="width" />
							</td>
						</tr>
						<tr class="form-field -wrap">
							<th scope="row">
								<label for="height"> <?php esc_html_e( 'Height (px)', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input placeholder="600" type="text" value="
								<?php
								if ( isset( $options['height'] ) && '' !== $options['height'] ) {
									echo esc_attr( $options['height'] );
								}
								?>
								"
								name="height" />
							</td>
						</tr>

						<tr class="form-field -wrap">
							<th scope="row">
								<label for="scaling"> <?php esc_html_e( 'Scaling', 'lsx-wetu-importer' ); ?></label>
							</th>
							<td>
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'raw' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling" value="raw" /> <?php esc_html_e( 'Get the Full size image, no cropping takes place.', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'c' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="c" /> <?php esc_html_e( 'Crop image to fit fully into the frame, Crop is taken from middle, preserving as much of the image as possible.', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'h' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="h" /> <?php esc_html_e( 'Crop image to fit fully into the frame, but resize to height first, then crop on width if needed', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'w' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="w" /> <?php esc_html_e( 'Crop image to fit fully into the frame, but resize to width first, then crop on height if needed', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'nf' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="nf" /> <?php esc_html_e( 'Resize the image to fit within the frame. but pad the image with white to ensure the resolution matches the frame', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'n' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="n" /> <?php esc_html_e( 'Resize the image to fit within the frame. but do not upscale the image.', 'lsx-wetu-importer' ); ?><br />
								<input type="radio"
								<?php
								if ( isset( $options['scaling'] ) && '' !== $options['scaling'] && 'W' === $options['scaling'] ) {
									echo esc_attr( 'checked="checked"' );
								}
								?>
								name="scaling"  value="W" /> <?php esc_html_e( 'Resize the image to fit within the frame. Image will not exceed specified dimensions', 'lsx-wetu-importer' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'lsx-wetu-importer' ); ?>"></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Save the options fields
	 *
	 * @return void
	 */
	public function save_options() {
		if ( ! isset( $_POST['lsx_wetu_importer_save_options'] ) || ! wp_verify_nonce( $_POST['lsx_wetu_importer_save_options'], 'lsx_wetu_importer_save' ) ) {
			return;
		}
		$data_to_save = array();
		foreach ( $this->defaults as $key => $field ) {
			if ( isset( $_POST[ $key ] ) ) {
				$data_to_save[ $key ] = $_POST[ $key ];
			} else {
				$data_to_save[ $key ] = '';
			}
		}
		update_option( 'lsx_wetu_importer_settings', $data_to_save );
	}
}
