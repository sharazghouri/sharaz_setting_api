<?php

/**
 *Class Ss_Structure use to define to strucuter of setting  api
 * @author sharaz
 * @since 1.0.0
 *
 */
class Ss_Structure {

	/**
	 * settings sections array
	 *
	 * @var array
	 */
	protected $settings_sections = array();

	/**
	 * Settings fields array
	 *
	 * @var array
	 */
	protected $settings_fields = array();

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 *
	 * Enqueue scripts and styles
	 * @author sharaz
	 *
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Set settings sections
	 * @author sharaz
	 *
	 * @param array $sections setting sections array
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section
	 * @author sharaz
	 *
	 * @param array $section
	 *
	 * @return  $this
	 */
	public function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields
	 * @author sharaz
	 *
	 * @param array $fields settings fields array
	 *
	 * @return  $this
	 */
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	/**
	 * setting field in section
	 *
	 * @param $section
	 * @param $field
	 *
	 * @return $this
	 */
	public function add_field( $section, $field ) {
		$defaults = array(
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text'
		);

		$arg                                 = wp_parse_args( $field, $defaults );
		$this->settings_fields[ $section ][] = $arg;

		return $this;
	}

	/**
	 * user to register setting section
	 * @author sharaz
	 */
	public function register_setting_section() {

		//register settings sections
		foreach ( $this->settings_sections as $section ) {

			//if the section first time registering add id of the section in option table
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			//if section have any description add desc in $section['desc']
			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['callback']= '<div class="inside">' . $section['desc'] . '</div>';

			}else{
// for setting call back to false[ because create user function deprecate php7.0.2 for dynamic desc we echo formated desc in do_setting section ]
				// see the  d0_setting_section methos of this class
				$section['callback'] = false;
			}


			// register the section in wordpress
			$this->register_wp_setting_section( $section );
		}
	}


	/**
	 * use to register section into wordpress
	 *
	 * @param array $section
	 */
	private function register_wp_setting_section( array $section ) {
		add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );
	}

	/**
	 * use to register setting fields
	 * @author sharaz
	 */
	public function register_setting_fields() {

		//register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $index => $option ) {

				$name     = $option['name'];
				$type     = isset( $option['type'] ) ? $option['type'] : 'text';
				$label    = isset( $option['label'] ) ? $option['label'] : '';
				$callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );
				$page     = isset( $option['page'] ) ? $option['page'] : 'default';
				$args     = array(
					'id'                => $name,
					'class'             => isset( $option['class'] ) ? $option['class'] : $name,
					'label_for'         => "{$section}[{$name}]",
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $label,
					'section'           => $section,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'type'              => $type,
					'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
					'min'               => isset( $option['min'] ) ? $option['min'] : '',
					'max'               => isset( $option['max'] ) ? $option['max'] : '',
					'step'              => isset( $option['step'] ) ? $option['step'] : '',
					'link'              => isset( $option['link'] ) ? $option['link'] : '',
				);



				$this->register_wp_setting_fields( $args, $callback, $page );

			}
		}

	}

	/**
	 * use to register setting fields in wordpress core
	 *
	 * @param array $args
	 * @param $callback
	 * @param $page
	 */
	private function register_wp_setting_fields( array $args, $callback, $page ) {
		add_settings_field( "{$args[ 'section' ]}[ {$args['id' ]} ]", $args['name'], $callback, $page, $args['section'], $args );
	}

	private function register_settings( array $section ) {

		// creates our settings in the options table
            register_setting( $section['page'], $section['id'], array( $this, 'sanitize_options' ) );

	}

	/**
	 * register the section and fields
	 */
	public function admin_init() {

		//register settings section
		$this->register_setting_section();
		//register fields
		$this->register_setting_fields();

		//registering settings final step
		foreach ( $this->settings_sections as $section ) {
			$this->register_settings( $section );

		}
	}


	/**
	 * Get field description for display
	 *
	 * @param array $args settings field args
	 */
	public function get_field_description( $args ) {
		if ( ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
		} else {
			$desc = '';
		}

		return $desc;
	}


	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_text( $args ) {

		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';


		$html = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s"/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
		$html .= $this->get_field_description( $args );
		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_select( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}

		$html .= sprintf( '</select>' );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_checkbox( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

		$html = sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
		$html .= $this->get_field_description( $args );
		echo $html;
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_color( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_wysiwyg( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

		echo '<div style="max-width: ' . $size . ';">';

		$editor_settings = array(
			'teeny'         => true,
			'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
			'textarea_rows' => 10
		);

		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$editor_settings = array_merge( $editor_settings, $args['options'] );
		}

		wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

		echo '</div>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_ss_text( $args ) {

		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

		$html = $this->get_field_description( $args );

		$html .= sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s"/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_ss_select( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html = $this->get_field_description( $args );
		$html  .= sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}

		$html .= sprintf( '</select>' );

		echo $html;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_ss_checkbox( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

		$html = $this->get_field_description( $args );
		$html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
		echo $html;
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_ss_color( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = $this->get_field_description( $args );
		$html .= sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );

		echo $html;
	}

	function show_forms() {
		echo '<div class="">';

		echo '<form method="post" action="options.php">';
		$this->do_settings_sections( 'ss_page' );
		settings_fields( 'ss_page' );
		submit_button();
		echo '</form>';

		echo '</div>';

        echo '<div id="">';
		echo '<form method="post" action="options.php">';
		$this->do_settings_sections( 'ss_page1');
		settings_fields( 'ss_page1' );
		submit_button();
		echo '</form>';
		echo '</div>';


		$this->script();
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return string
	 */
	public function get_option( $option, $section, $default = '' ) {

		$options = get_option( $section );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	/**
	 * Sanitize callback for Settings API
	 *
	 * @return mixed
	 */
	function sanitize_options( $options ) {

		if ( ! $options ) {
			return $options;
		}

		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );

			}

		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
     * get from wp( 4.9.1)
	 * Prints out all settings sections added to a particular settings page
	 *
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 *
	 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
	 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
	 * @since 2.7.0
	 *
	 * @param string $page The slug name of the page whose settings sections you want to output
	 */
	function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;



		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n";
			}
			if ( $section['desc'] ) {
				echo  $section['desc'];
			}



			if ( $section['callback'] ) {

			    echo  $section['callback'];

				//isset( $wp_settings_fields[ $page ][ $section['id'] ] )
            //    call_user_func( $section['callback'], $section );s
			}




			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
            }

			echo '<table class="form-table">';

			$this->do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}


	/**
     * get from wp( 4.9.1)
	 * Print out the settings fields for a particular settings section
	 *
	 * Part of the Settings API. Use this in a settings page to output
	 * a specific section. Should normally be called by do_settings_sections()
	 * rather than directly.
	 *
	 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
	 *
	 * @since 2.7.0
	 *
	 * @param string $page Slug title of the admin page who's settings fields you want to show.
	 * @param string $section Slug title of the settings section who's fields you want to show.
	 */
	public function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;


		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}


		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$class = '';


			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}

			echo "<tr{$class}>";

			if ( ! empty( $field['args']['label_for'] ) ) {

			    echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';

			} else {
				echo '<th scope="row">' . $field['title'] . '</th>';
			}

			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
			echo '</tr>';
		}




	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	 public function script() {
		?>
        <script>
            jQuery(document).ready(function ($) {
                //Initiate Color Picker
                $('.wp-color-picker-field').wpColorPicker();

                // Switches option sections
                $('.group').hide();
                var activetab = '';
                if (typeof(localStorage) != 'undefined') {
                    activetab = localStorage.getItem("activetab");
                }

                //if url has section id as hash then set it as active or override the current local storage value
                if (window.location.hash) {
                    activetab = window.location.hash;
                    if (typeof(localStorage) != 'undefined') {
                        localStorage.setItem("activetab", activetab);
                    }
                }

                $(activetab + '-tab-content').fadeIn();
                if (activetab != '' && $(activetab).length) {
                    $(activetab).fadeIn();
                } else {
                    $('.group:first').fadeIn();
                }
                $('.group .collapsed').each(function () {
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                        function () {
                            if ($(this).hasClass('last')) {
                                $(this).removeClass('hidden');
                                return false;
                            }
                            $(this).filter('.hidden').removeClass('hidden');
                        });
                });

                if (activetab != '' && $(activetab + '-tab').length) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                }
                else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function (evt) {

                    if ('ssb_go_pro-tab' == $(this).attr('id')) {
                        return;
                    }
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href') + '-tab-content';
                    if (typeof(localStorage) != 'undefined') {
                        if ($(this).attr('href').indexOf('#') > -1) {
                            localStorage.setItem("activetab", $(this).attr('href'));
                        }
                    }
                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });

                $('.wpsa-browse').on('click', function (event) {
                    event.preventDefault();

                    var self = $(this);

                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: self.data('uploader_title'),
                        button: {
                            text: self.data('uploader_button_text'),
                        },
                        multiple: false
                    });

                    file_frame.on('select', function () {
                        attachment = file_frame.state().get('selection').first().toJSON();
                        self.prev('.wpsa-url').val(attachment.url).change();
                    });

                    // Finally, open the modal
                    file_frame.open();
                });

                $('#ssb_subscribe_btn').on('click', function (event) {
                    event.preventDefault();
                    var subscriber_mail = $('#ssb_subscribe_mail').val();
                    var name = $('#ssb_subscribe_name').val();
                    if (!subscriber_mail) {
                        $('.ssb_subscribe_warning').html('Please Enter Email');
                        return;
                    }
                    $.ajax({
                        url: 'https://wpbrigade.com/wp-json/wpbrigade/v1/subsribe-to-mailchimp',
                        type: 'POST',
                        data: {
                            subscriber_mail: subscriber_mail,
                            name: name,
                            plugin_name: 'ssb'
                        },
                        beforeSend: function () {
                            $('.ssb_subscribe_loader').show();
                            $('#ssb_subscribe_btn').attr('disabled', 'disabled');
                        }
                    })
                        .done(function (res) {
                            $('.ssb_return_message').html(res);
                            $('.ssb_subscribe_loader').hide();
                        });
                });
                $('.simplesocialbuttons-style').on('click', function () {
                    var el = $(this);
                    $(this).addClass('social-active').parent().siblings().find('.simplesocialbuttons-style').removeClass('social-active');
                    $(this).find('input[type="radio"]').prop('checked', true);
                });
                $('.simplesocial-postion-box').on('click', function () {
                    var el = $(this);
                    var target = $(this).children('input[type="checkbox"]').val();
                    if ($(this).children('input[type="checkbox"]').is(':checked')) {
                        $(this).addClass('social-active');
                        $('#ssb_' + target).fadeIn();
                    } else {
                        $(this).removeClass('social-active');
                        $('#ssb_' + target).fadeOut();
                    }
                    $(this).find('.shadow').addClass('animated');
                    setTimeout(function () {
                        el.find('.shadow').removeClass('animated');
                    }, 400);
                });
                $('.simplesocial-postion-box').each(function () {
                    var el = $(this);
                    var target = $(this).children('input[type="checkbox"]').val();
                    if ($(this).children('input[type="checkbox"]').is(':checked')) {
                        $(this).addClass('social-active');
                        $('#ssb_' + target).fadeIn();
                    } else {
                        $(this).removeClass('social-active');
                        $('#ssb_' + target).fadeOut();
                    }
                });
                $('.simplesocial-inline-form-section label').on('click', function () {
                    var el = $(this);
                    $(this).find('.shadow').addClass('animated');
                    setTimeout(function () {
                        el.find('.shadow').removeClass('animated');
                    }, 400);
                });
                $('.simpleshare-acordions h3').on('click', function () {
                    $(this).toggleClass('simpleshare-active');
                    $(this).next('.postbox-content').slideToggle();
                });
                $('.ssb_select').each(function () {

                    // Cache the number of options
                    var $this = $(this),
                        numberOfOptions = $(this).children('option').length;

                    // Hides the select element
                    $this.addClass('s-hidden');

                    // Wrap the select element in a div
                    $this.wrap('<div class="select"></div>');

                    // Insert a styled div to sit over the top of the hidden select element
                    $this.after('<div class="styledSelect"></div>');

                    // Cache the styled div
                    var $styledSelect = $this.next('div.styledSelect');
                    var getHTML = $this.children('option[value="' + $this.val() + '"]').text();
                    // Show the first select option in the styled div
                    $styledSelect.text(getHTML);

                    // Insert an unordered list after the styled div and also cache the list
                    var $list = $('<ul />', {
                        'class': 'options'
                    }).insertAfter($styledSelect);

                    // Insert a list item into the unordered list for each select option
                    for (var i = 0; i < numberOfOptions; i++) {
                        $('<li />', {
                            text: $this.children('option').eq(i).text(),
                            rel: $this.children('option').eq(i).val()
                        }).appendTo($list);
                    }

                    // Cache the list items
                    var $listItems = $list.children('li');

                    // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
                    $styledSelect.click(function (e) {

                        // $(this).addClass('active').next('ul.options').slideDown();
                        if ($(this).hasClass('active')) {
                            $(this).removeClass('active').next('ul.options').slideUp();
                        } else {
                            $('div.styledSelect.active').each(function () {
                                $(this).removeClass('active').next('ul.options').slideUp();
                            });
                            $(this).addClass('active').next('ul.options').slideDown();
                        }
                        e.stopPropagation();
                    });

                    // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
                    // Updates the select element to have the value of the equivalent option
                    $listItems.click(function (e) {
                        e.stopPropagation();
                        $styledSelect.text($(this).text()).removeClass('active');
                        var value = $(this).attr('rel').toString();
                        $($this).val(value);
                        $($this).trigger('change');
                        $list.slideUp();
                        /* alert($this.val()); Uncomment this for demonstration! */
                    });

                    // Hides the unordered list when clicking outside of it
                    $(document).click(function () {
                        $styledSelect.removeClass('active');
                        $list.slideUp();
                    });

                });
            });
        </script>
		<?php

	}

}