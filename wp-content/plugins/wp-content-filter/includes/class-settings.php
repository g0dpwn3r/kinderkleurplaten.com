<?php
/**
 * Settings repository and admin page.
 *
 * @package WPGO_Content_Censor
 */

namespace WPGO\Content_Censor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the Settings API contract and accessible admin UI.
 */
final class Settings {

	/** Settings page slug. */
	const PAGE_SLUG = 'content-censor-wpgoplugins-settings';

	/** Settings group. */
	const GROUP = 'content_censor_plugin_options_group';

	/**
	 * Settings page hook.
	 *
	 * @var string
	 */
	private static $page_hook = '';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function boot() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_content_censor_reset', array( __CLASS__, 'reset' ) );
	}

	/**
	 * Return canonical defaults, including licensed extensions.
	 *
	 * @return array<string, string>
	 */
	public static function defaults() {
		$defaults = array(
			'chk_post_title'       => '1',
			'chk_post_content'     => '1',
			'chk_comments'         => '1',
			'chk_tags'             => '0',
			'chk_tag_cloud'        => '0',
			'chk_ignore_html_attr' => '0',
			'txtar_keywords'       => 'Apple|Orange|Pear',
			'txt_exclude'          => '',
			'drp_keyword_sep'      => 'pipe',
			'rdo_strict_filtering' => 'strict_on',
			'rdo_word'             => 'all',
			'drp_filter_char'      => 'star',
			'rdo_case'             => 'insen',
		);

		/**
		 * Filter the canonical option defaults.
		 *
		 * @param array<string, string> $defaults Defaults.
		 */
		return apply_filters( 'content_censor_default_options', $defaults );
	}

	/**
	 * Return settings merged over defaults.
	 *
	 * @return array<string, string>
	 */
	public static function get() {
		$stored = get_option( WPGO_CONTENT_CENSOR_OPTIONS, array() );
		return wp_parse_args( is_array( $stored ) ? $stored : array(), self::defaults() );
	}

	/**
	 * Register the Settings API record.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			WPGO_CONTENT_CENSOR_OPTIONS,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitize every supported option with an explicit allow-list.
	 *
	 * @param mixed $input Submitted value.
	 * @return array<string, string>
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array(
			'txtar_keywords'       => self::sanitize_keywords( (string) ( $input['txtar_keywords'] ?? '' ) ),
			'txt_exclude'          => Migrator::sanitize_id_list( (string) ( $input['txt_exclude'] ?? '' ) ),
			'drp_keyword_sep'      => self::allowed( $input, 'drp_keyword_sep', array( 'pipe', 'comma', 'pound' ), 'pipe' ),
			'rdo_strict_filtering' => self::allowed( $input, 'rdo_strict_filtering', array( 'strict_on', 'strict_off' ), 'strict_on' ),
			'rdo_word'             => self::allowed( $input, 'rdo_word', array( 'first', 'all', 'firstlast' ), 'all' ),
			'drp_filter_char'      => self::allowed( $input, 'drp_filter_char', array( 'star', 'dollar', 'question', 'exclamation', 'hyphen', 'hash', 'tilde', 'blank' ), 'star' ),
			'rdo_case'             => self::allowed( $input, 'rdo_case', array( 'sen', 'insen' ), 'insen' ),
		);

		foreach ( array( 'chk_post_title', 'chk_post_content', 'chk_comments', 'chk_tags', 'chk_tag_cloud', 'chk_ignore_html_attr' ) as $checkbox ) {
			$clean[ $checkbox ] = isset( $input[ $checkbox ] ) && '1' === (string) $input[ $checkbox ] ? '1' : '0';
		}

		// Keep licensed settings intact if premium code is temporarily unavailable,
		// for example after a licence expires or while the Free build is active.
		$stored = get_option( WPGO_CONTENT_CENSOR_OPTIONS, array() );
		if ( is_array( $stored ) ) {
			foreach ( array( 'chk_browser_title', 'txtar_filters' ) as $premium_key ) {
				if ( array_key_exists( $premium_key, $stored ) ) {
					$clean[ $premium_key ] = (string) $stored[ $premium_key ];
				}
			}
		}

		/**
		 * Sanitize licensed settings that are absent from the WordPress.org build.
		 *
		 * @param array<string, string> $clean Sanitized shared settings.
		 * @param array<string, mixed>  $input Raw submitted settings.
		 */
		return apply_filters( 'content_censor_sanitize_options', $clean, $input );
	}

	/**
	 * Add the Settings submenu.
	 *
	 * @return void
	 */
	public static function add_page() {
		self::$page_hook = (string) add_submenu_page(
			Admin::HOME_SLUG,
			esc_html__( 'Content Censor Settings', 'wp-content-filter' ),
			esc_html__( 'Settings', 'wp-content-filter' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Load assets only on this plugin's page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( self::$page_hook !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'wpgo-content-censor-admin',
			plugins_url( 'css/content-censor-admin.css', WPGO_CONTENT_CENSOR_FILE ),
			array(),
			WPGO_CONTENT_CENSOR_VERSION
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage Content Censor.', 'wp-content-filter' ) );
		}

		$tabs = apply_filters(
			'content_censor_settings_tabs',
			array(
				'filtering' => esc_html__( 'Filtering', 'wp-content-filter' ),
				'support'   => esc_html__( 'License & Support', 'wp-content-filter' ),
			)
		);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This selects a read-only settings tab.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'filtering';
		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'filtering';
		}
		?>
		<div class="wrap content-censor-settings">
			<?php Admin::header( 'settings' ); ?>
			<p class="content-censor-intro"><?php esc_html_e( 'Control where configured words are filtered and how each match is rendered.', 'wp-content-filter' ); ?></p>
			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Content Censor settings', 'wp-content-filter' ); ?>">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a class="nav-tab <?php echo $active_tab === $slug ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( self::tab_url( $slug ) ); ?>" <?php echo $active_tab === $slug ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php settings_errors(); ?>
			<?php if ( 'filtering' === $active_tab ) : ?>
				<?php self::render_filtering_form(); ?>
			<?php elseif ( 'support' === $active_tab ) : ?>
				<?php self::render_support(); ?>
			<?php else : ?>
				<?php do_action( 'content_censor_render_settings_tab', $active_tab ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Reset settings after a nonce and capability check.
	 *
	 * @return void
	 */
	public static function reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to reset Content Censor.', 'wp-content-filter' ), 403 );
		}
		check_admin_referer( 'content_censor_reset' );
		if ( ! isset( $_POST['content_censor_confirm_reset'] ) || '1' !== $_POST['content_censor_confirm_reset'] ) {
			wp_die( esc_html__( 'Confirm that you want to reset the settings.', 'wp-content-filter' ), 400 );
		}
		update_option( WPGO_CONTENT_CENSOR_OPTIONS, self::defaults(), false );
		wp_safe_redirect( add_query_arg( 'settings-updated', 'reset', self::tab_url( 'filtering' ) ) );
		exit;
	}

	/**
	 * Render shared settings.
	 *
	 * @return void
	 */
	private static function render_filtering_form() {
		$options = self::get();
		$name    = WPGO_CONTENT_CENSOR_OPTIONS;
		?>
		<form method="post" action="options.php">
			<?php settings_fields( self::GROUP ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="content-censor-keywords"><?php esc_html_e( 'Words to filter', 'wp-content-filter' ); ?></label></th>
					<td>
						<textarea class="large-text code" id="content-censor-keywords" name="<?php echo esc_attr( $name ); ?>[txtar_keywords]" rows="8"><?php echo esc_textarea( $options['txtar_keywords'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter words separated by the selected delimiter. Words shorter than three characters are ignored.', 'wp-content-filter' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="content-censor-delimiter"><?php esc_html_e( 'Word delimiter', 'wp-content-filter' ); ?></label></th>
					<td>
						<select id="content-censor-delimiter" name="<?php echo esc_attr( $name ); ?>[drp_keyword_sep]">
							<option value="pipe" <?php selected( 'pipe', $options['drp_keyword_sep'] ); ?>><?php esc_html_e( 'Pipe (|)', 'wp-content-filter' ); ?></option>
							<option value="comma" <?php selected( 'comma', $options['drp_keyword_sep'] ); ?>><?php esc_html_e( 'Comma (,)', 'wp-content-filter' ); ?></option>
							<option value="pound" <?php selected( 'pound', $options['drp_keyword_sep'] ); ?>><?php esc_html_e( 'Hash (#)', 'wp-content-filter' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="content-censor-exclusions"><?php esc_html_e( 'Excluded content IDs', 'wp-content-filter' ); ?></label></th>
					<td>
						<input class="regular-text code" id="content-censor-exclusions" name="<?php echo esc_attr( $name ); ?>[txt_exclude]" value="<?php echo esc_attr( $options['txt_exclude'] ); ?>">
						<p class="description"><?php esc_html_e( 'Comma-separated post or page IDs that must never be filtered.', 'wp-content-filter' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Content areas', 'wp-content-filter' ); ?></th>
					<td>
						<?php self::checkbox( $name, $options, 'chk_post_content', esc_html__( 'Post and page content and excerpts', 'wp-content-filter' ) ); ?>
						<?php self::checkbox( $name, $options, 'chk_post_title', esc_html__( 'Titles', 'wp-content-filter' ) ); ?>
						<?php self::checkbox( $name, $options, 'chk_comments', esc_html__( 'Comments and comment author names', 'wp-content-filter' ) ); ?>
						<?php self::checkbox( $name, $options, 'chk_tags', esc_html__( 'Post tag links', 'wp-content-filter' ) ); ?>
						<?php self::checkbox( $name, $options, 'chk_tag_cloud', esc_html__( 'Post tag clouds', 'wp-content-filter' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Matching', 'wp-content-filter' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Matching', 'wp-content-filter' ); ?></legend>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[rdo_strict_filtering]" value="strict_on" <?php checked( 'strict_on', $options['rdo_strict_filtering'] ); ?>> <?php esc_html_e( 'Whole words only', 'wp-content-filter' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[rdo_strict_filtering]" value="strict_off" <?php checked( 'strict_off', $options['rdo_strict_filtering'] ); ?>> <?php esc_html_e( 'Also filter words embedded in longer text', 'wp-content-filter' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[rdo_case]" value="insen" <?php checked( 'insen', $options['rdo_case'] ); ?>> <?php esc_html_e( 'Case-insensitive', 'wp-content-filter' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $name ); ?>[rdo_case]" value="sen" <?php checked( 'sen', $options['rdo_case'] ); ?>> <?php esc_html_e( 'Case-sensitive', 'wp-content-filter' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="content-censor-rendering"><?php esc_html_e( 'Word rendering', 'wp-content-filter' ); ?></label></th>
					<td>
						<select id="content-censor-rendering" name="<?php echo esc_attr( $name ); ?>[rdo_word]">
							<option value="all" <?php selected( 'all', $options['rdo_word'] ); ?>><?php esc_html_e( 'Replace every character', 'wp-content-filter' ); ?></option>
							<option value="first" <?php selected( 'first', $options['rdo_word'] ); ?>><?php esc_html_e( 'Keep the first character', 'wp-content-filter' ); ?></option>
							<option value="firstlast" <?php selected( 'firstlast', $options['rdo_word'] ); ?>><?php esc_html_e( 'Keep the first and last characters', 'wp-content-filter' ); ?></option>
						</select>
						<select aria-label="<?php esc_attr_e( 'Replacement character', 'wp-content-filter' ); ?>" name="<?php echo esc_attr( $name ); ?>[drp_filter_char]">
							<?php foreach ( self::wildcards() as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $options['drp_filter_char'] ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'HTML handling', 'wp-content-filter' ); ?></th>
					<td>
						<?php self::checkbox( $name, $options, 'chk_ignore_html_attr', esc_html__( 'Leave words inside HTML tags and attributes unchanged', 'wp-content-filter' ) ); ?>
					</td>
				</tr>
				<?php do_action( 'content_censor_settings_fields', $name, $options ); ?>
			</table>
			<?php submit_button(); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="content-censor-reset-form" data-confirm="<?php esc_attr_e( 'Reset all Content Censor settings to their defaults? Your stored posts and comments will not change.', 'wp-content-filter' ); ?>">
			<input type="hidden" name="action" value="content_censor_reset">
			<?php wp_nonce_field( 'content_censor_reset' ); ?>
			<p><label><input type="checkbox" name="content_censor_confirm_reset" value="1" required> <?php esc_html_e( 'I confirm that all Content Censor settings will return to their defaults.', 'wp-content-filter' ); ?></label></p>
			<?php submit_button( esc_html__( 'Reset settings', 'wp-content-filter' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
	}

	/**
	 * Render license and support links.
	 *
	 * @return void
	 */
	private static function render_support() {
		$account_url = function_exists( 'content_censor_fs' ) ? content_censor_fs()->get_account_url() : '';
		?>
		<div class="content-censor-card">
			<h2><?php esc_html_e( 'License and updates', 'wp-content-filter' ); ?></h2>
			<p><?php esc_html_e( 'Manage your license, billing details, downloads, and automatic updates through your Content Censor account.', 'wp-content-filter' ); ?></p>
			<?php if ( $account_url ) : ?>
				<p><a class="button button-primary" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Open account', 'wp-content-filter' ); ?></a></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Account access is currently unavailable. Your filtering settings remain available.', 'wp-content-filter' ); ?></p>
			<?php endif; ?>
		</div>
		<div class="content-censor-card">
			<h2><?php esc_html_e( 'Documentation and support', 'wp-content-filter' ); ?></h2>
			<p><?php Admin::external_link( 'https://wpgoplugins.com/documentation/content-censor/', __( 'Read the setup guide', 'wp-content-filter' ) ); ?></p>
			<p><?php Admin::external_link( Admin::support_url(), __( 'Get support', 'wp-content-filter' ) ); ?></p>
		</div>
		<?php Admin::support_summary(); ?>
		<?php
	}

	/**
	 * Render one checkbox control.
	 *
	 * @param string                $name    Option record name.
	 * @param array<string, string> $options Options.
	 * @param string                $key     Setting key.
	 * @param string                $label   Visible label.
	 * @return void
	 */
	private static function checkbox( $name, array $options, $key, $label ) {
		?>
		<label class="content-censor-checkbox"><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( '1', $options[ $key ] ?? '0' ); ?>> <?php echo esc_html( $label ); ?></label>
		<?php
	}

	/**
	 * Build a settings-tab URL.
	 *
	 * @param string $tab Tab slug.
	 * @return string
	 */
	private static function tab_url( $tab ) {
		return add_query_arg(
			array(
				'page' => self::PAGE_SLUG,
				'tab'  => $tab,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Sanitize the keyword source while enforcing a practical storage bound.
	 *
	 * @param string $value Raw keywords.
	 * @return string
	 */
	private static function sanitize_keywords( $value ) {
		$value = sanitize_textarea_field( $value );
		return strlen( $value ) > 50000 ? substr( $value, 0, 50000 ) : $value;
	}

	/**
	 * Return a whitelisted submitted value.
	 *
	 * @param array<string, mixed> $input   Input.
	 * @param string               $key     Key.
	 * @param array<int, string>   $allowed Allowed values.
	 * @param string               $fallback Fallback.
	 * @return string
	 */
	private static function allowed( array $input, $key, array $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * Labels for replacement characters.
	 *
	 * @return array<string, string>
	 */
	private static function wildcards() {
		return array(
			'star'        => esc_html__( 'Asterisk (*)', 'wp-content-filter' ),
			'dollar'      => esc_html__( 'Dollar ($)', 'wp-content-filter' ),
			'question'    => esc_html__( 'Question mark (?)', 'wp-content-filter' ),
			'exclamation' => esc_html__( 'Exclamation mark (!)', 'wp-content-filter' ),
			'hyphen'      => esc_html__( 'Hyphen (-)', 'wp-content-filter' ),
			'hash'        => esc_html__( 'Hash (#)', 'wp-content-filter' ),
			'tilde'       => esc_html__( 'Tilde (~)', 'wp-content-filter' ),
			'blank'       => esc_html__( 'Remove completely', 'wp-content-filter' ),
		);
	}
}
