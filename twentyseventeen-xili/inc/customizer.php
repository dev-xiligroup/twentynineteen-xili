<?php
/**
 * Twenty Seventeen xili Customizer functionality
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since
 */

/**
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function twentyseventeen_xili_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'texts_section' , array(
					'title'       => __( 'Other texts section', 'twentyseventeen' ),
					'priority'    => 421
	));

	$wp_customize->add_setting( 'copyright', array(
		'sanitize_callback' => 'twentyseventeenx_sanitize_text',
		'default' => __('My company','twentyseventeen'),
		'transport' => 'postMessage',
		) );

	$wp_customize->add_control( 'copyright', array(
				'label'    => __( 'Your copyright (footer)', 'twentyseventeen' ),
				'section'  => 'texts_section',
				'settings' => 'copyright',
				'priority'    => 1,
		) );

}
add_action( 'customize_register', 'twentyseventeen_xili_customize_register', 12 ); // after parent


function twentyseventeenx_sanitize_text( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}

/**
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since 1.2.0
 */
function twentyseventeen_xili_customize_preview_js() {
	wp_enqueue_script( 'twentyseventeen-xili-customize-preview', get_template_directory_uri() . '/assets/js/customize-preview.js', array( 'customize-preview' ), '20170824', true );
}
add_action( 'customize_preview_init', 'twentyseventeen_xili_customize_preview_js' );
?>