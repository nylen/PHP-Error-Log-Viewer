<?php

/**
 * -----------------------------------------------------------------------------
 * Plugin Name: PHP Error Log Viewer
 * Description: Create a browser-viewable display of the PHP error log. Messages are styled, filterable, and reverse-sortable to facilitate quick skimming.
 * Version: 2.4.0
 * Author: ClassicPress Contributors
 * Author URI: https://www.classicpress.net
 * Plugin URI: https://www.classicpress.net
 * Text Domain: codepotent-php-error-log-viewer
 * Domain Path: /languages
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2021, John Alarcon (Code Potent)
 * -----------------------------------------------------------------------------
 * Adopted by ClassicPress Contributors, 06/01/2021
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
	 * Path to error log file
	 *
	 * @var null
	 */
	public $error_log = null;

	/**
	 * For tallying errors
	 *
	 * @var integer
	 */
	public $error_count = 0;

	/**
	 * For admin bar alert bubble
	 *
	 * @var integer
	 */
	public $errors_displayed = 0;

	/**
	 * For gathering plugin options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Multidimensional array of errors, keyed by type
	 *
	 * @var array[][]
	 */
	public $errors = [];

	/**
	 * Constructor
	 *
	 * No properties to set; move straight to initialization.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if (!function_exists('\wp_get_current_user')) {
			require_once(ABSPATH.'wp-includes/pluggable.php');
		}

		// Setup all the things.
		$this->init();

		// Process the error log into object properties.
		$this->convert_error_log_into_properties();

	}

	/**
	 * Plugin initialization
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
		require_once(PATH_INCLUDES.'/functions.php');

		// Load plugin update class.
		require_once(PATH_CLASSES.'/UpdateClient.class.php');

		// Update options in time to redirect; keeps admin bar alerts current.
		add_action('plugins_loaded', [$this, 'update_display_options']);

		// Execute purge requests; if no purge requested, nothing happens.
		add_action('plugins_loaded', [$this, 'process_purge_requests']);

		// Register admin page and menu item.
		add_action('admin_menu', [$this, 'register_admin_menu']);

		// Admin notices for purge confirmations.
		add_action('admin_notices', [$this, 'render_confirmation_notices']);

		// Enqueue global scripts.
		add_action('admin_enqueue_scripts', [$this, 'enqueue_global_scripts']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_global_scripts']);

		// Enqueue global styles.
		add_action('admin_enqueue_scripts', [$this, 'enqueue_global_styles']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_global_styles']);

		// Handle AJAX requests to purge the error log.
		add_action('wp_ajax_purge_error_log', [$this, 'process_ajax_purge_requests']);

		// Add error log link to admin bar.
		add_action('wp_before_admin_bar_render', [$this, 'register_admin_bar']);

		// Replace footer text with plugin name and version info.
		add_filter('admin_footer_text', [$this, 'filter_footer_text'], 10000);

		// Add a "Settings" link to core's plugin admin row.
		add_filter('plugin_action_links_'.PLUGIN_IDENTIFIER, [$this, 'register_action_links']);

		// Register hooks for activation, deactivation, and uninstallation.
		register_uninstall_hook(__FILE__,    [__CLASS__, 'uninstall_plugin']);
		register_activation_hook(__FILE__,   [$this, 'activate_plugin']);
		register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

	}

	/**
	 * Admin bar link
	 *
	 * Add a link to the admin bar that leads to the PHP error log; just a minor
	 * convenience.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 */
	public function register_admin_bar() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}
		
		// No way to get the link.
		if (!isset($_SERVER['REQUEST_SCHEME']) || !isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['REQUEST_URI'])) {
			return;
		}

		// Primary link text.
		$link_text = esc_html__('PHP Errors', 'codepotent-php-error-log-viewer');

		// Alert bubble for displayed errors.
		$primary_alert = '';
		if ($this->errors_displayed > 0) {
			$primary_alert = '<span class="error-count-bubble">'.number_format($this->errors_displayed).'</span>';
		}

		// Alert bubble for hidden errors.
		$secondary_alert = '';
		if ($this->error_count !== $this->errors_displayed) {
			$secondary_alert = '<span class="error-count-bubble hidden-errors">'.number_format($this->error_count-$this->errors_displayed).'</span>';
		}

		// Filters to remove alert bubbles.
		$primary_alert = apply_filters(PLUGIN_PREFIX.'_primary_alert', $primary_alert);
		$secondary_alert = apply_filters(PLUGIN_PREFIX.'_secondary_alert', $secondary_alert);
		
		// Assemble the return URL.
		$return_url =  esc_url_raw(wp_unslash($_SERVER['REQUEST_SCHEME']).'://'. wp_unslash($_SERVER['HTTP_HOST']). wp_unslash($_SERVER['REQUEST_URI']));

		// Bring the admin bar into scope.
		global $wp_admin_bar;

		// Add the main link.
		$wp_admin_bar->add_menu([
			'parent' => false,
			'id'     => PLUGIN_PREFIX.'_admin_bar',
			'title'  => $link_text.$primary_alert.$secondary_alert,
			'href'   => admin_url('tools.php?page='.PLUGIN_SHORT_SLUG),
			'meta'   => [
				'title' => sprintf(
					esc_html__('PHP %s', 'codepotent-php-error-log-viewer'),
					phpversion()
				)
			]
		]);

		// Add submenu item to purge log via AJAX; only if log is writeable.
		if (is_writable($this->error_log)) {
			$wp_admin_bar->add_menu([
				'parent' => PLUGIN_PREFIX.'_admin_bar',
				'title' => esc_html__('Purge Error Log', 'codepotent-php-error-log-viewer'),
				'id' => PLUGIN_SLUG.'-admin-bar-purge-link',
				'href' => '#',
				'meta' => [
					'data-return-url' => esc_url($return_url)
				]
			]);
		}
	}

	/**
	 * Register admin view
	 *
	 * Place a "PHP Error Log" submenu item under the core Tools menu. This also
	 * registers the admin page for same.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {

		// Add submenu under the Tools menu.
		add_submenu_page(
			'tools.php',
			esc_html__('PHP Error Log', 'codepotent-php-error-log-viewer'),
			PLUGIN_MENU_TEXT,
			'manage_options',
			PLUGIN_SHORT_SLUG,
			[$this, 'render_php_error_log']
			);

	}

	/**
	 * Add a direct link to the PHP Error Log in the plugin admin display
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

		// Prepend error log link in plugin row; for admins only.
		if (current_user_can('manage_options')) {
			$error_log_link = '<a href="'.admin_url('tools.php?page='.PLUGIN_SHORT_SLUG).'">'.esc_html__('PHP Error Log', 'codepotent-php-error-log-viewer').'</a>';
			array_unshift($links, $error_log_link);
		}

		// Return the maybe-updated $links array.
		return $links;

	}

	/**
	 * Enqueue global scripts
	 *
	 * This method enqueues scripts that are used by both admin and user sides.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function enqueue_global_scripts() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}

		// No way to get the link.
		if (!isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['REQUEST_URI'])) {
			return;
		}

		// Applies for all admin views.
		wp_enqueue_script(PLUGIN_SLUG.'-global', URL_SCRIPTS.'/global.js', ['jquery'], false, false);

		// For redirecting back to the current page.
		$redirect_target = esc_url_raw((is_ssl()?'https://':'http://').wp_unslash($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));

		// Setup a deletion-link URL.
		$deletion_link = esc_url(
			wp_nonce_url(
				admin_url('tools.php?page='.PLUGIN_SHORT_SLUG.'&purge_errors=1&redirect_url='.$redirect_target),
				PLUGIN_PREFIX.'_purge_error_log'
				)
			);

		// Data to localize to JavaScript.
		$localized_data = [
			'prefix'            => PLUGIN_SLUG,
			'ajax_url'          => admin_url('admin-ajax.php'),
			'ajax_nonce'        => wp_create_nonce('purge_error_log'),
			'deletion_link'     => $deletion_link,
			'text_confirmation' => esc_html__('Remove all entries from the PHP error log?', 'codepotent-php-error-log-viewer'),
			'text_zero_bytes'   => esc_html__('0 bytes', 'codepotent-php-error-log-viewer'),
			'text_ajax_success' => esc_html__('Error log successfully purged.', 'codepotent-php-error-log-viewer'),
			'text_ajax_failure' => esc_html__('Something went wrong; error log was not purged.', 'codepotent-php-error-log-viewer'),
		];

		// Scope the above PHP array out to the JS file.
		wp_localize_script(PLUGIN_SLUG.'-global', PLUGIN_PREFIX.'_data', $localized_data);

	}

	/**
	 * Enqueue global styles
	 *
	 * As of version 2.0.0, the plugin integrates admin bar alerts. An admin bar
	 * can be present in both (or either of) the front and back ends, so, styles
	 * for the alert bubble must be present on both sides. Still, the styles are
	 * only needed if the admin bar is visible, so, we use that as the check and
	 * squeeze another bit of performance out of the plugin. Note that the style
	 * sheet (at this writing) is only 7k, so, it's not a huge savings; still, a
	 * saved request never hurts.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.2.0
	 */
	public function enqueue_global_styles() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}

		// Used in admin bar alerts; enqueue for all needed pages.
		if (is_admin_bar_showing()) {
			wp_enqueue_style(PLUGIN_SLUG.'-admin', URL_STYLES.'/global.css');
		}

	}

	/**
	 * Get error type.
	 *
	 * This method receives a line from the error log and determines the type of
	 * error it is.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 * @param string $error A line from the error log.
	 *
	 * @return string The type of error it is.
	 */
	public function get_error_type($error) {

		// Run through various acts of string-fu.
		if (strpos($error, 'PHP Deprecated')) {
			$type = 'deprecated';
		} else if (strpos($error, 'PHP Notice')) {
			$type = 'notice';
		} else if (substr($error, 0, 11) === 'Stack trace' || strpos($error, 'Stack trace')) {
			$type = 'stack_trace_title';
		} else if (substr($error, 0, 1) === '#' || strpos($error, 'stderr: #') || preg_match('|( PHP +[0-9]+\. )|', $error)) {
			$type = 'stack_trace_step';
		} else if (substr($error, 0, 9) === 'thrown in' || strpos($error, 'thrown in')) {
			$type = 'stack_trace_origin';
		} else if (strpos($error, 'error:') || strpos($error, 'stderr:') || strpos($error, '[error]')) {
			$type = 'error';
		} else if (strpos($error, 'PHP Warning') || strpos($error, '[warn]')) {
			$type = 'warning';
		} else if (strpos($error, '(') === 0 || strpos($error, ')') === 0 || strpos($error, '[') === 0 || strpos($error, ']') === 0 || strpos($error, 'in ') === 0 || empty($error)) {
			$type = 'stack_trace_step';
		} else {
			$type = 'other';
		}

		// Return the error type.
		return $type;

	}

	/**
	 * Get error types
	 *
	 * This method returns an array of error types contemplated by the plugin.
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
	 * Get error defaults
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

		// Setup an array of empty arrays as defaults.
		$defaults = [];
		foreach (array_keys($this->get_error_types()) as $type) {
			$defaults[$type] = [];
		}

		// Return the defaults array.
		return $defaults;

	}

	/**
	 * Process error log
	 *
	 * This method processes the error log into various object properties.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function convert_error_log_into_properties() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}

		// Initialization.
		$this->errors = $this->raw_errors = [];

		// Error log not found? Bail.
		if (!file_exists($error_log = ini_get('error_log'))) {
			return;
		}

		// Set the error log path.
		$this->error_log = $error_log;

		// Set the filesize; in bytes.
		$this->filesize = filesize($error_log);

		// Set a default errors array.
		$this->errors = $this->get_error_defaults();

		// Set plugin options.
		$this->options = get_option(PLUGIN_PREFIX, []);

		// If no errors found, this is far enough.
		if (empty($this->raw_errors = file($error_log))) {
			return;
		}

		// Reverse sort the array, if requested.
		if (!empty($this->options['reverse_sort']) && $this->options['reverse_sort']) {
			$this->reverse_sort_errors();
		}

		// Iterate over error lines.
		foreach ($this->raw_errors as $n=>$error) {
			// Tidy up the ends.
			$error = trim($error);
			// Determine this error's type.
			$type = $this->get_error_type($error);
			// Map the error to a type in all cases.
			$this->error_map[$n] = $type;
			// Capture only those errors that will be displayed.
			if (!empty($this->options[$type])) {
				$this->errors[$type][$n] = $error;
			}
			// For user-generated errors, remove the rogue line at the bottom.
			if (strstr($error, 'User Generated')) {
				$error = preg_replace('|(<\/pre> in )(.*)|', '</pre>', $error);
			}
			// Bold (most) error titles.
			$this->errors[$type][$n] = preg_replace('|(PHP )([A-Za-z]){1,} *([A-Za-z ]){1,}|', '<strong>${0}</strong>', $error);
			// Strip: "mod_fcgid: stderr:"
			$this->errors[$type][$n] = str_replace('mod_fcgid: stderr: ', '', $this->errors[$type][$n]);
			// Regex to find a datetime string.
			$pattern = '|([){1}([A-Za-z0-9_ -:\/]){1,}(]){1}|';
			// Strip date/time, or wrap it for styling purposes.
			if (empty($this->options['datetime'])) {
				$this->errors[$type][$n] = preg_replace($pattern, '', $this->errors[$type][$n]);
			} else {
				$this->errors[$type][$n] = preg_replace($pattern, '<span class="'.PLUGIN_SLUG.'-datetime">${0}</span>', $this->errors[$type][$n]);
			}
		}

		// With errors all gathered and sorted, count them up.
		foreach ($this->errors as $type=>$error_array) {
			// Stack trace data isn't counted; parent errors are.
			if (strpos($type, 'stack_trace_') !== 0) {
				// Count errors to be displayed.
				if (!empty($this->options[$type])) {
					$this->errors_displayed += count($error_array);
				}
				// Total of all errors.
				$this->error_count += count($error_array);
			}
		}

	}

	/**
	 * Purge error log
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

		// No nonce or action? Bail.
		if (!isset($_GET['_wpnonce']) || !isset($_GET['purge_errors'])) {
			return;
		}

		// Suspicious nonce? Bail.
		if (!wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), PLUGIN_PREFIX.'_purge_error_log')) {
			return;
		}

		// Not requesting purge? Bail.
		if (!(bool)$_GET['purge_errors']) {
			return;
		}

		// Overwrite log file with 0 bytes; set transient.
		if (!empty($this->error_log) && is_writable($this->error_log)) {
			if (file_put_contents($this->error_log, '') !== false) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				set_transient(PLUGIN_PREFIX.'_purged', 1, 120);
			}
		}

		// In case we need a custom redirect.
		$redirect_url = admin_url('tools.php?page='.PLUGIN_SHORT_SLUG);
		if (!empty($_GET['redirect_url'])) {
			$redirect_url = esc_url_raw(wp_unslash($_GET['redirect_url']));
		}

		// Redirect.
		wp_safe_redirect($redirect_url);
		exit;

	}

	/**
	 * Purge error log via AJAX request.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.2.0
	 */
	public function process_ajax_purge_requests() {

		// Admins only.
		if (!current_user_can('manage_options')) {
			return;
		}

		// If nonce checks out, purge the error log.
		if (check_ajax_referer( 'purge_error_log' )) {
			if (!empty($this->error_log) && is_writable($this->error_log)) {
				file_put_contents($this->error_log, ''); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			}
		}

		// ...and that's that.
		wp_die();

	}

	/**
	 * Update filter options
	 *
	 * This method updates the plugin's settings made with the checkboxes at the
	 * top of the display; for filtering the displayed errors.
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
		if (!wp_verify_nonce(sanitize_key(wp_unslash($_POST[$nonce_name])), $nonce_name)) {
			return false;
		}

		// Date is a display option, not an error type; prepend it manually.
		$this->options['datetime'] = (isset($_POST[PLUGIN_PREFIX]['datetime'])) ? 1 : '';

		// Gather display options; ensure clean values.
		foreach (array_keys($this->get_error_types()) as $type) {
			$this->options[$type] = (isset($_POST[PLUGIN_PREFIX][$type])) ? 1 : '';
		}

		// More stack trace properties; mirrored from stack trace title setting.
		$this->options['stack_trace_step'] = (!empty($this->options['stack_trace_title'])) ? 1 : '';
		$this->options['stack_trace_origin'] = (!empty($this->options['stack_trace_title'])) ? 1 : '';

		// Sorting is a display option, not an error type; append it manually.
		$this->options['reverse_sort'] = (isset($_POST[PLUGIN_PREFIX]['reverse_sort'])) ? 1 : '';

		// Update options.
		update_option(PLUGIN_PREFIX, $this->options);

		// Redirect to ensure admin bar alerts show correct integer(s).
		wp_safe_redirect(admin_url('tools.php?page='.PLUGIN_SHORT_SLUG));
		exit;

	}

	/**
	 * Render success message
	 *
	 * This is only used for a "traditional" log purge; that is, a purge that is
	 * done via traditional URL, rather than AJAX.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 */
	public function markup_success_message() {

		// Assemble a dismissible message.
		$markup = '<div class="notice notice-success is-dismissible">';
		$markup .= '<p>'.esc_html__('Error log has been emptied.', 'codepotent-php-error-log-viewer').'</p>';
		$markup .= '</div>'."\n";

		// Delete the transient that triggered this message.
		delete_transient(PLUGIN_PREFIX.'_purged');

		// Return the markup string.
		return $markup;

	}

	/**
	 * Markup filter inputs
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML form for filtering the errors displayed.
	 */
	public function markup_display_inputs() {

		// Form open.
		$markup = '<form id="'.PLUGIN_SLUG.'-filter" method="post" action="'.admin_url('/tools.php?page='.PLUGIN_SHORT_SLUG).'">';

		// Markup the nonce field.
		$markup .= wp_nonce_field(PLUGIN_PREFIX.'_nonce', PLUGIN_PREFIX.'_nonce', true, false);

		// Date/time input.
		$markup .= '<label>'.esc_html__('Date/Time', 'codepotent-php-error-log-viewer').' <input type="checkbox" name="'.PLUGIN_PREFIX.'[datetime]" value="1" '.checked($this->options['datetime'], 1, false).'></label>';

		// Divider.
		$markup .= '<span class="codepotent-php-error-log-viewer-option-divider"></span>';

		// Error type texts are translated/escaped here, not in the loop below.
		$error_types = $this->get_error_types();

		// Print the labels and inputs.
		foreach ($error_types as $type=>$text) {
			$total = !empty($this->errors[$type]) ? count($this->errors[$type]) : 0;
			if (strpos($type, 'stack_trace') !== 0) {
				$markup .= '<label>'.$text.' ('.number_format($total).') <input type="checkbox" name="'.PLUGIN_PREFIX.'['.$type.']" value="1" '.checked($this->options[$type], 1, false).'></label>';
			}
		}

		// Divider.
		$markup .= '<span class="codepotent-php-error-log-viewer-option-divider"></span>';

		// Sort input.
		$markup .= '<label>';
		$markup .= esc_html__('Show Stack Traces', 'codepotent-php-error-log-viewer');
		$markup .= ' <input type="checkbox" name="'.PLUGIN_PREFIX.'[stack_trace_title]" value="1" '.checked($this->options['stack_trace_title'], 1, false).'>';
		$markup .= '</label>';

		// Divider.
		$markup .= '<span class="codepotent-php-error-log-viewer-option-divider"></span>';

		// Sort input.
		$markup .= '<label>';
		$markup .= esc_html__('Reverse Sort', 'codepotent-php-error-log-viewer');
		$markup .= ' <input type="checkbox" name="'.PLUGIN_PREFIX.'[reverse_sort]" value="1" '.checked($this->options['reverse_sort'], 1, false).'>';
		$markup .= '</label>';

		// Markup the submit button.
		$markup .= '<input type="submit" class="button button-primary" name="submit" value="'.esc_html__('Apply Filters', 'codepotent-php-error-log-viewer').'">';

		// Close the form.
		$markup .= '</form>';

		// Return markup string.
		return $markup;

	}

	/**
	 * Markup jump links
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
	 * @param string $where Set to "header" if not "footer".
	 *
	 * @return string Any generated markup.
	 */
	public function markup_jump_link($where) {

		// Initialization.
		$markup = '';

		// Not many errors currently displaying? Bail.
		if ($this->errors_displayed < 10) {
			return $markup;
		}

		// Container.
		$markup .= '<div class="alignleft">';

		// Markup the jump depending on whether it's for the header or footer.
		if ($where === 'header') {
			$markup .= '<a href="#nav-jump-bottom" class="'.PLUGIN_SLUG.'-jump-link">'.esc_html__('Skip to bottom', 'codepotent-php-error-log-viewer').'</a>';
		} else {
			$markup .= '<a id="nav-jump-bottom" href="#nav-jump-top" class="'.PLUGIN_SLUG.'-jump-link">'.esc_html__('Back to top', 'codepotent-php-error-log-viewer').'</a>';
		}

		// Container.
		$markup .= '</div>';

		// Return markup string.
		return $markup;

	}

	/**
	 * Markup action buttons
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
	 * @param $where string Location, top or bottom.
	 *
	 * @return string HTML markup for refresh and purge buttons.
	 */
	public function markup_action_buttons($where) {

		if ($where !== 'top') {
			$where = 'bottom';
		}

		// Open containers.
		$markup = '<div class="'.PLUGIN_SLUG.'-buttons-'.$where.'">';
		$markup .= '<span class="alignright">';

		// Refresh button.
		$markup .= '<a href="'.admin_url('tools.php?page='.PLUGIN_SHORT_SLUG).'" class="button button-secondary '.PLUGIN_SLUG.'-buttons">'.esc_html__('Refresh Error Log', 'codepotent-php-error-log-viewer').'</a>';

		// Purge button; if log is writeable.
		if (is_writable($this->error_log)) {
			$markup .= '<a href="#" id="'.PLUGIN_SLUG.'-confirm-purge-'.$where.'" class="button button-secondary '.PLUGIN_SLUG.'-buttons">'.esc_html__('Purge Error Log', 'codepotent-php-error-log-viewer').'</a>';
		}

		// Close containers.
		$markup .= '</span>';
		$markup .= '</div><!-- .'.PLUGIN_SLUG.'_buttons ['.$where.'] -->';

		// Return the string.
		return $markup;

	}

	/**
	 * Markup error log size
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @deprecated As of 2.2.0, use markup_filesize_location_indicator instead.
	 *
	 * @return string Text representation, ie, 123 bytes, 10.3 kB, 1.2 MB
	 */
	public function markup_filesize_indicator() {

		return $this->markup_filesize_location_indicator();

	}

	/**
	 * Markup error log size and location.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.2.0
	 *
	 * @return string Text representation, ie, 123 bytes, 10.3 kB, 1.2 MB
	 */
	public function markup_filesize_location_indicator() {

		// Cast the log size.
		settype($this->filesize, 'int');

		// Setup default display text.
		$display_text = sprintf(
			esc_html__('%d bytes', 'codepotent-php-error-log-viewer'),
			$this->filesize
			);

		// Is error log greater than 1MB? Change the text to suit.
		if ($this->filesize > 1000000) {
			$display_text = sprintf(
				esc_html__('%d MB', 'codepotent-php-error-log-viewer'),
				round($this->filesize/1000000, 1)
				);
		}
		// Is error log greater than 1kB? Change the text to suit.
		else if ($this->filesize > 1000) {
			$display_text = sprintf(
				esc_html__('%d kB', 'codepotent-php-error-log-viewer'),
				round($this->filesize/1000, 1)
				);
		}

		// Markup file location and filesize.
		$markup = '<div class="'.PLUGIN_SLUG.'-filesize">';
		$markup .= '<strong>'.esc_html__('Log File', 'codepotent-php-error-log-viewer').'</strong>: <span>'.$this->error_log.'</span>';
		$markup .= ' &nbsp; ';
		$markup .= '<strong>'.esc_html__('Log Size', 'codepotent-php-error-log-viewer').'</strong>: <span class="log-size">'.$display_text.'</span>';
		$markup .= '</div>';

		// Return the string.
		return $markup;

	}

	/**
	 * Markup information legend
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @return string Markup for the legend.
	 */
	public function markup_legend() {

		// Error types.
		$types = $this->get_error_types();

		// Open container.
		$markup = '<div class="'.PLUGIN_SLUG.'-legend">';

		// Title.
		$markup .= '<h3 class="'.PLUGIN_SLUG.'-legend-title">'.esc_html__('Legend', 'codepotent-php-error-log-viewer').'</h3>';

		// Markup each legend item.
		foreach ($types as $type=>$text) {
			if ($type !== 'stack_trace_step' && $type !== 'stack_trace_origin') {
				$markup .= '<div class="'.PLUGIN_SLUG.'-legend-box item-php-'.str_replace('_', '-', $type).'">'.$text.'</div>';
			}
		}

		// Close container.
		$markup .= '</div> <!-- .'.PLUGIN_SLUG.'-legend -->'."\n";

		// Return the markup.
		return $markup;

	}

	/**
	 * Markup error rows
	 *
	 * This method handles markup generation for the error entries.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 * @param array $raw_errors All errors as read from the log file.
	 * @param array $typed_errors[type][line] Line numbers keyed by error type.
	 *
	 * @return string|mixed
	 */
	public function markup_error_rows() {

		// Initialize the markup string.
		$markup = '';

		// Iterate over raw_errors array.
		foreach (array_keys($this->raw_errors) as $n) {

			// Get error type from its line position.
			$type = $this->error_map[$n];

			// Not currently displaying this type of error? Next!
			if (empty($this->options[$type])) {
				continue;
			}

			/**
			 * Stack trace titles are padded to make sure they "touch" the error
			 * that produced them. If stack traces are displayed and errors have
			 * been supressed from display, this block ensures that the rows are
			 * separated appropriately.
			 */
			$style = '';
			if ($type === 'stack_trace_title' && !$this->options['error']) {
				$style = ' style="margin-top:13px;"';
			}

			// Mark up the error row.
			$markup .= '<div class="error-log-row php-'.str_replace('_', '-', $type).'"'.$style.'>';
			$markup .= $this->errors[$type][$n];
			$markup .= '</div>'."\n";

		}

		// Return the string.
		return $markup;

	}

	/**
	 * Reverse sort errors
	 *
	 * Reversing the display order of errors in the log is more complicated than
	 * it seems on the surface. It's the stack trace data that screws everything
	 * up – simply reversing the array means the stack traces are then contained
	 * in the array above their respective errors and things break down. To sort
	 * the entries in reverse order, the stack trace data must first be removed,
	 * saved aside in a temp variable, then the remaining (actual) errors sorted
	 * while preserving their line position values. From there, the stack traces
	 * are re-added back to the mix, in preserved order and having been keyed to
	 * the particular error line to which they apply with some creative keywork.
	 * Since those newly keyed items will be at the end of the array, (and would
	 * display at the end of the error list,) a final reverse sort is applied. I
	 * am only bothering to explain this here because when someone sees the code
	 * it took to achieve this, there is going to be some 'splaining to do. Hey,
	 * if you have a better solution, I'm all ears! :)
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public function reverse_sort_errors() {

		// Initialization.
		$stack_trace_parts = $actual_errors = [];

		// Key for reuniting stack trace data with parent errors after sort.
		$error_line_number = 0;

		// Iterate over the raw lines read in from the error log.
		foreach ($this->raw_errors as $n=>$error) {

			// Trim any split ends off the line.
			$error = trim($error);

			// Get the error's type.
			$type = $this->get_error_type($error);

			// If dealing with stack trace data, capture it; move on.
			if (strpos($type, 'stack_trace') === 0) {
				$stack_trace_parts[$error_line_number][$n] = $error;
				continue;
			}

			// Capture everyting else (ie, not stack trace data) as an error.
			$actual_errors[$n] = $error;

			// Update the key to ensure stack trace data stays in sync.
			$error_line_number = $n;

		}

		// Sort the now-stack-trace-free array; preserve keys.
		krsort($actual_errors, SORT_NUMERIC);

		// Iterate over the stack trace data that was captured.
		$i = 0;
		foreach ($stack_trace_parts as $error_line=>$errors) {
			// Rekey stack traces to fall under related errors in the array.
			foreach ($errors as $error) {
				$i += .05;
				$actual_errors[(string)($error_line-$i)] = $error;
			}
		}

		// Newly keyed items are at the bottom; resort, preserving keys.
		krsort($actual_errors, SORT_NUMERIC);

		// And set the whole affair back to the object.
		$this->raw_errors = $actual_errors;

	}

	/**
	 * Provide notice and possible solutions if error log not found
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
		$markup .= '<h1>'.esc_html__('PHP Error Log', 'codepotent-php-error-log-viewer').'</h1>';

		// Description of issue.
		$markup .= '<div class="notice notice-error"><p>'.esc_html__('Your PHP error log could not be found.', 'codepotent-php-error-log-viewer').'</p></div>';

		// Probable solution.
		$markup .= '<h3>'.esc_html__('Possible Solution', 'codepotent-php-error-log-viewer').'</h3>';
		$markup .= '<p>';
		$markup .= sprintf(
				esc_html__('Open your %1$swp-config.php%2$s file and find the line that reads %1$sdefine(\'WP_DEBUG\', false);%2$s. Replace that single line with all of the following lines. Be sure to change the path to reflect the location of your PHP error log file. You can (and should) place your error log file outside your publicly accessible web directory.', 'codepotent-php-error-log-viewer'),
				'<code>',
				'</code>'
				);
		$markup .= '</p>';
		$markup .= '<p><textarea rows="14">';
		$markup .= '// Define a custom error log file'."\n".'$error_log_file = \'/path/to/php-error-log-Rh2Eu3r5V7e5Wrha.log\';'."\n\n";
		$markup .= '// Turn on ClassicPress debugging'."\n".'define(\'WP_DEBUG\', true);'."\n\n";
		$markup .= '// Turn off error display'."\n".'define(\'WP_DEBUG_DISPLAY\', false);'."\n\n";
		$markup .= '// Prevent ClassicPress from setting error log file'."\n".'define(\'WP_DEBUG_LOG\', false);'."\n\n";
		$markup .= '// Set the error log file'."\n".'ini_set(\'error_log\', $error_log_file);';
		$markup .= '</textarea></p>';

		// No dice? Maybe try .htaccess?
		$markup .= '<h3>'.esc_html__('Still not working?', 'codepotent-php-error-log-viewer').'</h3>';
		$markup .= '<p>¯\_(ツ)_/¯</p>';

		// Plugin container.
		$markup .= '</div><!-- #'.PLUGIN_SLUG.'-not-found -->';

		// Core container.
		$markup .= '</div><!-- .wrap -->';

		// Return the markup string.
		return $markup;

	}

	/**
	 * Render confirmation notices
	 *
	 * This method renders the success and failure messages that are shown after
	 * the log is purged with AJAX via the admin bar link. These notices are for
	 * use in every admin view since the log can be deleted while on any screen.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.2.0
	 */
	public function render_confirmation_notices() {

		// Success message.
		echo '<div class="'.esc_html(PLUGIN_SLUG).'-success notice notice-success is-dismissible" style="display:none;">';
		echo '    <p>'.esc_html__('Error log has been emptied.', 'codepotent-php-error-log-viewer').'</p>';
		echo '    <button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__('Dismiss this notice.', 'codepotent-php-error-log-viewer').'</span></button>';
		echo '</div>';

		// Failure message.
		echo '<div class="'.esc_html(PLUGIN_SLUG).'-failure notice notice-error is-dismissible" style="display:none;">';
		echo '    <p>'.esc_html__('Error log purge failed. Please try again.', 'codepotent-php-error-log-viewer').'</p>';
		echo '    <button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__('Dismiss this notice.', 'codepotent-php-error-log-viewer').'</span></button>';
		echo '</div>';

	}

	/**
	 * Render PHP errors
	 *
	 * This functions render HTML, so escaping function are not used.
	 *
	 * @author John Alarcon
	 *
	 * @since 1.0.0
	 *
	 */
	public function render_php_error_log() {

		// No permission to see the log? Bail.
		if (!current_user_can('manage_options')) {
			return;
		}

		// Can't find error log? Describe a possible solution; return early.
		if (!$this->error_log) {
			echo $this->markup_error_log_404(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		// Outer container.
		echo '<div class="wrap" id="'.esc_html(PLUGIN_SLUG).'">';

		// Display success message if error log was just purged.
		if (get_transient(PLUGIN_PREFIX.'_purged')) {
			echo $this->markup_success_message();  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Print plugin title.
		echo '<h1 id="nav-jump-top">'.esc_html__('PHP Error Log', 'codepotent-php-error-log-viewer').'</h1>';

		// Print filter checkboxes.
		echo $this->markup_display_inputs($this->errors, $this->options);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Print a jump-link in the header.
		echo $this->markup_jump_link('header'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Print buttons for refresh and purge actions.
		echo $this->markup_action_buttons('top'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Print the filesize.
		echo $this->markup_filesize_location_indicator(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Filter before legend; for any explanatory text.
		echo apply_filters(PLUGIN_PREFIX.'_before_legend', ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Print the legend.
		echo $this->markup_legend(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Filter after legend; for any explanatory text.
		echo apply_filters(PLUGIN_PREFIX.'_after_legend', '');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// If error log is empty, go no further; close wrapper and return.
		if (empty($this->errors)) {
			echo '</div><!-- .wrap -->';
			return;
		}

		// Print the error rows.
		echo $this->markup_error_rows(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Another jump-link, if the display grows long.
		echo $this->markup_jump_link('footer');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Print buttons to refresh and purge errors; for long pages.
		if ($this->errors_displayed > 10) {
			echo $this->markup_action_buttons('bottom'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// That's a wrap – thanks, everyone!
		echo '</div><!-- .wrap -->';

	}

	/**
	 * Filter footer text
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

		// Are we on this post type's screen? If so, change the footer text.
		if (strpos(get_current_screen()->base, PLUGIN_SHORT_SLUG)) {
			$text = '<span id="footer-thankyou" style="vertical-align:text-bottom;"><a href="'.PLUGIN_AUTHOR_URL.'/" title="'.PLUGIN_DESCRIPTION.'">'.PLUGIN_NAME.'</a> '.PLUGIN_VERSION.' &#8211; by <a href="'.PLUGIN_AUTHOR_URL.'">'.PLUGIN_AUTHOR.'</a></span>';
		}

		// Return the string.
		return $text;

	}

	/**
	 * Plugin activation
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

		// Initialize the options array.
		$options = [];

		// Make sure the datetime variable is set.
		$options['datetime'] = 1;

		// Iterate over error types; ensure they, too, are set.
		foreach (array_keys($this->get_error_types()) as $type) {
			$options[$type] = 1;
		}

		// Set the sort order.
		$options['reverse_sort'] = 0;

		// Update with defaults.
		update_option(PLUGIN_PREFIX, $options);

	}

	/**
	 * Plugin deactivation
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
	 * Plugin deletion
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