<?php

/**
 * -----------------------------------------------------------------------------
 * Purpose: Logging function for PHP Error Log Viewer plugin for ClassicPress.
 * Author: John Alarcon
 * Author URI: https://codepotent.com
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2021, John Alarcon (Code Potent)
 * -----------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

/**
 * Send data to the error log
 *
 * A function to trigger an error which will dump arbitrary $data into the error
 * log, allowing it to be preserved, styled, filtered, sorted, and purged.
 *
 * So it's just ok to use trigger_errors() and print_r().
 * Also output is not escaped as it's intended to be used by a programmer.
 *
 * @author John Alarcon
 *
 * @since 2.2.0
 *
 * @param mixed   $data An integer, string, array, object, or whatever.
 * @param string  $type Type of error to trigger: warning, notice, or error.
 * @param boolean $file The path of the file that called this method.
 * @param boolean $line The line where this method was called.
 * @return void
 */
function codepotent_php_error_log_viewer_log($data, $type='notice', $file=false, $line=false) {

	// Validate the error level.
	$error_level = E_USER_NOTICE;
	if (in_array($type, ['deprecated', 'warning', 'error'], true)) {
		$error_level = constant('E_USER_'.strtoupper($type));
	}

	// Start off the error message by indicating the nature of the entry.
	$msg = esc_html__('User Generated', 'codepotent-php-error-log-viewer');

	// Let's also indicate the data type, shall we?
	$msg .= ': <strong>'.gettype($data).'</strong>';

	// If file path or line number exist, tack on a separator.
	if ($file || $line) {
		$msg .= ': ';
	}

	/**
	 * Validate and add file path, if any. Because Windows-based drives will
	 * not pass validate_file() (due to the drive name and colon,) the first
	 * block here will remove those parts, if present, before validating the
	 * path. If the path is then valid, those parts will be added back to it
	 * for display in the message. In this case, only the filename shows; to
	 * view the full path, the filename can be hovered.
	 */
	if ($file) {
		// Default flag.
		$windows = false;
		// Handle Windows-based drive paths.
		if (substr($file, 1, 1) === ':') {
			$drive = substr($file, 0, 2);
			$file = substr($file, 2);
			$windows = true;
		}
		// If path is valid, add it to the message.
		if (validate_file($file) === 0) {
			if ($windows) {
				$file = $drive.$file;
			}
			$msg .= '<span title="'.esc_attr($file).'">'.esc_html(basename($file)).'</span>';
		}
	}

	// Validate and add line number, if any.
	if ($line) {
		$msg .= sprintf(
			esc_html__(' at line %d', 'codepotent-php-error-log-viewer'),
			$line
			);
	}

	// Done with first line, break to a new line.
	$msg .= '<br>';

	// One last thing: convert true/false/null into displayalbe strings.
	if (is_bool($data) && !is_numeric($data)) {
		if ($data) {
			$data = 'true';
		} else {
			$data = 'false';
		}
	} else if (is_null($data)) {
		$data = 'null';
	}

	// Convert $data into a preformatted string and add it to the message.
	$msg .= '<pre>'.str_replace(["\r","\n"], '<br>', print_r($data, true)).'</pre>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

	// Send the whole affair off to the error log.
	trigger_error($msg, $error_level); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error,WordPress.Security.EscapeOutput.OutputNotEscaped

}