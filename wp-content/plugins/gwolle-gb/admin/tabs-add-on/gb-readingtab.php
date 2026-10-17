<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_reading_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_reading" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );


	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_reading_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_reading_nonce" name="gwolle_gb_addon_reading_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table">
		<tbody>

		<?php
		$items = gwolle_gb_addon_get_meta_fields_all_v2();
		if ( is_array( $items ) && ! empty( $items ) ) {

			$reading = get_option( 'gwolle_gb_addon-reading', array() );
			if ( is_string( $reading ) ) {
				$reading = maybe_unserialize( $reading );
			}

			foreach ( $items as $item ) {
				if ( ! isset($item['slug']) || ! isset($item['name']) ) {
					continue;
				}

				$item_slug = $item['slug'];
				$item_name = $item['name'];
				$option_reading = 2; // by default shown in metabox.
				if ( isset($reading["$item_slug"]) ) {
					$option_reading = $reading["$item_slug"];
				}
				?>
				<tr>
					<td colspan="3">
						<h3><?php esc_html_e('Configure where you want the extra fields displayed.', 'gwolle-gb'); ?></h3>
					</td>
				</tr>

				<tr>
					<td style="width:25%;">
						<?php echo esc_attr( $item_name ); ?>:
					</td>
					<td style="width:25%;">
						<label><input type="radio" name="gwolle_gb_addon_read[<?php echo esc_attr( $item_slug ); ?>]" value="0" <?php echo checked('0', $option_reading, false); ?> /><?php esc_html_e('Above content.', 'gwolle-gb'); ?></label><br />
						<label><input type="radio" name="gwolle_gb_addon_read[<?php echo esc_attr( $item_slug ); ?>]" value="1" <?php echo checked('1', $option_reading, false); ?> /><?php esc_html_e('Under content.', 'gwolle-gb'); ?></label><br />
						<label><input type="radio" name="gwolle_gb_addon_read[<?php echo esc_attr( $item_slug ); ?>]" value="2" <?php echo checked('2', $option_reading, false); ?> /><?php esc_html_e('In metabox.', 'gwolle-gb'); ?></label><br />
						<label><input type="radio" name="gwolle_gb_addon_read[<?php echo esc_attr( $item_slug ); ?>]" value="3" <?php echo checked('3', $option_reading, false); ?> /><?php esc_html_e('None.', 'gwolle-gb'); ?></label><br />
					</td>
					<td style="width:25%;"></td>
				</tr>

				<?php
			}
			?>

				<tr>
					<td colspan="3">
						<h3><?php esc_html_e('Which fields should be added to the guestbook widget.', 'gwolle-gb'); ?></h3>
					</td>
				</tr>

				<tr>
					<td colspan="2">
					<?php
					$widget = get_option( 'gwolle_gb_addon-widget', array() );
					if ( is_string( $widget ) ) {
						$widget = maybe_unserialize( $widget );
					}

					foreach ( $items as $item ) {
						if ( ! isset($item['slug']) || ! isset($item['name']) ) {
							continue;
						}

						$item_slug = $item['slug'];
						$item_name = $item['name'];
						$option_widget = 'off';
						if ( isset($widget["$item_slug"]) ) {
							$option_widget = $widget["$item_slug"];
						}
						?>
						<p>
							<label>
								<input type="checkbox" id="gwolle_gb_addon_widget[<?php echo esc_attr( $item_slug ); ?>]" name="gwolle_gb_addon_widget[<?php echo esc_attr( $item_slug ); ?>]" <?php echo checked('on', $option_widget, false); ?> />
								<?php echo esc_attr( $item_name ); ?>
							</label>
						</p>
						<?php
					}
					?>
					</td>
					<td style="width:25%;"></td>
				</tr>

			<?php
		} else {
			?>
			<tr>
				<td colspan="3">
					<p><?php esc_html_e('There are no Meta Fields saved yet. Please go to the Form tab, enter a field and save it.', 'gwolle-gb'); ?></p>
				</td>
			</tr>
			<?php
		}
		?>

		<tr>
			<th colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_addon_reading" id="gwolle_gb_addon_reading" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
