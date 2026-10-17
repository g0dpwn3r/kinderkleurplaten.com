<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_starrating_v2() {

	if ( ! current_user_can('manage_options') ) {
		die(__('You need a higher level of permission.', 'gwolle-gb'));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_starrating" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_starrating_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_starrating_nonce" name="gwolle_gb_addon_starrating_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table">
		<tbody>

		<tr>
			<th scope="row"><label for="gb_starrating"><?php esc_html_e('Star Rating', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" <?php
					if ( get_option( 'gwolle_gb_addon-starrating', 'false' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> name="gb_starrating" id="gb_starrating" /><label for="gb_starrating"><?php esc_html_e('Star Rating', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description"><?php esc_html_e('Use star rating so visitors can give a star rating for your website or post.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_starrating_avg"><?php esc_html_e('Show Average', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" <?php
					if ( get_option( 'gwolle_gb_addon-starrating_avg', 'false' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> name="gb_starrating_avg" id="gb_starrating_avg" /><label for="gb_starrating_avg"><?php esc_html_e('Show Average Star Rating', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description"><?php esc_html_e('The average will be shown above the list of entries.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<?php
		$option = (int) get_option( 'gwolle_gb_addon-starrating_loc', 2 );
		?>
		<tr>
			<th scope="row"><label for="gwolle_gb_addon_starrating_loc"><?php esc_html_e('Location for display', 'gwolle-gb'); ?></label></th>
			<td>
				<label><input type="radio" name="gwolle_gb_addon_starrating_loc" value="0" <?php echo checked('0', $option, false); ?> /><?php esc_html_e('Above content.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_starrating_loc" value="1" <?php echo checked('1', $option, false); ?> /><?php esc_html_e('Under content.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_starrating_loc" value="2" <?php echo checked('2', $option, false); ?> /><?php esc_html_e('In metabox.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_starrating_loc" value="3" <?php echo checked('3', $option, false); ?> /><?php esc_html_e('None.', 'gwolle-gb'); ?></label><br />
				<label><input type="radio" name="gwolle_gb_addon_starrating_loc" value="4" <?php echo checked('4', $option, false); ?> /><?php esc_html_e('After author info heading.', 'gwolle-gb'); ?></label><br />
			</td>
			<td style="width:25%;"></td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_starrating_type"><?php esc_html_e('Subject for Star Rating', 'gwolle-gb'); ?></label></th>
			<td>
				<select name="gb_starrating_type" id="gb_starrating_type">
					<?php // https://webmasters.googleblog.com/2019/09/making-review-rich-results-more-helpful.html
					$type = get_option( 'gwolle_gb_addon-starrating_type', 'LocalBusiness' );
					$presets = array(
						'Book',
						'Course',
						'CreativeWorkSeason',
						'CreativeWorkSeries',
						'Episode',
						'Event',
						'Game',
						'HowTo',
						'LocalBusiness',
						'MediaObject',
						'MusicPlaylist',
						'MusicRecording',
						'Organization',
						'Product',
						'Recipe',
						'SoftwareApplication',
					);
					$count_presets = count($presets);
					for ($i = 0; $i < $count_presets; $i++) {
						echo '<option value="' . esc_attr( $presets["$i"] ) . '"';
						if ($presets["$i"] === $type) {
							echo ' selected="selected"';
						}
						echo '>' . esc_html( $presets["$i"] ) . '</option>';
					}
					?>
				</select>
				<br />
				<span class="setting-description"><?php esc_html_e('Subject for Average Star Rating, used by Search Engines.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<tr>
			<th colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_addon_starrating" id="gwolle_gb_addon_starrating" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
