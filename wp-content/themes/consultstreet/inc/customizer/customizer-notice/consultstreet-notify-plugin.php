<?php

/*
 *  Customizer Notifications
 */

require get_template_directory() . '/inc/customizer/customizer-notice/consultstreet-customizer-notify.php';

$consultstreet_config_customizer = array(
    'recommended_plugins' => array( 
        'arile-extra' => array(
            'recommended' => true,
            'description' => sprintf( 
                /* translators: %s: plugin name */
                esc_html__( 'If you want to show all the features and business sections of the FrontPage. please install and activate %s plugin', 'consultstreet' ), '<strong>Arile Extra</strong>' 
            ),
        ),
    ),
	'recommended_actions'       => array(),
	'recommended_actions_title' => esc_html__( 'Recommended Actions', 'consultstreet' ),
	'recommended_plugins_title' => esc_html__( 'Recommended Plugin', 'consultstreet' ),
	'install_button_label'      => esc_html__( 'Install and Activate', 'consultstreet' ),
	'activate_button_label'     => esc_html__( 'Activate', 'consultstreet' ),
	'consultstreet_deactivate_button_label'   => esc_html__( 'Deactivate', 'consultstreet' ),
);
ConsultStreet_Customizer_Notify::init( apply_filters( 'consultstreet_customizer_notify_array', $consultstreet_config_customizer ) );