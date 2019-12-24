<?php

/**
 * -----------------------------------------------------------------------------
 * Plugin Name: PHP Error Log Viewer
 * Description: Creates a browser-viewable display of the PHP error log. Error messages are styled and filterable to facilitate quick skimming.
 * Version: 1.2.0
 * Author: Code Potent
 * Author URI: https://codepotent.com
 * Plugin URI: https://codepotent.com/classicpress/plugins/
 * Text Domain: codepotent-php-error-log-viewer
 * Domain Path: /languages
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright © 2019 - CodePotent
 * -----------------------------------------------------------------------------
 *           ____          _      ____       _             _
 *          / ___|___   __| | ___|  _ \ ___ | |_ ___ _ __ | |_
 *         | |   / _ \ / _` |/ _ \ |_) / _ \| __/ _ \ '_ \| __|
 *         | |__| (_) | (_| |  __/  __/ (_) | ||  __/ | | | |_
 *          \____\___/ \__,_|\___|_|   \___/ \__\___|_| |_|\__|.com
 *
 * -----------------------------------------------------------------------------
 */

// Declare the namespace.
namespace CodePotent\PhpErrorLogViewer;

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

class PhpErrorLogViewer {

	/**
	 * Constructor.
	 *
	 * No properties to set; move straight to initialization.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Setup all the things.
		$this->init();

	}

	/**
	 * Plugin initialization.
	 *
	 * Register actions and filters to hook the plugin into the system.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Load constants.
		require_once plugin_dir_path(__FILE__).'includes/constants.php';

		// Load plugin update class.
		require_once(PATH_CLASSES.'/UpdateClient.class.php');

		// Run the update client.
		UpdateClient::get_instance();
		
		// Add a quick link to the error log.
		add_action('wp_before_admin_bar_render', [$this, 'adminbar_quicklink']);

		// Register admin page and menu item.
		add_action('admin_menu', [$this, 'register_admin_menu']);

		// Add a "Settings" link to core's plugin admin row.
		add_filter('plugin_action_links_'.PLUGIN_SLUG.'/'.PLUGIN_FILE, [$this, 'register_action_links']);

		// Enqueue backend scripts.
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		// Enqueue backend styles.
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

		// Execute purge requests; if no purge requested, nothing happens.
		add_action('init', [$this, 'process_purge_requests']);

 		// Replace footer text with plugin name and version info.
 		add_filter('admin_footer_text', [$this, 'filter_footer_text'], 10000);

 		// Register hooks for plugin activation and deactivation; use $this.
 		register_activation_hook(__FILE__, [$this, 'activate_plugin']);
 		register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

 		// Register hook for plugin deletion; use __CLASS__.
 		register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall_plugin']);

	}

	/**
	 * Admin bar link.
	 *
	 * Add a link to the admin bar that leads to the PHP error log; just a minor
	 * convenience.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.1.0
	 *
	 */
	public function adminbar_quicklink() {

		global $wp_admin_bar;

		$wp_admin_bar->add_menu([
			'parent' => false,
			'id'     => PLUGIN_PREFIX.'_adminbar_quicklink',
			'title'  => esc_html__('PHP Errors', 'codepotent-php-error-log-viewer'),
			'href'   => admin_url('tools.php?page='.PLUGIN_SLUG),
			'meta'   => false,
		]);

	}

	/**
	 * Register admin view.
	 *
	 * Place a "PHP Error Log" submenu item under the core Tools menu. This also
	 * registers the admin page for same.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {

		add_submenu_page(
			'tools.php',
			PLUGIN_NAME,
			PLUGIN_MENU_TEXT,
			'manage_options',
			PLUGIN_SLUG,
			[$this, 'render_php_error_log']
			);

	}

	/**
	 * Add a direct link to the PHP Error Log in the plugin admin display.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Administration links for the plugin.
	 *
	 * @return array $links Updated administration links.
	 */
	public function register_action_links($links) {

		// A link to view the PHP error log.
		$error_log_link = '<a href="'.admin_url('tools.php?page='.PLUGIN_SLUG).'">'.esc_html__('View Error Log', 'codepotent-php-error-log-viewer').'</a>';

		// Prepend the above link to the action links.
		array_unshift($links, $error_log_link);

		// Return the updated $links array.
		return $links;

	}

	/**
	 * Enqueue JavaScript.
	 *
	 * JavaScript is used to allow the user to confirm the decision to purge the
	 * PHP error log; this prevents accidental deletion. Even though it's only a
	 * few lines of "vanilla" JavaScript, it should stil be enqueued in the same
	 * way as any other script.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {

		// Not in a view related to this plugin? Bail.
		$screen = get_current_screen();
		if ($screen->base !== 'tools_page_'.PLUGIN_SLUG) {
			return;
		}

		// Enqueue the script.
		wp_enqueue_script(PLUGIN_SLUG.'-admin', URL_SCRIPTS.'/admin-global.js');

		// Create an array of data to make available in the JavaScript.
		$js_vars = [

			// Allows for prefixing in the JS.
			'plugin_slug' => PLUGIN_SLUG,

			// Translate the question to be asked in the popup dialog.
			'question' => esc_html__('Remove all entries from the PHP error log?', 'codepotent-php-error-log-viewer'),

			// Create a nonce-fortified URL to purge the error log.
			'redirect' => esc_url(
				wp_nonce_url(
					admin_url('tools.php?page='.PLUGIN_SLUG.'&purge_errors=1'),
					PLUGIN_PREFIX.'_purge_error_log'
				)
			),

		];

		// Scope the above PHP array out to the JS file.
		wp_localize_script(PLUGIN_SLUG.'-admin', 'confirmation', $js_vars);

	}

	/**
	 * Enqueue CSS.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {

		// Not in a view related to this plugin? Bail.
		$screen = get_current_screen();
		if ($screen->base !== 'tools_page_'.PLUGIN_SLUG) {
			return;
		}

		// Enqueue the styles.
		wp_enqueue_style(PLUGIN_SLUG.'-admin', URL_STYLES.'/admin-global.css');

	}

	/**
	 * Get error types.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return array Error type texts keyed accordingly.
	 */
	public function get_error_types() {

		// Array of error type texts keyed by type.
		$error_types = [
			'deprecated'         => esc_html__('Deprecated', 'codepotent-php-error-log-viewer'),
			'notice'             => esc_html__('Notice', 'codepotent-php-error-log-viewer'),
			'warning'            => esc_html__('Warning', 'codepotent-php-error-log-viewer'),
			'error'              => esc_html__('Error', 'codepotent-php-error-log-viewer'),
			'stack_trace_title'  => esc_html__('Stack Trace', 'codepotent-php-error-log-viewer'),
			'stack_trace_step'   => '',
			'stack_trace_origin' => '',
			'other'              => esc_html__('Other', 'codepotent-php-error-log-viewer'),
		];

		// Return the error types.
		return $error_types;

	}

	/**
	 * Get error defaults.
	 *
	 * This method is used to ensure all expected elements are initialized.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	public function get_error_defaults() {

		// Get error types.
		$error_types = $this->get_error_types();

		// Empty arrays as defaults.
		$error_defaults = [];
		foreach ($error_types as $type=>$text) {
			$error_defaults[$type] = [];
		}

		// Return the array of empty arrays.
		return $error_defaults;

	}

	/**
	 * Get errors from log.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param string $error_log Path to the PHP error log.
	 *
	 * @return array Array of errors from the log.
	 */
	private function get_errors_raw($error_log) {

		// Initialize the return variable.
		$raw_errors = [];

		// Error log doesn't exist? Bail.
		if (!file_exists($error_log)) {
			return $raw_errors;
		}

		// Get the filesize.
		$raw_errors['size'] = filesize($error_log);

		// Get the error log's entries as an array.
		$raw_errors['lines'] = file($error_log);

		// Retrn the size/lines array.
		return $raw_errors;

	}

	/**
	 * Process errors into expected arrays.
	 *
	 * This method processes the raw errors array into various other arrays that
	 * are used to determine the error type of each entry.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param string $error_log Path to PHP error log.
	 * @param array $options Array of display options.
	 *
	 * @return array[]
	 */
	private function get_errors_processed($error_log, $options) {

		// Initialize defaults.
		$processed_errors = [];

		// Get errors.
		$raw_errors = $this->get_errors_raw($error_log);
		if (!isset($raw_errors['size']) || !isset($raw_errors['lines'])) {
			return $processed_errors;
		}

		// Grab the log size.
		$processed_errors['size'] = $raw_errors['size'];

		// Grab the raw unordered lines.
		$processed_errors['lines'] = $raw_errors['lines'];

		// Ensure defaults are set to prevent PHP warnings.
		$processed_errors['errors'] = $this->get_error_defaults();

		// Iterate over errors.
		foreach ($raw_errors['lines'] as $n=>$error) {

			$error = trim($error);
			// Set a key depending on the type of error.
			if (strpos($error, 'PHP Deprecated')) {
				$error_key = 'deprecated';
			} else if (strpos($error, 'PHP Notice')) {
				$error_key = 'notice';
			} else if (substr($error, 0, 11) === 'Stack trace' || strpos($error, 'Stack trace')) {
				$error_key = 'stack_trace_title';
			} else if (substr($error, 0, 1) === '#' || strpos($error, 'stderr: #')) {
				$error_key = 'stack_trace_step';
			} else if (substr($error, 0, 9) === 'thrown in' || strpos($error, 'thrown in')) {
				$error_key = 'stack_trace_origin';
			} else if (strpos($error, 'error:') || strpos($error, 'stderr:') || strpos($error, '[error]')) {
				$error_key = 'error';
			} else if (strpos($error, 'PHP Warning') || strpos($error, '[warn]')) {
				$error_key = 'warning';
			} else {
				$error_key = 'other';
			}

			// Map the URL to a type in all cases.
			$processed_errors['mapped'][$n] = $error_key;

			// Capture only those errors that will be displayed.
			if ($options[$error_key]) {
				$processed_errors['errors'][$error_key][$n] = $error;
			}

			// Bold (most) error titles.
			$processed_errors['errors'][$error_key][$n] =
				preg_replace(
					'|(PHP )([A-Za-z]){1,} *([A-Za-z ]){1,}|',
					'<strong>${0}</strong>',
					$error
				);

			// Strip: mod_fcgid: stderr:
			$processed_errors['errors'][$error_key][$n] = str_replace('mod_fcgid: stderr: ', '', $processed_errors['errors'][$error_key][$n]);

			// Strip datetime, else wrap it for styling purposes.
			if (empty($options['datetime'])) {
				$processed_errors['errors'][$error_key][$n] = preg_replace('|([){1}([A-Za-z0-9_ -:\/]){1,}(]){1}|', '', $processed_errors['errors'][$error_key][$n]);
			} else {
				$processed_errors['errors'][$error_key][$n] = preg_replace('|([){1}([A-Za-z0-9_ -:\/]){1,}(]){1}|', '<span class="'.PLUGIN_SLUG.'-datetime">${0}</span>', $processed_errors['errors'][$error_key][$n]);
			}

		}

		// Return the processed errors.
		return $processed_errors;

	}

	/**
	 * Render PHP errors.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 */
	public function render_php_error_log() {

		// Get path to error log.
		$error_log = ini_get('error_log');

		// Error log not found? Describe and return.
		if (!file_exists($error_log)) {
			echo $this->markup_error_log_404();
			return;
		}

		// Update checkbox options, if requested.
		if (isset($_POST[PLUGIN_PREFIX.'_nonce'])) {
			$this->update_display_options();
		}

		// Get current display options.
		$options = get_option(PLUGIN_PREFIX, []);

		// Get processed error log.
		$errors = $this->get_errors_processed($error_log, $options);

		// Open container.
		echo '<div class="wrap" id="'.PLUGIN_SLUG.'">';

		// Display success message if error log was just purged.
		if (get_transient(PLUGIN_PREFIX.'_purged')) {
			echo $this->markup_success_message();
		}

		// Print title.
		echo '<h1 id="'.PLUGIN_SLUG.'-nav-jump-top">'.PLUGIN_NAME.'</h1>';

		// Print filter checkboxes.
		echo $this->markup_display_inputs($errors, $options);

		// Print a jump-link if entries start scrolling the page.
		echo $this->markup_jump_link($errors, $options, 'header');

		// Print buttons for refresh and purge actions.
		echo $this->markup_action_buttons($error_log);

		// Print the filesize note.
		echo $this->markup_filesize_indicator($errors['size']);

		// Print the legend.
		echo $this->markup_legend();

		// If error log is empty, go no further. Wrap it up and return here.
		if (empty($errors)) {
			echo '</div><!-- .wrap -->';
			return;
		}

		// Print the error rows.
		echo $this->markup_error_rows($errors, $options);

		// Another jump-link, if the display grows long.
		echo $this->markup_jump_link($errors, $options, 'footer');

		// Container.
		echo '</div>';

	}

	/**
	 * Render success message.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function markup_success_message() {

		// Assemble the message.
		$markup = '<div class="notice notice-success is-dismissible">';
		$markup .= '<p>'.esc_html__('Error log has been emptied.', 'codepotent-php-error-log-viewer').'</p>';
		$markup .= '</div>'."\n";

		// Delete the transient that triggered this message.
		delete_transient(PLUGIN_PREFIX.'_purged');

		// Return the markup string.
		return $markup;

	}

	/**
	 * Markup filter inputs.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array[][] $errors Multidimensional array of errors.
	 * @param array $options Array of display options.
	 *
	 * @return string HTML form for filtering the errors displayed.
	 */
	public function markup_display_inputs($errors, $options) {

		// Form open.
		$markup = '<form id="'.PLUGIN_SLUG.'-filter" method="post" action="'.admin_url('/tools.php?page='.PLUGIN_SLUG).'">';

		// Markup the nonce field.
		$markup .= wp_nonce_field(PLUGIN_PREFIX.'_nonce', PLUGIN_PREFIX.'_nonce', true, false);

		// Date/time input.
		$markup .= '<label>'.esc_html__('Date/Time', 'codepotent-php-error-log-viewer').' <input type="checkbox" name="'.PLUGIN_PREFIX.'[datetime]" value="1" '.checked($options['datetime'], 1, false).'></label>';

		// Get error types. Note: texts are escaped here, not the foreach below.
		$error_types = $this->get_error_types();

		// Print the labels and inputs.
		foreach ($error_types as $type=>$text) {
			if ($type !== 'stack_trace_step' && $type !== 'stack_trace_origin') {
				$markup .= '<label>'.$text.' ('.number_format(count($errors['errors'][$type])).') <input type="checkbox" name="'.PLUGIN_PREFIX.'['.$type.']" value="1" '.checked($options[$type], 1, false).'></label>';
			}
		}

		// Markup the submit button.
		$markup .= '<input type="submit" class="button button-primary" name="submit" value="'.esc_html__('Apply Filters', 'codepotent-php-error-log-viewer').'">';

		// Close the form.
		$markup .= '</form>';

		// Return markup string.
		return $markup;

	}

	/**
	 * Markup jump links.
	 *
	 * Because new errors always appear at the bottom, if the error log has many
	 * entries, the user would have to scroll each time the page was loaded. The
	 * jump-links allow users to easily jump from the top to the bottom and back
	 * again without the need for endless scrolling. These links only display if
	 * there are enough entries showing onscreen.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array[][] $errors Multidimensional array of errors.
	 * @param array $options Array of display options.
	 * @param string $where Set to "header" if not "footer".
	 *
	 * @return string Any generated markup.
	 */
	public function markup_jump_link($errors, $options, $where='header') {

		// Initialization.
		$markup = '';

		// Not many errors currently displaying? Bail.
		if ($this->count_displayed_items($errors, $options) < 10) {
			return $markup;
		}

		// Container.
		$markup .= '<div class="alignleft">';

		// Jump-link.
		if ($where === 'header') {
			$markup .= '<a href="#'.PLUGIN_SLUG.'-nav-jump-bottom">Skip to bottom</a>';
		} else {
			$markup .= '<a id="'.PLUGIN_SLUG.'-nav-jump-bottom" href="#'.PLUGIN_SLUG.'-nav-jump-top">Back to top</a>';
		}

		// Container.
		$markup .= '</div>';

		// Return markup string.
		return $markup;

	}

	/**
	 * Markup action buttons.
	 *
	 * Generates markup for the buttons used to refresh and purge the error log.
	 * This is always used at the top of the display. If there are enough errors
	 * that the page begins to scroll, the buttons will also be placed below the
	 * list to convenience.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param string $error_log Path to error log file.
	 *
	 * @return string HTML markup for refresh and purge buttons.
	 */
	public function markup_action_buttons($error_log) {

		// Open containers.
		$markup = '<div class="'.PLUGIN_SLUG.'-buttons">';
		$markup .= '	<span class="alignright">';

		// Refresh button.
		$markup .= '	<a href="'.admin_url('tools.php?page='.PLUGIN_SLUG).'" class="button button-secondary">'.esc_html__('Refresh Error Log', 'codepotent-php-error-log-viewer').'</a>';

		// Purge button; if log is writeable.
		if (is_writable($error_log)) {
			$markup .= '	<a href="#" id="'.PLUGIN_SLUG.'-confirm-purge" class="button button-secondary">'.esc_html__('Purge Error Log', 'codepotent-php-error-log-viewer').'</a>';
		}

		// Close containers.
		$markup .= '	</span>';
		$markup .= '</div><!-- .{PLUGIN_SLUG}_buttons -->';

		// Return the string.
		return $markup;

	}

	/**
	 * Markup error log size.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param int $log_size Size of the error log, in bytes.
	 * @return string Text representation, ie, 123 byes, 10.3 Kb, 1.2 Mb
	 */
	public function markup_filesize_indicator($log_size) {

		// Cast the log size.
		settype($log_size, 'int');

		// Setup default display text.
		$display_text = sprintf(
				esc_html__('%d bytes', 'codepotent-php-error-log-viewer'),
				$log_size
				);

		// Is error log greater than 1Mb? Change the text to suit.
		if ($log_size > 1000000) {
			$display_text = sprintf(
					esc_html__('%d Mb', 'codepotent-php-error-log-viewer'),
					round($log_size/1000000, 1)
					);
		}
		// Is error log greater than 1Kb? Change the text to suit.
		else if ($log_size > 1000) {
			$display_text = sprintf(
					esc_html__('%d Kb', 'codepotent-php-error-log-viewer'),
					round($log_size/1000, 1)
					);
		}

		// Markup filesize container and note.
		$markup = '<div class="'.PLUGIN_SLUG.'-filesize">';
		$markup .= '<strong>'.esc_html__('Log Size', 'codepotent-php-error-log-viewer').'</strong>: '.$display_text;
		$markup .= '</div>';

		// Return the string.
		return $markup;

	}

	/**
	 * Markup information legend.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return string Markup for the legend.
	 */
	public function markup_legend() {

		// Initialization.
		$markup = '';

		// Error types.
		$types = $this->get_error_types();

		// Open container.
		$markup .= '<div class="'.PLUGIN_SLUG.'-legend">';

		// Title.
		$markup .= '<h3 class="'.PLUGIN_SLUG.'-legend-title">'.esc_html__('Legend', 'codepotent-php-error-log-viewer').'</h3>';

		// Markup each legend item.
		foreach ($types as $type=>$text) {
			if ($type !== 'stack_trace_step' && $type !== 'stack_trace_origin') {
				$markup .= '<div class="'.PLUGIN_SLUG.'-legend-box item-php-'.str_replace('_', '-', $type).'">'.$text.'</div>';
			}
		}

		// Close container.
		$markup .= '</div> <!-- .'.PLUGIN_SLUG.'-legend -->';

		// Return the markup.
		return $markup;

	}

	/**
	 * Markup error rows.
	 *
	 * This method handles markup generation for the error entries.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array $raw_errors All errors as read from the log file.
	 * @param array $typed_errors[type][line] Array of line numbers keyed by error type.
	 * @param array $options Which error types to display.
	 *
	 * @return string|mixed
	 */
	public function markup_error_rows($error_data, $options) {

		// Initialize the markup string.
		$markup = '';

		// Iterate over raw_errors array.
		foreach ($error_data['lines'] as $n=>$error) {

			// Get error type.
			$type = $error_data['mapped'][$n];

			// Don't display this type of error? Next.
			if (!$options[$type]) {
				continue;
			}

			/**
			 * Stack trace titles are padded to make sure they "touch" the error
			 * that produced them. If stack traces are displayed and errors have
			 * been supressed from display, this block ensures that the rows are
			 * separated appropriately.
			 */
			$style = '';
			if ($type === 'stack_trace_title' && !$options['error']) {
				$style = ' style="margin-top:13px;"';
			}

			// Mark up the error row.
			$markup .= '<p class="error-log-row php-'.str_replace('_', '-', $type).'"'.$style.'>'.$error_data['errors'][$type][$n].'</p>';

		}

		// Return the string.
		return $markup;

	}

	/**
	 * Provide notice and possible solutions if error log not found.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function markup_error_log_404() {

		// Core container.
		$markup = '<div class="wrap" id="'.PLUGIN_SLUG.'">';

		// Plugin container.
		$markup .= '<div id="'.PLUGIN_SLUG.'-not-found">';

		// Title.
		$markup .= '<h1>'.esc_html__('PHP Error Log Not Found', 'codepotent-php-error-log-viewer').'</h1>';

		// Description of issue.
		$markup .= '	<p>'.esc_html__('The PHP error log could not be detected. Following are several possible solutions to resolve this issue. After implementing one or the other solution, come back and refresh this page.', 'codepotent-php-error-log-viewer').'</p>';
		$markup .= '	<hr>';

		// Solution one.
		$markup .= '	<h3>'.esc_html__('Solution 1: Specify error log via PHP', 'codepotent-php-error-log-viewer').'</h3>';
		$markup .= '	<p>';
		$markup .= sprintf(
				esc_html__('Add the following line to your %1$swp-config.php%2$s file on a new line just after the opening %1$s&lt;?php%2$s tag. Be sure to change the path so it points to your PHP error log.', 'codepotent-php-error-log-viewer'),
				'<code>',
				'</code>'
				);
		$markup .= '	</p>';
		$markup .= '	<p><textarea class="solution-one">ini_set(\'error_log\', \'/path/to/php/error/log/file\');</textarea></p>';
		$markup .= '	<hr>';

		// Solution two.
		$markup .= '	<h3>'.esc_html__('Solution 2: Specify error log via .htaccess', 'codepotent-php-error-log-viewer').'</h3>';
		$markup .= '	<p>';
		$markup .= sprintf(
				esc_html__('Add the following two lines at the top of your %1$s.htaccess%2$s file. If your file already has one or both of these lines, you can just edit those lines instead of adding these. Be sure to change the path so it points to your PHP error log.', 'codepotent-php-error-log-viewer'),
				'<code>',
				'</code>'
				);
		$markup .= '	</p>';
		$markup .= '	<p><textarea class="solution-two">php_flag log_errors On'."\n".'php_value error_log /path/to/php/error/log/file</textarea></p>';
		$markup .= '	<hr>';

		// Solution three.
		$markup .= '	<h3>'.esc_html__('Solution 3: Specify error settings via ClassicPress constants', 'codepotent-php-error-log-viewer').'</h3>';
		$markup .= sprintf(
				esc_html__('In your %1$swp-config.php%2$s file, find the line that reads %1$sdefine(\'WP_DEBUG\', false);%2$s. Replace that line with the following three lines. Be sure to change the path so it points to your PHP error log.', 'codepotent-php-error-log-viewer'),
				'<code>',
				'</code>'
				);
		$markup .= '	</p>';
		$markup .= '	<p><textarea class="solution-three">define(\'WP_DEBUG\', true);'."\n".'define(\'WP_DEBUG_DISPLAY\', false);'."\n".'define(\'WP_DEBUG_LOG\', \'/path/to/php/error/log/file\');</textarea></p>';

		// Plugin container.
		$markup .= '</div><!-- #codepotent-php-error-log-not-found -->';

		// Core container.
		$markup .= '</div><!-- .wrap -->';

		// Return the markup string.
		return $markup;

	}

	/**
	 * Count displayed rows.
	 *
	 * This method counts the errors that are currently being displayed. This is
	 * used to show the refresh/purge buttons at the bottom when the display has
	 * quite a few errors showing.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array $errors MulLine numbers keyed by error type.
	 * @param array $options Which error types to display.
	 *
	 * @return int Total number of rows to be displayed.
	 */
	public function count_displayed_items($errors, $options) {

		// Initialization.
		$displayed_errors = 0;

		// No errors mapped? Bail.
		if (empty($errors['mapped'])) {
			return $displayed_errors;
		}

		// Iterate over error type map.
		foreach ($errors['mapped'] as $line_number=>$error_type) {

			// Not displaying this type of error? Don't count it.
			if (!$options[$error_type]) {
				continue;
			}

			// Count errors...
			if ($error_type === 'stack_trace_title') {
				// ...only count title entries for stack traces...
				if (!$options['error']) {
					$displayed_errors++;
				}
			} else if ($error_type === 'stack_trace_step') {
				// ...skip counting stack trace steps...
				continue;
			} else if ($error_type === 'stack_trace_origin') {
				// ...skip counting stack trace origins...
				continue;
			} else {
				// Count the error.
				$displayed_errors++;
			}

		}

		// Total displayed error items.
		return $displayed_errors;

	}

	/**
	 * Update filter options.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function update_display_options() {

		// Define the nonce name.
		$nonce_name = PLUGIN_PREFIX.'_nonce';

		// If nonce is missing, bail.
		if (empty($_POST[$nonce_name])) {
			return false;
		}

		// If nonce is suspect, bail.
		if (!wp_verify_nonce($_POST[$nonce_name], $nonce_name)) {
			return false;
		}

		// Get error types.
		$error_types = $this->get_error_types();

		// Initialization.
		$options = [];

		// Date is a display option, not an error type. Prepend it manually.
		$options['datetime'] = (isset($_POST[PLUGIN_PREFIX]['datetime'])) ? 1 : '';

		// Gather display options; ensure clean values.
		foreach ($error_types as $type=>$text) {
			$options[$type] = (isset($_POST[PLUGIN_PREFIX][$type])) ? 1 : '';
		}

		// More stack trace properties; mirrored from stack trace title setting.
		$options['stack_trace_step'] = (!empty($options['stack_trace_title'])) ? 1 : '';
		$options['stack_trace_origin'] = (!empty($options['stack_trace_title'])) ? 1 : '';

		// Update options.
		update_option(PLUGIN_PREFIX, $options);

	}

	/**
	 * Purge error log.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function process_purge_requests() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}

		// No nonce? Bail.
		if (!isset($_GET['_wpnonce'])) {
			return;
		}

		// Suspicious nonce? Bail.
		if (!wp_verify_nonce($_GET['_wpnonce'], PLUGIN_PREFIX.'_purge_error_log')) {
			return;
		}

		// Not requesting purge? Bail.
		if (!isset($_GET['purge_errors']) || !$_GET['purge_errors']) {
			return;
		}

		// Get path to error log.
		$error_log = ini_get('error_log');

		// Erase log file, if writable; set transient.
		if (is_writable($error_log)) {
			if (file_put_contents($error_log, '') !== false) {
				set_transient(PLUGIN_PREFIX.'_purged', 1, 120);
			}
		}

		// Redirect.
		wp_redirect(admin_url('tools.php?page='.PLUGIN_SLUG));
		exit;

	}

	/**
	 * Filter footer text.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The original footer text.
	 *
	 * @return void|string Branded footer text if in this plugin's admin.
	 */
	public function filter_footer_text($text) {

		// Update footer text if on this plugin's own screen.
		$screen = get_current_screen();
		if ($screen->base === 'tools_page_'.PLUGIN_SLUG) {
			$text = '<span id="footer-thankyou"><a href="'.CODEPOTENT_URL.'/classicpress/plugins/">'.PLUGIN_NAME.'</a> '.PLUGIN_VERSION.' — A <a href="'.CODEPOTENT_URL.'" title="Code Potent"><img src="'.CODEPOTENT_LOGO_SVG_WORDS.'" style="height:1em;vertical-align:middle;"></a> Production</span>';
		}

		// Return the string.
		return $text;
	
	}

	/**
	 * Plugin activation.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function activate_plugin() {

		// No permission to activate plugins? Bail.
		if (!current_user_can('activate_plugins')) {
			return;
		}

		// Initial settings.
		$error_types = $this->get_error_types();

		// Initialize the options array.
		$options = [];

		// Make sure the datetime variable is set.
		$options['datetime'] = 1;

		// Iterate over error types; ensure they, too, are set.
		foreach ($error_types as $type=>$text) {
			$options[$type] = 1;
		}

		// Update with defaults.
		update_option(PLUGIN_PREFIX, $options);

	}

	/**
	 * Plugin deactivation.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function deactivate_plugin() {

		// No permission to activate plugins? None to deactivate either. Bail.
		if (!current_user_can('activate_plugins')) {
			return;
		}

		// Not that there was anything to do here anyway. :)

	}

	/**
	 * Plugin deletion.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public static function uninstall_plugin() {

		// No permission to delete plugins? Bail.
		if (!current_user_can('delete_plugins')) {
			return;
		}

		// Delete options related to the plugin.
		delete_option(PLUGIN_PREFIX);

	}

}

// Make awesome all the errors.
new PhpErrorLogViewer;