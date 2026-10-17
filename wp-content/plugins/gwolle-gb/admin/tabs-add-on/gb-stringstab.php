<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_strings_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_strings" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_strings_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_strings_nonce" name="gwolle_gb_addon_strings_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table">
		<thead>
			<tr>
				<th colspan="4"><?php esc_html_e('String Replacement', 'gwolle-gb'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="2">
					<span class="setting-description">
						<?php esc_html_e('Here you can replace text strings throughout the frontend form, the list of entries, and the messages that get displayed for the form.', 'gwolle-gb'); ?><br />
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<strong><?php esc_html_e('Old String', 'gwolle-gb'); ?></strong><br />
					<small><?php esc_html_e('Example: Guestbook', 'gwolle-gb'); ?></small>
				</td>
				<td colspan="2">
					<strong><?php esc_html_e('New String', 'gwolle-gb'); ?></strong><br />
					<small><?php esc_html_e('Example: Review', 'gwolle-gb'); ?></small>
				</td>
			</tr>

			<?php
			$output = '';
			$strings = get_option( 'gwolle_gb_addon-strings', array() );
			if ( is_string( $strings ) ) {
				$strings = maybe_unserialize( $strings );
			}
			if ( is_array($strings) && ! empty($strings) ) {
				foreach ( $strings as $oldstring => $newstring ) {
					$output .= '

			<tr>
				<td style="width:35%;">
					<input type="text" name="gb_strings[oldstring][]" value="' . esc_attr( esc_html( $oldstring ) ) . '" style="width:99%;" />
				</td>
				<td style="width:2%;">&raquo;</td>
				<td style="width:35%;">
					<input type="text" name="gb_strings[newstring][]" value="' . esc_attr( esc_html( $newstring ) ) . '" style="width:99%;" />
				</td>
				<td style="width:25%;">
					<span class="gb-string-delete">
						<a style="color:red;" href="#" onClick="gwolle_gb_addon_string_delete( this ); return false;">
							' . esc_html__('Delete', 'gwolle-gb') . '
						</a>
					</span>
				</td>
			</tr>

					';
				}
			}
			echo $output;
			?>

			<tr class="gb-string-new">
				<td style="width:35%;">
					<input type="text" name="gb_strings[oldstring][]" value="" style="width:99%;" />
				</td>
				<td style="width:2%;">&raquo;</td>
				<td style="width:35%;">
					<input type="text" name="gb_strings[newstring][]" value="" style="width:99%;" />
				</td>
				<td style="width:25%;">
					<span class="gb-string-delete">
						<a style="color:red;" href="#" onClick="gwolle_gb_addon_string_delete( this ); return false;">
							<?php esc_html_e('Delete', 'gwolle-gb'); ?>
						</a>
					</span>
				</td>
			</tr>

			<tr class="gb-string-before">
				<td colspan="4">
					<span class="gb-string-add-new">
						<strong><?php esc_html_e('+ Add new string.', 'gwolle-gb'); ?></strong>
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="4">
					<p class="submit">
						<input type="submit" name="gwolle_gb_addon_strings" id="gwolle_gb_addon_strings" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
					</p>
				</td>
			</tr>

		</tbody>
	</table>

	<?php
}
