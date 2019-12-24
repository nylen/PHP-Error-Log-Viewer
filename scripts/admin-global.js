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
 * Copyright © 2019 CodePotent
 * -----------------------------------------------------------------------------
 *           ____          _      ____       _             _
 *          / ___|___   __| | ___|  _ \ ___ | |_ ___ _ __ | |_
 *         | |   / _ \ / _` |/ _ \ |_) / _ \| __/ _ \ '_ \| __|
 *         | |__| (_) | (_| |  __/  __/ (_) | ||  __/ | | | |_
 *          \____\___/ \__,_|\___|_|   \___/ \__\___|_| |_|\__|.com
 *
 * -----------------------------------------------------------------------------
 */

// Vanilla JS to confirm purge requests.
document.addEventListener('click', function (event) {

	// Not the relevant element? Bail.
	if (!event.target.matches('#'+confirmation.plugin_slug+'-confirm-purge')) return;
	
	// Prevent link from being followed.
	event.preventDefault();
	
	// Pop the confirmation.
	var confirmed = window.confirm(confirmation.question);
	
	// Redirect to URL that initiates error log purge.
	if (confirmed) {
		window.location.href = confirmation.redirect;
	}

}, false);