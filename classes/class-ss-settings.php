<?php

/**
 *Class Ss_Setting use to work on setting api
 * @author sharaz
 * @since 1.0.0
 *
 */
class Ss_Settings {

	/**
	 *   setting api will contain the opject of the setting api
	 */
	private $setting_api;

	function __construct() {

		include_once SS_PLUGIN_DIR . '/classes/class-ss-structure.php';

		$this->setting_api = new Ss_Structure();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	/**
	 * register all  menu for the plugin
	 * @author sharaz
	 * @since  1.0.0
	 */
	public function admin_menu() {
		add_menu_page( 'Sharaz setting', 'sharaz setting', 'manage_options', 'ss_page', array(
			$this,
			'plugin_page'
		), 'edit', 75 );
	}

	/**
	 * call back of the main menu of the plugin
	 * @author sharaz
	 * @since  1.0.0
	 */
	public function plugin_page() {
		echo 'hello';
	}

	//sorting the array
	public function sort_array( $a, $b ) {
		return $a['priority'] - $b['priority'];
	}

	/**
	 * set the section of your setting
	 * @autor sharaz
	 *
	 * @return mixed
	 */
	public function get_settings_sections() {
		$sections = array(
			array(
				'id'       => 'test_1',
				'title'    => __( 'Easy Setting Test', 'sharaz-settings' ),
				'priority' => '10',
			),
			array(
				'id'       => 'test_2',
				'title'    => __( 'Easy Setting Test2', 'sharaz_settings' ),
				'priority' => '15',
			),

		);

		//  use to filter the section any point
		$setting_section = apply_filters( 'ss_section', $sections );

		// sort the array parity wise
		usort( $setting_section, array( $this, 'sort_array' ) );

		return $setting_section;
	}


	/**
	 * setting the fields of the sections
	 * @sharaz
	 * @return array
	 */
	public function get_settings_fields() {


		/*
		 * name     ( name of the field)
		 * type     ( type of the field which help to call call class back)
		 * label    ( [optional] if you want to display any think the description )
		 * default  ( [optional] default  values)
		 * options  ( [optional]  options we use when type is select mostly but this use to give option to fields)
		 * link     ( [optional]   in case you send a  link to field)
		 * sanitize_callback ( [optional] sanitize call back of the field)
		 * priority   [ [optional] use to  giving parity  to the user]
		 *
		 *
		 * */


		// setting files index key is ( section => array( fields ))
		$settings_fields = array(
			'test_1' => array(
				array(
					'name'              => 'name',
					'label'             => __( 'Enter the name', 'sharaz_settings' ),
					'desc'              => __( '<h4>Sharaz Setting Api</h4>if you like please share to others', 'sharaz_settings' ),
					'type'              => 'ss_text',
					'sanitize_callback' => 'sanitize_text_field',
					'priority'          => '5',
				),
				array(
					'name'     => 'salary',
					'label'    => 'Select your salary range',
					'type'     => 'ss_select',
					'default'  => '2000',
					'options'  => array(
						'1000' => '1000',
						'2000' => '2000',
						'3000' => '3000',
					),
					'priority' => '10'
				),
				array(
					'name'              => 'advance_bonus',
					'label'             => __( 'Receving advance bonus', 'sharaz_settings' ),
					'type'              => 'ss_checkbox',
					'sanitize_callback' => 'sanitize_text_field',
					'priority'          => '15',
				),
				array(
					'name'     => 'your_color',
					'label'    => __( 'Your Setting', 'sharaz-setting' ),
					'type'     => 'ssb_color',
					'default'  => '',
					'priority' => '20',
				)
			),
			'test_2' => array(

				array(
					array(
						'name'              => 'suggestion',
						'label'             => __( 'Ang suggestion', 'sharaz_settings' ),
						'desc'              => __( '<h4>Please type some</h4>if you like suggest some thing to us', 'sharaz_settings' ),
						'type'              => 'ss_text',
						'sanitize_callback' => 'sanitize_text_field',
						'priority'          => '5',
					),
				)

			)
		);

		return $settings_fields;
	}
}


new Ss_Settings();