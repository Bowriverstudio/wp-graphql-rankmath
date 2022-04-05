<?php
/**
 * Plugin Name: WP GraphQL RankMath
 * Plugin URI: https://github.com/Bowriverstudio/wp-graphql-clarity
 * GitHub Plugin URI: https://github.com/Bowriverstudio/wp-graphql-clarity
 * Description: GraphQL API for Rankmath
 * Author: Maurice Tadros
 * Author URI: http://www.bowriverstudio.com
 * Version: 1.0.1
 * Text Domain: wp-graphql-rankmath
 * Domain Path: /languages/
 * Requires PHP: 7.1
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use WPGraphQL\AppContext;
/**
 * Ensures core dependencies are active
 *
 * @see https://github.com/ashhitch/wp-graphql-yoast-seo/blob/master/wp-graphql-yoast-seo.php
 */
add_action(
	'admin_init',
	function () {

		$options = get_option( 'active_plugins' );

		$core_dependencies = array(
			'WPGraphQL plugin'             => class_exists( 'WPGraphQL' ),
			'Rank Math'              => in_array( 'microsoft-clarity/clarity.php', $options ),
		);

		$missing_dependencies = array_keys(
			array_diff( $core_dependencies, array_filter( $core_dependencies ) )
		);
		$display_admin_notice = static function () use ( $missing_dependencies ) {
			?>
			<div class="notice notice-error">
			  <p>
			  <?php
				esc_html_e(
					'The WPGraphQL Site Kite plugin can\'t be loaded because these dependencies are missing:',
					'wp-graphql-site-kit'
				);
				?>
			  </p>
			  <ul>
				<?php foreach ( $missing_dependencies as $missing_dependency ) : ?>
				  <li><?php echo esc_html( $missing_dependency ); ?></li>
				<?php endforeach; ?>
			  </ul>
			</div>
				<?php
		};

		if ( ! empty( $missing_dependencies ) ) {
			add_action( 'network_admin_notices', $display_admin_notice );
			add_action( 'admin_notices', $display_admin_notice );

			return;
		}
	}
);


add_action(
	'graphql_register_types',
	function() {

		register_graphql_object_type(
			'RankMathHours',
			array(
				'fields' => array(
					'day' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Day', 'wp-graphql-clarity' ),
					),
					'time' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Like: 09:00-19:00', 'wp-graphql-clarity' ),
					),
				),
			)
		);

		register_graphql_object_type(
			'RankMathLocalAddress',
			array(
				'fields' => array(
					'streetAddress' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'streetAddress', 'wp-graphql-clarity' ),
					),
					'addressLocality' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'addressLocality or City', 'wp-graphql-clarity' ),
					),
					'addressRegion' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'addressRegion', 'wp-graphql-clarity' ),
					),
					'postalCode' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'postalCode or City', 'wp-graphql-clarity' ),
					),
					'addressCountry' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'addressCountry or City', 'wp-graphql-clarity' ),
					),
				),
			)
		);

		register_graphql_object_type(
			'RankMathTitles',
			array(
				'description' => __( 'RankMath Titles ', 'wp-graphql-clarity' ),

				'fields' => array(
					'opening_hours' => array(
						'type'        => array( 'non_null' => [ 'list_of' => 'RankMathHours' ] ),
						'description' => __( 'Settings for Analytics', 'wp-graphql-clarity' ),
					),
					'local_address' => array(
						'type'        => array( 'non_null' => 'RankMathLocalAddress' ),
						'description' => __( 'local_address', 'wp-graphql-clarity' ),
					),
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'RankMathTitles',
			array(
				'type'        => 'RankMathTitles',
				'description' => __( 'Data for Clarity', 'wp-graphql-clarity' ),
				'args'        => array(),
				'resolve'     => function( $root, $args, $context, $info ) {
					// Rankmath stores titles in  option_name rank-math-options-titles
					// SELECT * FROM wp_options WHERE option_name="rank-math-options-titles"
					$fields = array( 'opening_hours',  'local_address' );
					$values = array();
					foreach( $fields as $field ){
						$values[$field] = Helper::get_settings( "titles.$field" );
					}
				
					return $values;
				},
			)
		);

	}
);
