<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_social_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_social" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_social_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_social_nonce" name="gwolle_gb_addon_social_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table">
		<tbody>

		<tr>
			<th scope="row"><label for="gb_social_media"><?php esc_html_e('Share on Social Media', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" <?php
					if ( get_option( 'gwolle_gb_addon-social_media', 'false' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> name="gb_social_media" id="gb_social_media" /><label for="gb_social_media"><?php esc_html_e('Share on Social Media', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description">
					<?php esc_html_e('Show share icons for Social Media in the metabox. Below you can select which ones and their order.', 'gwolle-gb');
					echo '<br />';
					esc_html_e('Preferably you choose 6 services, since the standard layout has space for 6 icons.', 'gwolle-gb');
					?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_social_services"><?php esc_html_e('Sharing Services', 'gwolle-gb'); ?></label></th>
			<td>
				<?php
				$services = gwolle_gb_addon_get_social_media_v2();
				if ( is_array($services) && ! empty($services) ) {
					foreach ( $services as $key => $service ) {
						?>
						<label>
							<input type="checkbox" <?php
								if ( $service['check'] === 'true' ) {
									echo 'checked="checked"';
								}
								?> id="gb_social_services[<?php echo esc_attr( $key ); ?>]" name="gb_social_services[<?php echo esc_attr( $key ); ?>]" />
							<img src="<?php echo esc_attr( $service['icon'] ); ?>" />
							<?php echo esc_html( $service['name'] ); ?>
						</label>
						<br /><br />
					<?php
					}
				}
				?>
				<span class="setting-description"><?php esc_html_e('Select the Social Media services you want enabled for sharing.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<?php
		$option = (int) get_option( 'gwolle_gb_addon-social_media_loc', 2 );
		?>

		<tr>
			<th scope="row"><label for="gwolle_gb_addon_social_media_loc"><?php esc_html_e('Location for display', 'gwolle-gb'); ?></label></th>
			<td>
				<label><input type="radio" name="gwolle_gb_addon_social_media_loc" value="0" <?php echo checked('0', $option, false); ?> /><?php esc_html_e('Above content.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_social_media_loc" value="1" <?php echo checked('1', $option, false); ?> /><?php esc_html_e('Under content.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_social_media_loc" value="2" <?php echo checked('2', $option, false); ?> /><?php esc_html_e('In metabox.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_social_media_loc" value="3" <?php echo checked('3', $option, false); ?> /><?php esc_html_e('None.', 'gwolle-gb'); ?></label><br />
			</td>
			<td style="width:25%;"></td>
		</tr>

		<tr>
			<th colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_addon_social" id="gwolle_gb_addon_social" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
