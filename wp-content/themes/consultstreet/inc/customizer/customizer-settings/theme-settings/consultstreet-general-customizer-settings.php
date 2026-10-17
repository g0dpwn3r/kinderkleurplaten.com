<?php
/**
 * Page Header Settings.
 *
 * @package consultstreet
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
* Page Header Settings.
*/

if ( ! class_exists( 'ConsultStreet_Customize_General_Option' ) ) :

	class ConsultStreet_Customize_General_Option extends ConsultStreet_Customize_Base_Option {

		public function elements() {

			return array(
			
			
			    'consultstreet_page_header_heading' => array(
					'setting' => array(),
					'control' => array(
						'type'    => 'heading',
						'priority'        => 1,
						'label'   => esc_html__( 'General Settings', 'consultstreet' ),
						'section' => 'consultstreet_theme_general',
					),
				),
			
					'consultstreet_animation_disabled'            => array(
						'setting' => array(
							'default'           => true,
							'sanitize_callback' => array( 'ConsultStreet_Customizer_Sanitize', 'sanitize_checkbox' ),
						),
						'control' => array(
							'type'     => 'toggle',
							'priority' => 2,
							'label'    => esc_html__( 'Site Animation Enable/Disable', 'consultstreet' ),
							'section'  => 'consultstreet_theme_general',
						),
					),
			  

			);

		}

	}

	new ConsultStreet_Customize_General_Option();

endif;
