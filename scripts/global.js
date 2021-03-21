/**
 * -----------------------------------------------------------------------------
 * Plugin Name: PHP Error Log Viewer
 * Purpose: Global JS for admin views.
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2020, Code Potent
 * -----------------------------------------------------------------------------
 *           ____          _      ____       _             _
 *          / ___|___   __| | ___|  _ \ ___ | |_ ___ _ __ | |_
 *         | |   / _ \ / _` |/ _ \ |_) / _ \| __/ _ \ '_ \| __|
 *         | |__| (_) | (_| |  __/  __/ (_) | ||  __/ | | | |_
 *          \____\___/ \__,_|\___|_|   \___/ \__\___|_| |_|\__|.com
 *
 * -----------------------------------------------------------------------------
 */

jQuery(document).ready(function($) {

	// Surface localized data.
	var localized = codepotent_php_error_log_viewer_data;

	// Surface the vendor prefix.
	let prefix = localized.prefix;

	// Handle basic log purges (via URL.)
	$('#'+prefix+'-confirm-purge-top,#'+prefix+'-confirm-purge-bottom').click(function(e) {
		e.preventDefault();
		if (confirm_purge()) {
			window.location.href = localized.deletion_link;
		}
	});

	// Hande inline log purges (via AJAX.)
	$('#wp-admin-bar-'+prefix+'-admin-bar-purge-link a').click(function(e) {
		e.preventDefault();
		if (confirm_purge()) {
			$.post(localized.ajax_url, {
				action: "purge_error_log",
				_ajax_nonce: localized.ajax_nonce,
			}).success(function() {
				$('#'+prefix+' .error-log-row').detach();
				$('#wp-admin-bar-'+prefix.replaceAll('-','_')+'_admin_bar .error-count-bubble').hide();
				$('.'+prefix+'-buttons-bottom').hide();
				$('.'+prefix+'-jump-link').hide();
				$('.'+prefix+'-filesize span.log-size').text(localized.text_zero_bytes);
				console.log(localized.text_ajax_success);
				$('.'+prefix+'-success').show('slow', function() {}).delay(2500).hide('slow', function() {});

			}).fail(function(resp) {
				$('.'+prefix+'-failure').show('slow', function() {
					console.log(localized.text_ajax_failure);
					console.log(resp);
				}).delay(2500).hide('slow', function() {});
			});
		}
	});

	// For confirming all purge actions.
	function confirm_purge() {
		if (window.confirm(localized.text_confirmation)) {
			return true;
		}
	}

});