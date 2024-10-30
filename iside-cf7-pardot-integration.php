<?php
/**
 * Integration for CF7 Pardot Form Handlers
 *
 * @author    Team iSide
 * @copyright Copyright (C) 2024, iSide BV - dev@iside.be
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Integration for CF7 Pardot Form Handlers
 * Version:     2.0
 * Description: Send ContactForm7 fields to a Pardot form handler after a successful form submission.
 * Author:      Team iSide
 * Author URI:  https://www.iside.be
 * Text Domain: integration-for-cf7-pardot-form-handlers
 * License:     GPL v3
 * Requires at least: 6.0
 * Requires PHP: 7.0
 * Requires Plugins: contact-form-7
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

class Iside_Pardot {

	public $version = '2.0';
	public $plugin_url;

	public function __construct() {
		// Make sure CF7 exists.
		if ( ! class_exists( 'WPCF7' ) ) {
			return;
		}

		$this->plugin_url = plugin_dir_url( __FILE__ );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'wpcf7_editor_panels', array( $this, 'add_panel' ) );
		add_action( 'wpcf7_save_contact_form', array( $this, 'save_contact_form' ), 10, 1 );
		add_filter( 'wpcf7_form_hidden_fields', array( $this, 'hidden_fields' ) );
		add_filter( 'wpcf7_form_tag', array( $this, 'use_raw_values' ), 10, 2 );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'iside-cf7-pardot', $this->plugin_url . 'js/functions.js', array( 'jquery' ), $this->version );
	}

	/**
	 * Add a custom panel.
	 */
	public function add_panel( $panels ) {
		$panels['iside-pardot-panel'] = array(
			'title'    => __( 'Pardot', 'integration-for-cf7-pardot-form-handlers' ),
			'callback' => array( $this, 'cf7_pardot_panel' ),
		);
		return $panels;
	}

	/**
	 * Callback for the custom panel.
	 */
	public function cf7_pardot_panel( $form ) {
		$form_id = isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ? intval( $_GET['post'] ) : false;

		$iside_cf7_options         = get_post_meta( $form_id, 'iside_cf7_pardot_options', true );
		$iside_cf7_options_default = array(
			'formHandlerUrl' => '',
		);

		if ( ! is_array( $iside_cf7_options ) ) {
			$iside_cf7_options = $iside_cf7_options_default;
		}
		$iside_cf7_options = array_merge( $iside_cf7_options_default, $iside_cf7_options );

		$labels = array(
			'formHandlerUrl' => __( 'Pardot form action URL', 'integration-for-cf7-pardot-form-handlers' ),
		);
		?>
		<h3><?php echo esc_html( __( 'Pardot Integration', 'integration-for-cf7-pardot-form-handlers' ) ); ?></h3>
	
		<div id="iside-cf7-pardot-options">
			<?php
			$i = 0;
			foreach ( $iside_cf7_options as $id => $entry ) {
				?>
				<p class="description">
				<label for="iside_cf7_pardot_options[<?php echo esc_attr( $id ); ?>]" class="entry" id="entry-<?php echo intval( $i ); ?>">
					<?php echo ( isset( $labels[ $id ] ) ) ? esc_html( $labels[ $id ] ) : esc_html__( 'Label', 'integration-for-cf7-pardot-form-handlers' ); ?><br />
					<input type="text" name="iside_cf7_pardot_options[<?php echo esc_attr( $id ); ?>]" id="iside_cf7_pardot_options[<?php echo esc_attr( $id ); ?>]" class="textfield large-text" value="<?php echo trim( esc_attr( $entry ) ); ?>">
				</label>
				</p>
				<?php
				++$i;
			}
			?>
		</div>
		<?php
		if ( defined( 'WPCF7_USE_PIPE' ) && WPCF7_USE_PIPE ) {
			$allowed_html = array(
				'p'    => array(),
				'br'   => array(),
				'code' => array(),
				'a'    => array(
					'href'   => true,
					'target' => true,
				),
			);
			?>
			<hr style="margin:20px 0;">
			<p>
				<?php
				printf(
					wp_kses( 'This Pardot implementation supports <a href="%s" target="_blank">pipes</a> for drop-down menus, radio buttons and checkboxes.', 'integration-for-cf7-pardot-form-handlers', $allowed_html ),
					'https://contactform7.com/selectable-recipient-with-pipes/'
				);
				?>
				<br>
				<?php echo wp_kses( "To use the value after the pipe ('|') character for Pardot, add the attribute <code>use-raw-values</code> to the form tag.", 'integration-for-cf7-pardot-form-handlers', $allowed_html ); ?>
				<br>
				<?php echo wp_kses( 'Example: <code>[select my-select <strong>use-raw-values</strong> "CEO|ceo@example.com" "Sales|sales@example.com"]</code>', 'integration-for-cf7-pardot-form-handlers', $allowed_html ); ?>
				<br>
				<?php echo wp_kses( 'Please use with care: this option will reveal the raw values after the pipe character to the public.', 'integration-for-cf7-pardot-form-handlers', $allowed_html ); ?>
			</p>
			<?php
		}
	}

	public function save_contact_form( $contact_form ) {
		if ( ! isset( $_POST ) || empty( $_POST ) || ! isset( $_POST['iside_cf7_pardot_options'] ) || ! is_array( $_POST['iside_cf7_pardot_options'] ) ) {
			return;
		}

		$post_id = $contact_form->id();
		if ( ! $post_id ) {
			return;
		}

		$options = array_map( 'sanitize_text_field', $_POST['iside_cf7_pardot_options'] ?? array() ) ?? array();
		update_post_meta( $post_id, 'iside_cf7_pardot_options', $options );

		return;
	}

	/**
	 * Add PardotFormHandler as a hidden field to the form.
	 */
	public function hidden_fields( $fields = array() ) {
		$contact_form = WPCF7_ContactForm::get_current();

		$iside_cf7_pardot_options = get_post_meta( $contact_form->id(), 'iside_cf7_pardot_options', true );
		if ( is_array( $iside_cf7_pardot_options ) ) {
			foreach ( $iside_cf7_pardot_options as $id => $entry ) {
				if ( $entry && $entry != '' ) {
					$fields[ 'iside_cf7_pardot_' . $id ] = esc_attr( $entry );
				}
			}
		}
		return $fields;
	}

	/**
	 * By default in CF7 <select>s, only whatever is filled in before the | symbol in an option gets outputted on the frontend as both the value attribute of the <option> as wel as its content.
	 * Here we check if a select has the attribute "use-raw-values" set in the back-end, and if so make it so that what's before the | is the content of the <option> and what's behind it is used as the value attribute.
	 */
	public function use_raw_values( $scanned_tag, $replace ) {
		// Only apply when pipes are being used.
		if ( defined( 'WPCF7_USE_PIPE' ) && ! WPCF7_USE_PIPE ) {
			return;
		}
		$raw_values_for = array(
			'select',
			'select*',
			'checkbox',
			'checkbox*',
			'radio',
			'radio*',
		);

		if ( in_array( $scanned_tag['type'], $raw_values_for ) && in_array( 'use-raw-values', $scanned_tag['options'] ) ) {
			if ( array_key_exists( 'pipes', $scanned_tag ) && ! empty( $scanned_tag['pipes'] ) && $scanned_tag['pipes'] instanceof WPCF7_Pipes ) {
				$pipes = $scanned_tag['pipes']->collect_afters();
				if ( ! empty( $pipes ) ) {
					$scanned_tag['values'] = $pipes;
				}
			}
		}

		return $scanned_tag;
	}
}

new Iside_Pardot();