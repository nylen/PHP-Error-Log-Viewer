<?php

/**
 * -----------------------------------------------------------------------------
 * Purpose: Constant definitions.
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

/**
 * @author John Alarcon
 *
 * @since 1.0.0
 */

// Declare the namespace.
namespace CodePotent\PhpErrorLogViewer;

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

// Gain access to the get_home_path() function.
require_once(ABSPATH.'wp-admin/includes/file.php');

// -----------------------------------------------------------------------------
// BASIC PLUGIN CONSTANTS
// -----------------------------------------------------------------------------
// Plugin name in user-readable format.
const PLUGIN_NAME = 'PHP Error Log Viewer';
// Plugin version number.
const PLUGIN_VERSION = '1.0.0';
// Plugin slug; the plugin's own directory name.
const PLUGIN_SLUG = 'codepotent-php-error-log-viewer';
// Plugin file; the primary file that helms the plugin.
define(__NAMESPACE__.'\PLUGIN_FILE', PLUGIN_SLUG.'.php');
// Plugin prefix; for settings, form inputs, hooks; to avoid collisions.
define(__NAMESPACE__.'\PLUGIN_PREFIX', str_replace('-', '_', PLUGIN_SLUG));
// Plugin repo URL.
const PLUGIN_REPO_URL = 'https://github.com/codepotent';

// -----------------------------------------------------------------------------
// ADMIN MENU
// -----------------------------------------------------------------------------
const PLUGIN_MENU_TEXT = 'PHP Error Log';
const PLUGIN_MENU_ICON = 'dashicons-layout';
const PLUGIN_MENU_POS = 1;

// -----------------------------------------------------------------------------
// PATHS & URLS
// -----------------------------------------------------------------------------
// Ex: /home/user/mysite
define(__NAMESPACE__.'\PATH_HOME', untrailingslashit(get_home_path()));
// Ex: https://mysite.com
define(__NAMESPACE__.'\URL_HOME', untrailingslashit(home_url()));
// Ex: /home/user/mysite/wp-admin
const PATH_ADMIN = PATH_HOME.'/wp-admin';
// Ex: /home/user/mysite/wp-content/plugins
const PATH_PLUGINS = WP_PLUGIN_DIR;
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name
const PATH_SELF = PATH_PLUGINS.'/'.PLUGIN_SLUG;
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/extensions
const PATH_EXTENSIONS = PATH_SELF.'/extensions';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/scripts
const PATH_SCRIPTS = PATH_SELF.'/scripts';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/styles
const PATH_STYLES = PATH_SELF.'/styles';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/fonts
const PATH_FONTS = PATH_SELF.'/fonts';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/images
const PATH_IMAGES = PATH_SELF.'/images';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/languages
const PATH_LANGUAGES = PATH_SELF.'/languages';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/includes
const PATH_INCLUDES = PATH_SELF.'/includes';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/classes
const PATH_CLASSES = PATH_SELF.'/classes';
// Ex: /home/user/mysite/wp-content/plugins/my-plugin-name/templates
const PATH_TEMPLATES = PATH_SELF.'/templates';
// Ex: https://mysite.com/wp-admin
const URL_ADMIN = URL_HOME.'/wp-admin';
// Ex: https://mysite.com/wp-content/plugins
const URL_PLUGINS = WP_PLUGIN_URL;
// Ex: https://mysite.com/wp-content/plugins/my-plugin-name
const URL_SELF = URL_PLUGINS.'/'.PLUGIN_SLUG;
// Ex: https://mysite.com/wp-content/plugins/my-plugin-name/extensions
const URL_EXTENSIONS = URL_SELF.'/extensions';
// Ex: https://mysite.com/wp-content/plugins/my-plugin-name/scripts
const URL_SCRIPTS = URL_SELF.'/scripts';
// Ex: https://mysite.com/wp-content/plugins/my-plugin-name/styles
const URL_STYLES = URL_SELF.'/styles';
// Ex: https://mysite.com/wp-content/plugins/my-plugin-name/images
const URL_IMAGES = URL_SELF.'/images';

// -----------------------------------------------------------------------------
// CODE POTENT SPECIFIC
// -----------------------------------------------------------------------------
const CODEPOTENT_URL    = 'https://codepotent.com';
const CODEPOTENT_ORANGE = 'f8951d';
const CODEPOTENT_BLUE   = '337dc1';

// Logos as encoded SVG.
const CODEPOTENT_LOGO_SVG_WORDS = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgd2lkdGg9IjU2NS4zNTgiCiAgIGhlaWdodD0iODAuODY0IgogICB2aWV3Qm94PSIwIDAgNTY1LjM1ODQgODAuODYzOTk4IgogICBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCA1OTAgMTEzIgogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmc1ODk4IgogICBzb2RpcG9kaTpkb2NuYW1lPSJjb2RlcG90ZW50LWxvZ290eXBlLXdvcmRzLXJlc2FtcGxlZC5zdmciCiAgIGlua3NjYXBlOnZlcnNpb249IjAuOTIuMiAoNWMzZTgwZCwgMjAxNy0wOC0wNikiPgogIDxtZXRhZGF0YQogICAgIGlkPSJtZXRhZGF0YTU5MDQiPgogICAgPHJkZjpSREY+CiAgICAgIDxjYzpXb3JrCiAgICAgICAgIHJkZjphYm91dD0iIj4KICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL3N2Zyt4bWw8L2RjOmZvcm1hdD4KICAgICAgICA8ZGM6dHlwZQogICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+CiAgICAgICAgPGRjOnRpdGxlPjwvZGM6dGl0bGU+CiAgICAgIDwvY2M6V29yaz4KICAgIDwvcmRmOlJERj4KICA8L21ldGFkYXRhPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM1OTAyIiAvPgogIDxzb2RpcG9kaTpuYW1lZHZpZXcKICAgICBwYWdlY29sb3I9IiNmZmZmZmYiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgYm9yZGVyb3BhY2l0eT0iMSIKICAgICBvYmplY3R0b2xlcmFuY2U9IjEwIgogICAgIGdyaWR0b2xlcmFuY2U9IjEwIgogICAgIGd1aWRldG9sZXJhbmNlPSIxMCIKICAgICBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMCIKICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIgogICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iNzE2IgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjQ4MCIKICAgICBpZD0ibmFtZWR2aWV3NTkwMCIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgaW5rc2NhcGU6em9vbT0iMC41MDc2NDI5NyIKICAgICBpbmtzY2FwZTpjeD0iMjgyLjY3ODk5IgogICAgIGlua3NjYXBlOmN5PSI0MC40MzE5OTkiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LW1heGltaXplZD0iMCIKICAgICBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJzdmc1ODk4IiAvPgogIDxnCiAgICAgaWQ9Imc1ODk2Ij4KICAgIDxwYXRoCiAgICAgICBmaWxsPSIjZjA5NDMzIgogICAgICAgZD0iTTIwOS43MyA1NS42aDIwLjE0djEwLjdoLTIwLjE0eiIKICAgICAgIGlkPSJwYXRoNTg3NCIgLz4KICAgIDxwYXRoCiAgICAgICBmaWxsPSIjZjA5NDMzIgogICAgICAgZD0iTTM3LjA2IDMwLjlWMTkuN0gxNS43Yy0xLjk0IDAtMy42Ni4yNC01LjE4LjczLTEuNTMuNS0yLjg3IDEuMTQtNC4wMyAxLjk0LTEuMTguOC0yLjE3IDEuNzQtMyAyLjgtLjggMS4wNy0xLjQ3IDIuMTgtMiAzLjM0QzEgMjkuNy42IDMwLjg2LjM4IDMyLjA0LjEyIDMzLjIgMCAzNC4zMyAwIDM1LjR2MTUuMTZjMCAyLjkuNTMgNS4zNiAxLjU3IDcuMzUgMS4wNSAyIDIuMzYgMy42MyAzLjkzIDQuODggMS41NiAxLjI1IDMuMjYgMi4xNSA1LjEgMi43IDEuODQuNTQgMy41NC44MiA1LjEuODJoMzAuMzhWNTUuMWgtMzAuM2MtMS41IDAtMi42My0uNC0zLjQtMS4xOC0uNzgtLjc3LTEuMTctMS45LTEuMTctMy4zNlYzNS40OGMwLTEuNTYuNC0yLjcgMS4xNS0zLjQ1Ljc2LS43NSAxLjg3LTEuMTIgMy4zNC0xLjEyeiIKICAgICAgIGlkPSJwYXRoNTg3NiIgLz4KICAgIDxwYXRoCiAgICAgICBmaWxsPSIjZjA5NDMzIgogICAgICAgZD0iTTEwNS43IDUwLjU2YzAgMS45My0uMjUgMy42Ni0uNzQgNS4yLS41IDEuNTMtMS4xNCAyLjg4LTEuOTQgNC4wNXMtMS43NCAyLjE2LTIuOCAyLjk4Yy0xLjA3LjgyLTIuMTggMS41LTMuMzQgMi0xLjE3LjUyLTIuMzQuOS0zLjU0IDEuMTUtMS4yLjI0LTIuMzIuMzctMy4zOC4zN0g2OS43NGMtMS41NSAwLTMuMjUtLjI4LTUuMDgtLjgyLTEuODQtLjU1LTMuNTUtMS40NS01LjEtMi43LTEuNTgtMS4yNS0yLjktMi44Ny0zLjkzLTQuODctMS4wNS0xLjk4LTEuNTgtNC40My0xLjU4LTcuMzRWMzUuNGMwLTIuOS41My01LjMyIDEuNTgtNy4zMnMyLjM1LTMuNjIgMy45Mi00Ljg3YzEuNTYtMS4yNCAzLjI3LTIuMTQgNS4xLTIuNjggMS44NS0uNTUgMy41NC0uODIgNS4xLS44MmgyMC4yYzIuODggMCA1LjMyLjUgNy4zNCAxLjU1IDIgMS4wMyAzLjYyIDIuMzMgNC44NiAzLjkgMS4yMyAxLjU3IDIuMTMgMy4yNyAyLjcgNS4xLjU1IDEuODUuODMgMy41Ni44MyA1LjE0djE1LjE2em0tMTEuMjItMTUuMWMwLTEuNTQtLjM4LTIuNy0xLjE2LTMuNDMtLjc4LS43NS0xLjktMS4xMi0zLjM2LTEuMTJINjkuODNjLTEuNSAwLTIuNjMuNC0zLjQgMS4xNS0uNzguNzYtMS4xNyAxLjg3LTEuMTcgMy4zNHYxNS4xNmMwIDEuNDcuNCAyLjYgMS4xNiAzLjM2Ljc4Ljc4IDEuOSAxLjE3IDMuNCAxLjE3aDIwLjE0YzEuNTIgMCAyLjY2LS40IDMuNC0xLjE4Ljc1LS43NyAxLjEyLTEuOSAxLjEyLTMuMzZ2LTE1LjF6IgogICAgICAgaWQ9InBhdGg1ODc4IiAvPgogICAgPHBhdGgKICAgICAgIGZpbGw9IiNmMDk0MzMiCiAgICAgICBkPSJNMTY2LjM0IDUwLjU2YzAgMS45My0uMjUgMy42Ni0uNzMgNS4yLS40OCAxLjUzLTEuMTMgMi44OC0xLjkzIDQuMDVzLTEuNzQgMi4xNi0yLjggMi45OGMtMS4wNy44Mi0yLjE4IDEuNS0zLjM1IDItMS4xNi41Mi0yLjM0LjktMy41MyAxLjE1LTEuMi4yNC0yLjMzLjM3LTMuNC4zN2gtMjAuMmMtMS45NCAwLTMuNjYtLjI1LTUuMTgtLjc0LTEuNTMtLjQ4LTIuODctMS4xNC00LjAzLTEuOTYtMS4xOC0uODItMi4xNy0xLjc2LTMtMi44Mi0uOC0xLjA2LTEuNDctMi4xOC0yLTMuMzQtLjUtMS4xNy0uOS0yLjM0LTEuMTMtMy41NC0uMjUtMS4yLS4zNy0yLjMtLjM3LTMuMzRWMzUuNGMwLTIuOS41Mi01LjMyIDEuNTctNy4zMnMyLjM2LTMuNjIgMy45My00Ljg3YzEuNTYtMS4yNCAzLjI2LTIuMTQgNS4xLTIuNjggMS44NC0uNTUgMy41NC0uODIgNS4xLS44MmgyMC4ydjExLjJIMTMwLjVjLTEuNSAwLTIuNjMuNC0zLjQgMS4xNS0uOC43Ni0xLjE3IDEuODctMS4xNyAzLjM0djE1LjA3YzAgMS41My40IDIuNjggMS4xNSAzLjQ1Ljc2Ljc4IDEuODcgMS4xNyAzLjM0IDEuMTdoMjAuMmMxLjUzIDAgMi42Ni0uNCAzLjQtMS4xOC43Ni0uNzcgMS4xMy0xLjkgMS4xMy0zLjM2VjBoMTEuMnoiCiAgICAgICBpZD0icGF0aDU4ODAiIC8+CiAgICA8cGF0aAogICAgICAgZmlsbD0iI2YwOTQzMyIKICAgICAgIGQ9Ik0yMjcuMTYgMzUuMTNjMCAxLjU1LS4yNyAzLjIzLS44MiA1LjAyLS41NSAxLjgtMS40MyAzLjQ2LTIuNjUgNS0xLjIzIDEuNTQtMi44MyAyLjgyLTQuOCAzLjg2LTEuOTYgMS4wNC00LjM2IDEuNTYtNy4xNyAxLjU2SDE5MS41VjM5LjloMjAuMjNjMS41MiAwIDIuNy0uNDUgMy41My0xLjQuODMtLjkyIDEuMjUtMi4wOCAxLjI1LTMuNDYgMC0xLjQ2LS40Ni0yLjYtMS40LTMuNDUtLjkyLS44NC0yLjA1LTEuMjYtMy4zNy0xLjI2SDE5MS41Yy0xLjUgMC0yLjcuNDctMy41MiAxLjQtLjg0Ljk0LTEuMjUgMi4xLTEuMjUgMy40OHYxNS42NGMwIDEuNS40NiAyLjY2IDEuNCAzLjUuOTMuODMgMi4xIDEuMjQgMy40NyAxLjI0aDIwLjEzdjEwLjdIMTkxLjVjLTEuNTQgMC0zLjItLjI4LTUtLjgzLTEuOC0uNTQtMy40Ny0xLjQyLTUtMi42NS0xLjU1LTEuMjItMi44My0yLjgtMy44Ny00Ljc4LTEuMDMtMS45Ny0xLjU1LTQuMzYtMS41NS03LjE4VjM1LjEzYzAtMS41NS4yNy0zLjIzLjgyLTUuMDIuNTUtMS44IDEuNDMtMy40NiAyLjY1LTUgMS4yMi0xLjUzIDIuODItMi44MiA0LjgtMy44NSAxLjk1LTEuMDMgNC4zNS0xLjU1IDcuMTYtMS41NWgyMC4yM2MxLjU1IDAgMy4yMi4yNyA1LjAyLjgyIDEuOC41NCAzLjQ2IDEuNDMgNSAyLjY1czIuODIgMi44IDMuODYgNC43OGMxLjA0IDEuOTcgMS41NiA0LjM2IDEuNTYgNy4xOHoiCiAgICAgICBpZD0icGF0aDU4ODIiIC8+CiAgICA8cGF0aAogICAgICAgZmlsbD0iIzM1N2VjMCIKICAgICAgIGQ9Ik0yODYuMDggNTAuNTZjMCAxLjkzLS4yNCAzLjY2LS43MyA1LjItLjUgMS41My0xLjEzIDIuODgtMS45NCA0LjA1LS44IDEuMTctMS43MyAyLjE2LTIuOCAyLjk4LTEuMDUuODItMi4xNyAxLjUtMy4zMyAyLTEuMTYuNTItMi4zNC45LTMuNSAxLjE1LTEuMi4yNC0yLjMuMzctMy4zOC4zN2gtMjAuMjJWNTUuMWgyMC4yYzEuNSAwIDIuNjMtLjQgMy4zOC0xLjE4Ljc0LS43NyAxLjEyLTEuOSAxLjEyLTMuMzZ2LTE1LjFjMC0xLjU0LS4zOC0yLjctMS4xNC0zLjQzLS43Ny0uNzUtMS44OC0xLjEzLTMuMzUtMS4xM2gtMjAuMTRjLTEuNTIgMC0yLjY3LjQtMy40NCAxLjE1LS43OC43Ni0xLjE3IDEuODctMS4xNyAzLjM0djQ1LjQ2aC0xMS4yVjM1LjRjMC0xLjk0LjI0LTMuNjYuNzMtNS4xOC41LTEuNTMgMS4xNC0yLjg3IDEuOTYtNC4wMy44Mi0xLjE4IDEuNzYtMi4xNyAyLjgyLTMgMS4wNi0uOCAyLjE4LTEuNDggMy4zNC0yIDEuMTctLjUgMi4zNC0uOSAzLjU0LTEuMTMgMS4yLS4yNSAyLjMtLjM3IDMuMzQtLjM3aDIwLjJjMS45NCAwIDMuNjYuMjQgNS4yLjczIDEuNS41IDIuODUgMS4xNCA0LjAyIDEuOTQgMS4xNi44IDIuMTUgMS43NCAyLjk3IDIuOC44MiAxLjA3IDEuNSAyLjE4IDIgMy4zNC41MiAxLjE4LjkgMi4zNSAxLjE1IDMuNTMuMjQgMS4xOC4zNiAyLjMuMzYgMy4zNnoiCiAgICAgICBpZD0icGF0aDU4ODQiIC8+CiAgICA8cGF0aAogICAgICAgZmlsbD0iIzM1N2VjMCIKICAgICAgIGQ9Ik0zNDYuMzQgNTAuNTZjMCAxLjkzLS4yNCAzLjY2LS43MyA1LjItLjQ4IDEuNTMtMS4xMiAyLjg4LTEuOTMgNC4wNS0uOCAxLjE3LTEuNzQgMi4xNi0yLjggMi45OC0xLjA2LjgyLTIuMTggMS41LTMuMzQgMi0xLjE2LjUyLTIuMzQuOS0zLjUzIDEuMTUtMS4yLjI0LTIuMzMuMzctMy40LjM3aC0yMC4yYy0xLjU2IDAtMy4yNS0uMjgtNS4xLS44Mi0xLjgzLS41NS0zLjUzLTEuNDUtNS4xLTIuNy0xLjU3LTEuMjUtMi44Ny0yLjg3LTMuOTItNC44Ny0xLjA1LTEuOTgtMS41Ny00LjQzLTEuNTctNy4zNFYzNS40YzAtMi45LjUzLTUuMzIgMS41OC03LjMyczIuMzUtMy42MiAzLjkyLTQuODdjMS41Ny0xLjI0IDMuMjctMi4xNCA1LjEtMi42OCAxLjg1LS41NSAzLjU0LS44MiA1LjEtLjgyaDIwLjJjMi44OCAwIDUuMzMuNSA3LjM0IDEuNTUgMiAxLjAzIDMuNjMgMi4zMyA0Ljg3IDMuOSAxLjI0IDEuNTcgMi4xNCAzLjI3IDIuNyA1LjEuNTYgMS44NS44NCAzLjU2Ljg0IDUuMTR6bS0xMS4yLTE1LjFjMC0xLjU0LS40LTIuNy0xLjE3LTMuNDMtLjc3LS43NS0xLjktMS4xMi0zLjM2LTEuMTJIMzEwLjVjLTEuNSAwLTIuNjMuNC0zLjQgMS4xNS0uNzguNzYtMS4xNyAxLjg3LTEuMTcgMy4zNHYxNS4xNmMwIDEuNDcuNCAyLjYgMS4xOCAzLjM2Ljc3Ljc4IDEuOSAxLjE3IDMuNCAxLjE3aDIwLjEzYzEuNTMgMCAyLjY3LS40IDMuNDItMS4xOC43NC0uNzcgMS4xMi0xLjkgMS4xMi0zLjM2eiIKICAgICAgIGlkPSJwYXRoNTg4NiIgLz4KICAgIDxwYXRoCiAgICAgICBmaWxsPSIjMzU3ZWMwIgogICAgICAgZD0iTTM5OC4wMyAzMC45aC0xOS41N3YzNS40aC0xMS4zNFYzMC45aC0xNC41N1YxOS43aDE0LjU3VjQuNDhoMTEuMzRWMTkuN2gxOS41N3oiCiAgICAgICBpZD0icGF0aDU4ODgiIC8+CiAgICA8cGF0aAogICAgICAgZmlsbD0iIzM1N2VjMCIKICAgICAgIGQ9Ik00NTUuMzYgMzUuMTNjMCAxLjU1LS4yOCAzLjIzLS44MiA1LjAyLS41NSAxLjgtMS40MyAzLjQ2LTIuNjUgNS0xLjI0IDEuNTQtMi44MyAyLjgyLTQuOCAzLjg2LTEuOTcgMS4wNC00LjM2IDEuNTYtNy4xOCAxLjU2aC0yMC4yVjM5LjloMjAuMmMxLjUzIDAgMi43LS40NSAzLjU0LTEuNC44My0uOTIgMS4yNS0yLjA4IDEuMjUtMy40NiAwLTEuNDYtLjQ2LTIuNi0xLjQtMy40NS0uOTMtLjg0LTIuMDUtMS4yNi0zLjM4LTEuMjZoLTIwLjJjLTEuNTMgMC0yLjcuNDctMy41NSAxLjQtLjgzLjk0LTEuMjUgMi4xLTEuMjUgMy40OHYxNS42NGMwIDEuNS40NyAyLjY2IDEuNCAzLjUuOTQuODMgMi4xIDEuMjQgMy40NyAxLjI0aDIwLjEydjEwLjdoLTIwLjJjLTEuNTYgMC0zLjI0LS4yOC01LjAzLS44My0xLjgtLjU0LTMuNDgtMS40Mi01LTIuNjUtMS41NS0xLjIyLTIuODQtMi44LTMuODctNC43OC0xLjAzLTEuOTctMS41NS00LjM2LTEuNTUtNy4xOFYzNS4xM2MwLTEuNTUuMjctMy4yMy44Mi01LjAyLjU0LTEuOCAxLjQzLTMuNDYgMi42NS01IDEuMjItMS41MyAyLjgtMi44MiA0Ljc4LTMuODUgMS45Ny0xLjAzIDQuMzYtMS41NSA3LjE4LTEuNTVoMjAuMjJjMS41NiAwIDMuMjMuMjcgNS4wMy44MiAxLjguNTQgMy40NiAxLjQzIDUgMi42NSAxLjUzIDEuMjIgMi44MiAyLjggMy44NSA0Ljc4IDEuMDQgMS45NyAxLjU2IDQuMzYgMS41NiA3LjE4eiIKICAgICAgIGlkPSJwYXRoNTg5MCIgLz4KICAgIDxwYXRoCiAgICAgICBmaWxsPSIjMzU3ZWMwIgogICAgICAgZD0iTTUxNC41NCA2Ni4zaC0xMS4yVjQwLjQyYzAtMS40Ni0uMjYtMi43OC0uNzYtMy45NC0uNS0xLjE4LTEuMi0yLjE3LTIuMDUtMy0uODYtLjg0LTEuODgtMS40OC0zLjA0LTEuOTMtMS4xNy0uNDQtMi40My0uNjYtMy43OC0uNjZoLTE5LjZ2MzUuNEg0NjIuOVYyNS4yNmMwLS43OC4xNC0xLjUuNDMtMi4xOC4zLS42Ny43LTEuMjYgMS4yLTEuNzcuNTMtLjUgMS4xMy0uODggMS44Mi0xLjE3LjctLjMgMS40Mi0uNDMgMi4yLS40M2gyNS4yNmMxLjQgMCAyLjkuMTYgNC40Ny40NyAxLjU2LjMyIDMuMS44MyA0LjY1IDEuNTMgMS41NC43IDMgMS42IDQuNCAyLjY3IDEuNCAxLjA4IDIuNjMgMi40IDMuNyAzLjkzIDEuMDggMS41MyAxLjk0IDMuMyAyLjU3IDUuMzIuNjIgMiAuOTQgNC4yOC45NCA2Ljh6IgogICAgICAgaWQ9InBhdGg1ODkyIiAvPgogICAgPHBhdGgKICAgICAgIGZpbGw9IiMzNTdlYzAiCiAgICAgICBkPSJNNTY1LjM2IDMwLjlINTQ1Ljh2MzUuNGgtMTEuMzVWMzAuOWgtMTQuNTdWMTkuN2gxNC41N1Y0LjQ4aDExLjM0VjE5LjdoMTkuNTZ6IgogICAgICAgaWQ9InBhdGg1ODk0IiAvPgogIDwvZz4KPC9zdmc+Cg==';
const CODEPOTENT_LOGO_SVG_LETTERS = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgd2lkdGg9IjIxOS45NjEiCiAgIGhlaWdodD0iMTkwLjE0NiIKICAgdmlld0JveD0iMCAwIDgwLjE5MzI2IDY5LjMyMzUiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2ZzUyOTEiCiAgIHNvZGlwb2RpOmRvY25hbWU9ImNvZGUtcG90ZW50LWxvZ290eXBlLWxldHRlcnMtcmVzYW1wbGVkLnN2ZyIKICAgaW5rc2NhcGU6dmVyc2lvbj0iMC45Mi4yICg1YzNlODBkLCAyMDE3LTA4LTA2KSI+CiAgPG1ldGFkYXRhCiAgICAgaWQ9Im1ldGFkYXRhNTI5NyI+CiAgICA8cmRmOlJERj4KICAgICAgPGNjOldvcmsKICAgICAgICAgcmRmOmFib3V0PSIiPgogICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PgogICAgICAgIDxkYzp0eXBlCiAgICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz4KICAgICAgICA8ZGM6dGl0bGU+Q29kZSBQb3RlbnQgTG9nbzwvZGM6dGl0bGU+CiAgICAgIDwvY2M6V29yaz4KICAgIDwvcmRmOlJERj4KICA8L21ldGFkYXRhPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM1Mjk1IiAvPgogIDxzb2RpcG9kaTpuYW1lZHZpZXcKICAgICBwYWdlY29sb3I9IiNmZmZmZmYiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgYm9yZGVyb3BhY2l0eT0iMSIKICAgICBvYmplY3R0b2xlcmFuY2U9IjEwIgogICAgIGdyaWR0b2xlcmFuY2U9IjEwIgogICAgIGd1aWRldG9sZXJhbmNlPSIxMCIKICAgICBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMCIKICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIgogICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iNzE2IgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjQ4MCIKICAgICBpZD0ibmFtZWR2aWV3NTI5MyIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgaW5rc2NhcGU6em9vbT0iMS4yNDExNTE2IgogICAgIGlua3NjYXBlOmN4PSIxMDkuOTgwNSIKICAgICBpbmtzY2FwZTpjeT0iOTUuMDcyOTk4IgogICAgIGlua3NjYXBlOndpbmRvdy14PSIwIgogICAgIGlua3NjYXBlOndpbmRvdy15PSIwIgogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjAiCiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0ic3ZnNTI5MSIgLz4KICA8dGl0bGUKICAgICBpZD0idGl0bGU1MjgxIj5Db2RlIFBvdGVudCBMb2dvPC90aXRsZT4KICA8ZwogICAgIGlkPSJnNTI4OSI+CiAgICA8ZwogICAgICAgaWQ9Imc1Mjg3Ij4KICAgICAgPHBhdGgKICAgICAgICAgZmlsbD0iIzMzN2RjMSIKICAgICAgICAgZD0iTTQ4LjAyIDE2LjE3Yy01LjQ0IDAtOS44MyA0LjQtOS44MyA5LjgzdjQzLjMyaDkuNTNWNDEuNDhjMC0uMDQgMC0uMSAwLS4xNFYyNi44YzAtMS41IDEuMi0yLjcyIDIuNzItMi43MmgxNy41M2MxLjUyIDAgMi43NCAxLjIyIDIuNzQgMi43M3YxNC41NGMwIDEuNTItMS4yMiAyLjc0LTIuNzQgMi43NEg0OS40djkuMUg3MC4zN2M1LjQ0IDAgOS44Mi00LjQgOS44Mi05LjgzVjI2YzAtNS40NC00LjQtOS44My05LjgzLTkuODN6IgogICAgICAgICBpZD0icGF0aDUyODMiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGZpbGw9IiNmODk1MWQiCiAgICAgICAgIGQ9Ik05LjgzIDE1Ljg4QzQuMzggMTUuODggMCAyMC4yOCAwIDI1Ljd2MTcuMzZjMCA1LjQ0IDQuMzggOS44MiA5LjgzIDkuODJoOS4ydi4wM2gxNy43di04LjdIMTEuMDVjLTEuNDYgMC0yLjYzLTEuMTctMi42My0yLjYyVjI2Ljg0YzAtMS40NiAxLjE3LTIuNjMgMi42My0yLjYzaDE4LjkzVjE1Ljl6IgogICAgICAgICBpZD0icGF0aDUyODUiIC8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4K';

// Logos in HTML img tags.
const CODEPOTENT_LOGO_IMG_WORDS = '<img src="'.CODEPOTENT_LOGO_SVG_WORDS.'" alt="Code Potent" style="max-width:100%;">';
const CODEPOTENT_LOGO_IMG_LETTERS = '<img src="'.CODEPOTENT_LOGO_SVG_LETTERS.'" alt="Code Potent" style="height:1.2em;vertical-align:middle;">';

// Logos as linked images.
const CODEPOTENT_LOGO_LINKED_WORDS = '<a href="'.CODEPOTENT_URL.'" title="Code Potent">'.CODEPOTENT_LOGO_IMG_WORDS.'</a>';
const CODEPOTENT_LOGO_LINKED_LETTERS = '<a href="'.CODEPOTENT_URL.'" title="Code Potent">'.CODEPOTENT_LOGO_IMG_LETTERS.'</a>';
