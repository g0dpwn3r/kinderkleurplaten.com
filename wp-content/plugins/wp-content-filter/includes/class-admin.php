<?php
/**
 * Shared WPGO admin navigation and getting-started pages.
 *
 * @package WPGO_Content_Censor
 */

namespace WPGO\Content_Censor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Provides read-only Home pages and privacy-safe support diagnostics. */
final class Admin {
	/** Parent menu and activation destination. */
	const HOME_SLUG = 'content-censor-wpgoplugins';

	/**
	 * Register admin-only hooks.
	 *
	 * @return void
	 */
	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
		add_action( 'admin_menu', array( __CLASS__, 'extra_pages' ), 11 );
		add_action( 'admin_menu', array( __CLASS__, 'order_menu' ), PHP_INT_MAX );
		add_action( 'admin_init', array( __CLASS__, 'legacy_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Register the parent and first submenu.
	 *
	 * @return void
	 */
	public static function menu() {
		add_menu_page( __( 'Content Censor Home', 'wp-content-filter' ), __( 'Content Censor', 'wp-content-filter' ), 'manage_options', self::HOME_SLUG, array( __CLASS__, 'home' ), 'dashicons-shield', 58 );
		add_submenu_page( self::HOME_SLUG, __( 'Content Censor Home', 'wp-content-filter' ), __( 'Home', 'wp-content-filter' ), 'manage_options', self::HOME_SLUG, array( __CLASS__, 'home' ) );
	}

	/**
	 * Register the remaining read-only pages.
	 *
	 * @return void
	 */
	public static function extra_pages() {
		add_submenu_page( self::HOME_SLUG, __( 'Content Censor New Features', 'wp-content-filter' ), __( 'New Features', 'wp-content-filter' ), 'manage_options', self::HOME_SLUG . '-new-features', array( __CLASS__, 'new_features' ) );
		if ( ! self::is_pro() ) {
			add_submenu_page( self::HOME_SLUG, __( 'Support Forum', 'wp-content-filter' ), __( 'Support Forum', 'wp-content-filter' ), 'manage_options', 'https://wordpress.org/support/plugin/wp-content-filter/' );
		}
	}

	/**
	 * Order owned and SDK links after Freemius has registered its submenus.
	 *
	 * @return void
	 */
	public static function order_menu() {
		global $submenu;
		if ( ! isset( $submenu[ self::HOME_SLUG ] ) ) {
			return;
		}
		$remaining = $submenu[ self::HOME_SLUG ];
		$ordered   = array();
		$slugs     = array( self::HOME_SLUG, Settings::PAGE_SLUG, self::HOME_SLUG . '-new-features', self::HOME_SLUG . '-account', self::HOME_SLUG . '-contact', 'https://wordpress.org/support/plugin/wp-content-filter/', self::HOME_SLUG . '-pricing' );
		foreach ( $slugs as $slug ) {
			foreach ( $remaining as $key => $item ) {
				if ( isset( $item[2] ) && untrailingslashit( $slug ) === untrailingslashit( $item[2] ) ) {
					$ordered[] = $item;
					unset( $remaining[ $key ] );
				}
			}
		}
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Reorder only this plugin's registered links, retaining every other item.
		$submenu[ self::HOME_SLUG ] = array_merge( $ordered, array_values( $remaining ) );
	}

	/**
	 * Preserve the former Settings-menu bookmark, including scanner tabs.
	 *
	 * @return void
	 */
	public static function legacy_redirect() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'options-general.php' !== $pagenow || self::HOME_SLUG !== $page || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation; tabs are allowlisted.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'filtering';
		if ( ! in_array( $tab, array( 'filtering', 'scan', 'support' ), true ) ) {
			$tab = 'filtering';
		}
		wp_safe_redirect( add_query_arg( 'tab', $tab, self::url( Settings::PAGE_SLUG ) ) );
		exit;
	}

	/**
	 * Load first-party assets on owned pages only.
	 *
	 * @return void
	 */
	public static function assets() {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'toplevel_page_' . self::HOME_SLUG, 'content-censor_page_' . Settings::PAGE_SLUG, 'content-censor_page_' . self::HOME_SLUG . '-new-features' ), true ) ) {
			return;
		}
		wp_enqueue_style( 'wpgo-content-censor-admin', plugins_url( 'css/content-censor-admin.css', WPGO_CONTENT_CENSOR_FILE ), array(), WPGO_CONTENT_CENSOR_VERSION );
		wp_enqueue_script( 'wpgo-content-censor-admin', plugins_url( 'src/admin.js', WPGO_CONTENT_CENSOR_FILE ), array(), WPGO_CONTENT_CENSOR_VERSION, true );
	}

	/**
	 * Use effective entitlement, not merely the installed Pro filename.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		return function_exists( 'content_censor_fs' ) && content_censor_fs()->can_use_premium_code__premium_only();
	}

	/**
	 * Build an internal page URL.
	 *
	 * @param string $slug Page slug.
	 * @return string
	 */
	public static function url( $slug ) {
		return add_query_arg( 'page', $slug, admin_url( 'admin.php' ) );
	}

	/**
	 * Choose the support route for the effective edition.
	 *
	 * @return string
	 */
	public static function support_url() {
		return self::is_pro() ? 'https://wpgoplugins.com/support/' : 'https://wordpress.org/support/plugin/wp-content-filter/';
	}

	/**
	 * Render an accessible external link.
	 *
	 * @param string $url Link destination.
	 * @param string $label Link label.
	 * @return void
	 */
	public static function external_link( $url, $label ) {
		?>
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?> <span aria-hidden="true">↗</span><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wp-content-filter' ); ?></span></a>
		<?php
	}

	/**
	 * Render the shared product identity.
	 *
	 * @param string $page Current page identity.
	 * @return void
	 */
	public static function header( $page ) {
		$headings = array(
			'home'         => __( 'Content Censor Home', 'wp-content-filter' ),
			'settings'     => __( 'Content Censor Settings', 'wp-content-filter' ),
			'new-features' => __( 'Content Censor New Features', 'wp-content-filter' ),
		);
		?>
		<header class="content-censor-header">
			<img src="<?php echo esc_url( plugins_url( 'assets/content-censor.png', WPGO_CONTENT_CENSOR_FILE ) ); ?>" width="64" height="64" alt="">
			<div><h1><?php echo esc_html( $headings[ $page ] ?? $headings['home'] ); ?></h1>
				<?php self::external_link( 'https://wpgoplugins.com/documentation/content-censor/changelog/', WPGO_CONTENT_CENSOR_VERSION ); ?>
				<span class="content-censor-badge <?php echo self::is_pro() ? 'is-pro' : 'is-free'; ?>"><?php echo self::is_pro() ? esc_html__( 'Pro', 'wp-content-filter' ) : esc_html__( 'Free', 'wp-content-filter' ); ?></span>
			</div>
			<div class="content-censor-feedback"><?php self::external_link( 'https://wpgoplugins.com/contact/', __( 'Send feedback', 'wp-content-filter' ) ); ?></div>
		</header>
		<?php
	}

	/**
	 * Protect all admin views, including direct requests.
	 *
	 * @return void
	 */
	private static function authorize() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage Content Censor.', 'wp-content-filter' ), 403 );
		}
	}

	/**
	 * Render a useful starting point without creating or publishing content.
	 *
	 * @return void
	 */
	public static function home() {
		self::authorize();
		?>
		<div class="wrap content-censor-settings">
			<?php self::header( 'home' ); ?>
			<div class="content-censor-grid">
				<section class="content-censor-card"><h2><?php esc_html_e( 'Start here', 'wp-content-filter' ); ?></h2>
					<p><?php esc_html_e( 'Choose the words to filter, select the public output areas, then check a representative page. Your stored posts and comments stay unchanged.', 'wp-content-filter' ); ?></p>
					<p><a class="button button-primary" href="<?php echo esc_url( self::url( Settings::PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Configure filtering', 'wp-content-filter' ); ?></a></p>
					<p><?php esc_html_e( 'Opening Settings does not publish content or change your rules.', 'wp-content-filter' ); ?></p>
				</section>
				<section class="content-censor-card"><h2><?php esc_html_e( 'Quick links', 'wp-content-filter' ); ?></h2><ul>
					<li><?php self::external_link( 'https://wpgoplugins.com/documentation/content-censor/', __( 'Documentation', 'wp-content-filter' ) ); ?></li>
					<li><?php self::external_link( 'https://wpgoplugins.com/documentation/content-censor/changelog/', __( 'Changelog', 'wp-content-filter' ) ); ?></li>
					<li><a href="<?php echo esc_url( self::url( Settings::PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Settings', 'wp-content-filter' ); ?></a></li>
					<li><?php self::external_link( self::support_url(), __( 'Get support', 'wp-content-filter' ) ); ?></li>
				</ul></section>
			</div>
			<section class="content-censor-card"><h2><?php esc_html_e( 'Quick start', 'wp-content-filter' ); ?></h2><ol>
				<li><?php esc_html_e( 'Add your words and choose a delimiter in Settings.', 'wp-content-filter' ); ?></li>
				<li><?php esc_html_e( 'Choose matching, replacement and output areas, then save your rules.', 'wp-content-filter' ); ?></li>
				<li><?php esc_html_e( 'Review the public page and confirm the original remains available in the editor.', 'wp-content-filter' ); ?></li>
			</ol><img class="content-censor-quick-start" src="<?php echo esc_url( plugins_url( 'assets/filter-settings.png', WPGO_CONTENT_CENSOR_FILE ) ); ?>" alt="<?php esc_attr_e( 'Content Censor word list, delimiter and excluded-content controls with sample words Apple, Orange and Pear.', 'wp-content-filter' ); ?>"></section>
			<section class="content-censor-card"><h2><?php esc_html_e( 'Feature actions', 'wp-content-filter' ); ?></h2>
				<p><a href="<?php echo esc_url( self::url( Settings::PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Configure words, matching and exclusions', 'wp-content-filter' ); ?></a></p>
				<?php if ( self::is_pro() ) : ?>
					<p><a href="<?php echo esc_url( add_query_arg( 'tab', 'scan', self::url( Settings::PAGE_SLUG ) ) ); ?>"><?php esc_html_e( 'Scan published content', 'wp-content-filter' ); ?></a></p>
					<p><a href="<?php echo esc_url( self::url( Settings::PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Configure browser titles and custom filters', 'wp-content-filter' ); ?></a></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Pro adds batched scanning, browser-title filtering, BuddyPress coverage and custom filters.', 'wp-content-filter' ); ?></p>
					<p><a class="button" href="<?php echo esc_url( function_exists( 'content_censor_fs' ) ? self::url( self::HOME_SLUG . '-pricing' ) : 'https://wpgoplugins.com/plugins/content-censor/' ); ?>"><?php esc_html_e( 'Explore Content Censor Pro', 'wp-content-filter' ); ?></a></p>
				<?php endif; ?>
			</section>
			<section class="content-censor-card"><h2><?php esc_html_e( 'Need a hand?', 'wp-content-filter' ); ?></h2><p><?php self::external_link( self::support_url(), __( 'Get help with Content Censor', 'wp-content-filter' ) ); ?></p></section>
		</div>
		<?php
	}

	/**
	 * Render current user-visible changes, not internal release metadata.
	 *
	 * @return void
	 */
	public static function new_features() {
		self::authorize();
		?>
		<div class="wrap content-censor-settings"><?php self::header( 'new-features' ); ?>
			<section class="content-censor-card"><h2><?php esc_html_e( 'A clearer starting point', 'wp-content-filter' ); ?></h2>
				<p><?php esc_html_e( 'Home, Settings and New Features now live together in the Content Censor menu. Existing Settings bookmarks still work.', 'wp-content-filter' ); ?></p>
				<p><?php esc_html_e( 'Reset settings with explicit confirmation and copy a privacy-safe support summary from License & Support.', 'wp-content-filter' ); ?></p>
				<p><?php self::external_link( 'https://wpgoplugins.com/documentation/content-censor/changelog/', __( 'Read the full changelog', 'wp-content-filter' ) ); ?></p>
			</section>
		</div>
		<?php
	}

	/**
	 * Render an allowlisted, copyable summary with no site or option values.
	 *
	 * @return void
	 */
	public static function support_summary() {
		global $wp_version;
		$summary = implode( "\n", array( 'Plugin: Content Censor', 'Version: ' . WPGO_CONTENT_CENSOR_VERSION, 'Edition: ' . ( self::is_pro() ? 'Pro' : 'Free' ), 'WordPress: ' . $wp_version, 'PHP: ' . PHP_VERSION, 'Locale: ' . get_locale(), 'Multisite: ' . ( is_multisite() ? 'yes' : 'no' ) ) );
		?>
		<section class="content-censor-card"><h2><?php esc_html_e( 'Support summary', 'wp-content-filter' ); ?></h2>
			<p><?php esc_html_e( 'This summary contains version and environment information only. It excludes content, settings, site addresses and licence details.', 'wp-content-filter' ); ?></p>
			<label class="screen-reader-text" for="content-censor-support-summary"><?php esc_html_e( 'Support summary', 'wp-content-filter' ); ?></label>
			<textarea id="content-censor-support-summary" class="large-text code" rows="7" readonly><?php echo esc_textarea( $summary ); ?></textarea>
			<p><button type="button" class="button" id="content-censor-copy-summary" data-copied="<?php esc_attr_e( 'Summary copied.', 'wp-content-filter' ); ?>" data-fallback="<?php esc_attr_e( 'Select and copy the summary using your keyboard.', 'wp-content-filter' ); ?>"><?php esc_html_e( 'Copy support summary', 'wp-content-filter' ); ?></button></p>
			<p id="content-censor-copy-status" aria-live="polite"></p>
		</section>
		<?php
	}
}
