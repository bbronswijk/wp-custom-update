<?php
/*
 Plugin Name: Custom Wordpress Updater
 Plugin URI: https://github.com/bbronswijk/github-updater
 Description: This plugin allows WordPress to update plugins and themes directly from gitlab or github.
 Author: B. Bronswijk, LYCEO
 Version: 2.0
 */

$githubUpdatePlugin = new GithubUpdatePlugin();

// require the update class
require_once 'wp_custom_update.php';


class GithubUpdatePlugin
{
	// get accessed by the WP_CustomUpdate class
	public $setting_page  = 'custom_updater_settings';
	public $setting_section = 'update_access_token_section';
	public $option_group  = 'updater_token_group';

	function __construct()
	{
		add_action( 'upgrader_process_complete', array( $this, 'rename_plugin_dir' ), 10, 2  );
		add_action( 'upgrader_process_complete', array( $this, 'rename_theme_dir' ), 10, 2  );
		add_action( 'admin_menu', array( $this, 'create_admin_page' ) );
		add_action( 'admin_init', array( $this, 'add_setting_section' ) );
	}


	function rename_plugin_dir ( $upgrader_object, $data ) {
		// get the data of the updated plugins
		$updated_plugins = $data['plugins'];

		if ( !empty( $updated_plugins ) ) {
			foreach ( $updated_plugins as $path ) {
				$path_parts       = explode( '/', $path );
				$plugin_directory = $path_parts[0];

				// loop through plugin directories and look for the current updated plugin folder
				$dirs = glob( ABSPATH . 'wp-content/plugins/*' );
				foreach ( $dirs as $dir ) {
					// check if this is the folder we need
					if ( is_dir( $dir ) && strpos( $dir, '-master' ) !== false && strpos( $dir, $plugin_directory ) ) {

						//explode the directory path
						$parts = explode( '-master', $dir );

						// rename the directory and use only the part before the -master part
						rename( $dir, $parts[0] );
					}
				}
			}
		}
	}

	function rename_theme_dir($upgrader_object, $data )
	{
		// get the data of the updated plugins
		$updated_themes = $data['themes'];

		if( !empty($updated_themes) ) {

			foreach ( $updated_themes as $path ) {

				$path_parts      = explode( '/', $path );
				$theme_directory = $path_parts[0];

				// loop through plugin directories and look for the current updated plugin folder
				$dirs = glob( ABSPATH . 'wp-content/themes/*' );
				foreach ( $dirs as $dir ) {
					// check if this is the folder we need
					if ( is_dir( $dir ) && strpos( $dir, '-master' ) !== false && strpos( $dir, $theme_directory ) ) {

						//explode the directory path
						$parts = explode( '-master', $dir );

						// rename the directory and use only the part before the -master part
						rename( $dir, $parts[0] );
					}
				}
			}
		}
	}


	public function create_admin_page()
	{
		add_options_page(
			'Custom Updater', // title
			__('Update settings'), // menu item
			'publish_posts',
			$this->setting_page,
			array($this, 'admin_token_page')
		);
	}

	function admin_token_page()
	{
		if ( !current_user_can( 'publish_posts' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

		require_once 'admin_page.php';
	}

	public function section_description_html()
	{
		echo '<p>Voer hieronder uw accestokens in voor de themes & plugin die gebruik maken van de Custom Update Plugin</p>';
	}

	public function add_setting_section()
	{
		add_settings_section(
			$this->setting_section, // section id
			false, // section title
			array($this, 'section_description_html'), // hmtl callback
			$this->setting_page // setting page id
		);
	}

}




