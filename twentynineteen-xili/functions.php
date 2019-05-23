<?php
// dev.xiligroup.com - msc - 2019-05-10 - first update for 1.4
// dev.xiligroup.com - msc - 2019-04-16 - first update for 1.3
// dev.xiligroup.com - msc - 2018-11-07 - first implementation

define( 'TWENTYNINETEEN_XILI_VER', '1.4' ); // as parent style.css

function twentynineteen_xilidev_setup() {

	$theme_domain = 'twentynineteen';

	$minimum_xl_version = '2.23.0'; // >

	$xl_required_version = false;

	load_theme_textdomain( $theme_domain, get_stylesheet_directory() . '/langs' ); // now use .mo of child

	if ( class_exists( 'xili_language' ) ) { // if temporary disabled

		$xl_required_version = version_compare( XILILANGUAGE_VER, $minimum_xl_version, '>' );

		global $xili_language;

		$xili_language_includes_folder = $xili_language->plugin_path . 'xili-includes';

		$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili';

		if ( file_exists( $xili_functionsfolder . '/multilingual-functions.php' ) ) {
			require_once( $xili_functionsfolder . '/multilingual-functions.php' );
		}

		global $xili_language_theme_options; // used on both side
		// Args dedicated to this theme named Twenty NineTeen
		$xili_args = array(
			'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
			'settings_name' => 'xili_2019_theme_options', // name of array saved in options table
			'theme_name' => 'Twenty Nineteen',
			'theme_domain' => $theme_domain,
			'child_version' => TWENTYNINETEEN_XILI_VER,
		);

		add_action( 'widgets_init', 'twentynineteen_xili_add_widgets' );

		// new in WP 4.1 - now in XL 2.17.1
		if ( ! has_filter( 'get_the_archive_description', array( $xili_language, 'get_the_archive_description' ) ) ) {
			add_filter( 'get_the_archive_description', 'xili_get_the_archive_description' );
		}

		if ( is_admin() ) {

			// Admin args dedicaced to this theme

			$xili_admin_args = array_merge(
				$xili_args,
				array(
					'customize_adds' => true, // add settings in customize page
					'customize_addmenu' => false,
					'capability' => 'edit_theme_options',
					'authoring_options_admin' => false,
				)
			);

			if ( class_exists( 'Xili_Language_Theme_Options_Admin' ) ) {
				$xili_language_theme_options = new Xili_Language_Theme_Options_Admin( $xili_admin_args );
				$class_ok = true;
			} else {
				$class_ok = false;
			}
		} else { // visitors side - frontend

			if ( class_exists( 'Xili_Language_Theme_Options' ) ) {
				$xili_language_theme_options = new Xili_Language_Theme_Options( $xili_args );
				$class_ok = true;
			} else {
				$class_ok = false;
			}
		}
		// new ways to add parameters in authoring propagation
		add_theme_support(
			'xiliml-authoring-rules',
			array(
				'post_format' => array(
					'default' => '1',
					'data' => 'attribute',
					'hidden' => '1',
					'name' => 'Post Format',
					/* translators: added in child functions by xili */
					'description' => __( 'Will copy post_format in the future translated posts', 'twentynineteen' ),
				),
				'post_content' => array(
					'default' => '',
					'data' => 'post',
					'hidden' => '',
					'name' => 'Post Content',
					/* translators: added in child functions by xili */
					'description' => __( 'Will copy content in the future translated post', 'twentynineteen' ),
				),
				'post_parent' => array(
					'default' => '1', // (checked)
					'data' => 'post',
					'name' => 'Post Parent',
					'hidden' => '', // checkbox not visible in dashboard UI
					/* translators: added in child functions by xili */
					'description' => __( 'Will copy translated parent id (if original has parent and translated parent)!', 'twentynineteen' ),
				),
			)
		); //

		if ( $class_ok ) {
			$xili_theme_options = get_theme_xili_options();
			// to collect checked value in xili-options of theme
			if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php' ) && $xili_language->is_permalink && isset( $xili_theme_options['perma_ok'] ) && $xili_theme_options['perma_ok'] ) {
				require_once( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
			}
			if ( $xl_required_version ) { // msg choice is inside class
				$msg = $xili_language_theme_options->child_installation_msg( $xl_required_version, $minimum_xl_version, $class_ok );
			} else {
				$msg = '
				<div class="error">' .
					/* translators: added in child functions by xili */
					'<p>' . sprintf( __( 'The %1$s child theme requires xili_language version more recent than %2$s installed', 'twentynineteen' ), get_option( 'current_theme' ), $minimum_xl_version ) . '</p>
				</div>';

			}
		} else {

			$msg = '
			<div class="error">' .
				/* translators: added in child functions by xili */
				'<p>' . sprintf( __( 'The %s child theme requires xili_language_theme_options class installed and activated', 'twentynineteen' ), get_option( 'current_theme' ) ) . '</p>
			</div>';

		}
	} else {

		$msg = '
		<div class="error">' .
			/* translators: added in child functions by xili */
			'<p>' . sprintf( __( 'The %s child theme requires xili-language plugin installed and activated', 'twentynineteen' ), get_option( 'current_theme' ) ) . '</p>
		</div>';

	}

	// errors and installation informations
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) ) {
		// replace createfunction - obsolete in php 7.2
		add_action(
			'admin_notices',
			function() use ( &$msg ) {
				echo $msg;
			}
		);
	}

	// end errors...
	add_filter( 'pre_option_link_manager_enabled', '__return_true' ); // comment this line if you don't want links/bookmarks features

	//remove_filter( 'walker_nav_menu_start_el', 'twentynineteen_nav_description');
	add_filter( 'twentynineteen_default_hue', 'twentynineteen_xili_default_hue' );
	add_filter( 'twentynineteen_default_saturation', 'twentynineteen_xili_default_saturation' );
	add_filter( 'twentynineteen_default_lightness', 'twentynineteen_xili_default_lightness' );

	add_filter( 'twentynineteen_custom_colors_css', 'twentynineteen_xili_custom_colors_css', 10, 3 );
}

function twentynineteen_xili_custom_colors_css( $theme_css, $primary_color, $saturation ) {
	return $theme_css;
}

add_action( 'after_setup_theme', 'twentynineteen_xilidev_setup', 11 ); // called after parent

// Enable shortcodes in text widgets
add_filter( 'widget_text', 'do_shortcode' );

/**
 * define when search form is completed by radio buttons to sub-select language when searching
 *
 */
function special_head() {
	if ( class_exists( 'xili_language' ) ) { // if temporary disabled
		// to change search form of widget
		// if ( is_front_page() || is_category() || is_search() )
		if ( is_front_page() || is_search() || is_404() ) {
			add_filter( 'get_search_form', 'my_langs_in_search_form_2019', 10, 1 ); // here below
		}
	}
}
add_action( 'wp_head', 'special_head', 11 );

/**
 * add search other languages in form - see functions.php when fired
 *
 */
function my_langs_in_search_form_2019( $the_form ) {
	global $xili_language;

	if ( $xili_language->multiple_lang ) {
		$form = str_replace( '</form>', '', $the_form ) . do_shortcode( "[xili-multiple-lang-selector option='list']" );
	} else {
		$form = str_replace( '</form>', '', $the_form ) . '<span class="xili-s-radio">' . xiliml_langinsearchform( '<span class="radio-lang">', '</span>', false ) . '</span>';
	}
	$form .= '</form>';

	// if multiple language is set

	//if ( $xili_language->multiple_lang )
		//$form .=  do_shortcode( "[xili-multiple-lang-selector button-class='search-submit']");

	return $form;
}


if ( class_exists( 'xili_language' ) ) { // if temporary disabled
	add_action( 'after_setup_theme', 'theme_mod_create_array', 11, 1 );

	function theme_mod_create_array() {
		global $xili_language;
		if ( method_exists( $xili_language, 'set_theme_mod_to_be_filtered' ) ) {
			$xili_language->set_theme_mod_to_be_filtered( 'copyright' ); // used in footer
		}
	}
}

function twentynineteen_xili_add_widgets() {
	if ( class_exists( 'Xili_Widget_Categories' ) ) {
		register_widget( 'Xili_Widget_Categories' ); // in xili-language-widgets.php since 2.16.3
	}
}

function twentynineteen_xili_header_image() {

	$header_image_url = get_header_image();

	$text_color = get_header_textcolor();

	// If we get this far, we have custom styles.

	if ( $header_image_url ) {

			/**
			 * Filter the default twentynineteen custom header sizes attribute.
			 *
			 * @since Twenty Nineteen 1.0
			 *
			 * @param string $custom_header_sizes sizes attribute
			 * for Custom Header. Default '(max-width: 709px) 85vw,
			 * (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px'.
			 */
			$custom_header_sizes = apply_filters( 'twentynineteen_custom_header_sizes', '(max-width: 709px) 85vw, (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px' );

			$header_image_width = get_custom_header()->width; // default values
			$header_image_height = get_custom_header()->height;
		$image_srcset_id = ( isset( get_custom_header()->attachment_id ) ) ? get_custom_header()->attachment_id : 0;
		$srcset = ''; // no in default
		if ( class_exists( 'xili_language' ) ) {
			$xili_theme_options = get_theme_xili_options();
			if ( isset( $xili_theme_options['xl_header'] ) && $xili_theme_options['xl_header'] ) {
				global $xili_language, $xili_language_theme_options;
				// check if image exists in current language
				// 2013-10-10 - Tiago suggestion
				$curlangslug = ( '' == the_curlang() ) ? strtolower( $xili_language->default_lang ) : the_curlang();

					$headers = get_uploaded_header_images(); // search in uploaded header list - index = ID

					$this_default_headers = $xili_language_theme_options->get_processed_default_headers();
				if ( ! empty( $this_default_headers ) ) {

					foreach ( $this_default_headers as $header_key => $header ) {
						$headers[ $header_key ] = $header; // add string indexes to first id indexes 2.22
					}

						//$headers = array_merge( $this_default_headers, $headers );
				}
				foreach ( $headers as $header_key => $header ) {

					if ( isset( $xili_theme_options['xl_header_list'][ $curlangslug ] ) && $header_key == $xili_theme_options['xl_header_list'][ $curlangslug ] ) {
						$header_image_url = $header['url'];

						$header_image_width = ( isset( $header['width'] ) ) ? $header['width'] : get_custom_header()->width;
						$header_image_height = ( isset( $header['height'] ) ) ? $header['height'] : get_custom_header()->height; // not in default (but in uploaded)
						$image_srcset_id = ( isset( $header['attachment_id'] ) ) ? $header['attachment_id'] : $image_srcset_id;
						$srcset = ( $image_srcset_id ) ? 'srcset="' . esc_attr( wp_get_attachment_image_srcset( $image_srcset_id ) ) . '" ' : '';
						break;
					}
				}
			}
		}
	?>

	<div class="header-image">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo $header_image_url; ?>" <?php echo $srcset; ?>" sizes="<?php echo esc_attr( $custom_header_sizes ); ?>" width="<?php echo esc_attr( $header_image_width ); ?>" height="<?php echo esc_attr( $header_image_height ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
		</a>
	</div>

	<?php
	}
}

function twentynineteen_xilidev_setup_custom_header() {

	// %2$s = in child
	//

	register_default_headers(
		array(
			'xili2019' => array(
				'url' => '%2$s/assets/images/headers/xili2019-h1.jpg',
				'thumbnail_url' => '%2$s/assets/images/headers/xili2019-h1-thumb.jpg',
				/* translators: added in child functions by xili */
				'description' => _x( '2019 by xili', 'header image description', 'twentynineteen' ),
			),
			'xili2019-2' => array(
				'url' => '%2$s/assets/images/headers/xili2019-h2.jpg',
				'thumbnail_url' => '%2$s/assets/images/headers/xili2019-h2-thumb.jpg',
				/* translators: added in child functions by xili */
				'description' => _x( '2019.2 by xili', 'header image description', 'twentynineteen' ),
			),
		)
	);

	$args = array(
		// Text color and image (empty to use none).
		'default-text-color' => 'fffff0', // diff of parent
		'default-image' => '%2$s/assets/images/headers/xili2019-h1.jpg',

		// Set height and width, with a maximum value for the width.
		'height' => 280,
		'width' => 1200,
	);

	add_theme_support( 'custom-header', $args ); // need 8 in add_action to overhide parent

}
// not used by 2019
//add_action( 'after_setup_theme', 'twentynineteen_xilidev_setup_custom_header', 12 ); // 12 - child translation is active


function twentynineteen_xili_credits() {
	/* translators: added in child functions by xili */
	printf(
		/* translators: */
		esc_html__( 'Multilingual child theme of Twenty Nineteen by %1$s and %2$s', 'twentynineteen' ),
		'<a href="http://dev.xiligroup.com">dev.xiligroup</a>',
		'<span class="site-copyright">' . wp_kses(
			get_theme_mod(
				'copyright',
				esc_html__(
					'My company',
					'twentynineteen'
				)
			),
			array(
				'br' => array(),
				'em' => array(),
			)
		) . '</span>'
	);
}
add_action( 'twentynineteen_xili_credits', 'twentynineteen_xili_credits' );


// testing
function twentynineteen_xili_default_hue( $value ) {
	return $value; // 184;
}

function twentynineteen_xili_default_saturation( $value ) {
	return $value; // 100;
}

function twentynineteen_xili_default_lightness( $value ) {
	return $value; // 200;
}



// Admin side
// example with theme_mod_copyright in customizer (filter in xl 2.18.2)

/**
 * Customizer additions.
 *
 * @since
 */
require get_stylesheet_directory() . '/inc/customizer.php';
