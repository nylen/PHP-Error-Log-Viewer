<?php

/**
 * -----------------------------------------------------------------------------
 * Purpose: plugin updates before the ClassicPress plugin directory goes live.
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright © 2016, Genbu Media
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

class fxUpdater_Updater{

	/**
	 * Class Constructor
	 */
	public function __construct() {

		/* Updater Config */
		$this->config = array(
			'server'  => 'https://codepotent.com/',
			'type'    => 'plugin',
			'id'      => basename(dirname(__FILE__, 2)).'/'.basename(dirname(__FILE__, 2)).'.php',
			'api'     => '1.0.0',
			'post'    => array(),
		);

		/* Admin Init */
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		/* Fix Install Folder */
		add_filter( 'upgrader_post_install', array( $this, 'fix_install_folder' ), 11, 3 );
	}

	/**
	 * Admin Init.
	 * Some functions only available in admin.
	 */
	public function admin_init(){

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'add_plugin_update_data' ), 10, 2 );
		add_filter( 'plugins_api_result', array( $this, 'plugin_info' ), 10, 3 );

	}

	/**
	 * Add plugin update data if available
	 */
	public function add_plugin_update_data( $value, $transient ){
		if( isset( $value->response ) ){
			$update_data = $this->get_data( 'query_plugins' );
			foreach( $update_data as $plugin => $data ){
				if( isset( $data['new_version'], $data['slug'], $data['plugin'] ) ){
					$value->response[$plugin] = (object)$data;
				}
				else{
					unset( $value->response[$plugin] );
				}
			}
		}
		return $value;
	}

	/**
	 * Plugin Information
	 */
	public function plugin_info( $res, $action, $args ){

		/* Get list plugin */
		if( 'group' == $this->config['type'] ){
			$list_plugins = $this->get_data( 'list_plugins' );
		}
		else{
			$slug = dirname( $this->config['id'] );
			$list_plugins = array(
				$slug => $this->config['id'],
			);
		}

		/* If in our list, add our data. */
		if( 'plugin_information' == $action && isset( $args->slug ) && array_key_exists( $args->slug, $list_plugins ) ){

			$info = $this->get_data( 'plugin_information', $list_plugins[$args->slug] );

			if( isset( $info['name'], $info['slug'], $info['external'], $info['sections'] ) ){
				$res = (object)$info;
			}
		}
		return $res;
	}

	/**
	 * Get update data from server
	 */
	public function get_data( $action, $plugin = '' ){

		/* Get CP Version */
		global $cp_version;

		/* Remote Options */
		$body = $this->config['post'];
 		if( 'query_plugins' == $action ){
 			$body['plugins'] = get_plugins();
 		}
		elseif( 'plugin_information' == $action ){
			$body['plugin'] =  $plugin;
		}
		$options = array(
			'timeout'    => 20,
			'body'       => $body,
			'user-agent' => 'ClassicPress/' . $cp_version . '; ' . get_bloginfo( 'url' ),
		);

		/* Remote URL */
		$url_args = array(
			'fx_updater'          => $action,
			$this->config['type'] => $this->config['id'],
		);
		$server = set_url_scheme( $this->config['server'], 'http' );
		$url = $http_url = add_query_arg( $url_args, $server );
		if ( $ssl = wp_http_supports( array( 'ssl' ) ) ){
			$url = set_url_scheme( $url, 'https' );
		}

		/* Try HTTPS */
		$raw_response = wp_remote_post( esc_url_raw( $url ), $options );

		/* Fail, try HTTP */
		if ( is_wp_error( $raw_response ) ) {
			$raw_response = wp_remote_post( esc_url_raw( $http_url ), $options );
		}

		/* Still fail, bail. */
		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
			return array();
		}

		/* return array */
		$data = json_decode( trim( wp_remote_retrieve_body( $raw_response ) ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Fix Install Folder
	 */
	public function fix_install_folder( $true, $hook_extra, $result ){
		if ( isset( $hook_extra['plugin'] ) ){
			global $wp_filesystem;
			$proper_destination = trailingslashit( $result['local_destination'] ) . dirname( $hook_extra['plugin'] );
			$wp_filesystem->move( $result['destination'], $proper_destination );
			$result['destination'] = $proper_destination;
			$result['destination_name'] = dirname( $hook_extra['plugin'] );
			global $hook_suffix;
			if( 'update.php' == $hook_suffix && isset( $_GET['action'], $_GET['plugin'] ) && 'upgrade-plugin' == $_GET['action'] && $hook_extra['plugin'] == $_GET['plugin'] ){
				activate_plugin( $hook_extra['plugin'] );
			}
		}
		return $true;
	}

}