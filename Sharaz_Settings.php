<?php
/*
Plugin Name: Sharaz Settings
* Version: 1.0.0
* Author: Sharaz Ghouri
*/


/**
 *Class Sharaz_Settings
 * @author sharaz
 * @version 1.0.0
 * @since 1.0.0
 *
 */
class Sharaz_Settings {

	function __construct() {
		$this->constant();
		$this->includes();
	}

	/**
	 * use to define constant
	 * @author sharaz
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function constant() {

		define( 'SS_VERSION', $this->pluginVersion );
		define( 'SS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	}

	/**
	 * include all plugin required files
	 * @author sharaz
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function includes() {

		include_once SS_PLUGIN_DIR . '/classes/class-ss-settings.php';
	}


}


$object = new  Sharaz_Settings();