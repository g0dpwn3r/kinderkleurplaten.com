<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_misc_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_misc" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_misc_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_misc_nonce" name="gwolle_gb_addon_misc_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table">
		<tbody>

		<tr>
			<th scope="row"><label for="gb_preview"><?php /* translators: Settings page, option for preview */ esc_html_e('Preview', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-preview', 'true') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_preview" id="gb_preview">
				<label for="gb_preview">
					<?php esc_html_e('Show Preview button in Form.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('Adds a button to the form where visitors can preview their entry before posting.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_permalink"><?php /* translators: Settings page, option for permalink */ esc_html_e('Permalink', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-permalink', 'true') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_permalink" id="gb_permalink">
				<label for="gb_permalink">
					<?php esc_html_e('Show permalink in Metabox.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('A link to the single entry will be added to the metabox.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_email"><?php /* translators: Settings page, option for permalink */ esc_html_e('Author Email', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-email', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_email" id="gb_email">
				<label for="gb_email">
					<?php esc_html_e('Show author email in Metabox.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('The email address of the author will be added to the metabox.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_report"><?php esc_html_e('Report Abuse', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-report', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_report" id="gb_report">
				<label for="gb_report">
					<?php esc_html_e('Enable report abuse link in Metabox.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('Visitors can report abusive entries through a link in the metabox.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_upload"><?php /* translators: Settings page, option for uploading of images */ esc_html_e('Upload Images', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-upload', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_upload" id="gb_upload">
				<label for="gb_upload">
					<?php esc_html_e('Upload images through the form.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('Offer uploading of images. This will only be offered for users with the capability `gwolle_gb_upload_files`, which ususally is limited to Author, Editor and Administrator.', 'gwolle-gb'); ?><br />
					<?php esc_html_e('Images can be added through the form and will be uploaded to the Media Library and added to the content of the entry.', 'gwolle-gb'); ?><br />
					<?php esc_html_e('Please also enable the Visual formatting and Emoji in the main Form settings, image upload depends on that for BBcode support.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gb_likes"><?php /* translators: Settings page, option for likes */ esc_html_e('Likes', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-likes', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gb_likes" id="gb_likes">
				<label for="gb_likes">
					<?php esc_html_e('Enable likes for entries.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('This will enable likes, people can add a like to an entry and see the number of likes that were given.', 'gwolle-gb'); ?>
				</span>
				<?php
				$option = (int) get_option( 'gwolle_gb_addon-likes_loc', 1 );
				?>
				<p><label for="gwolle_gb_addon_likes_loc"><?php esc_html_e('Location for display', 'gwolle-gb'); ?></label></p>
				<label>
					<input type="radio" id="gwolle_gb_addon_likes_loc" name="gwolle_gb_addon_likes_loc" value="0" <?php echo checked('0', $option, false); ?> /><?php esc_html_e('Above content.', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_likes_loc" name="gwolle_gb_addon_likes_loc" value="1" <?php echo checked('1', $option, false); ?> /><?php esc_html_e('Under content.', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_likes_loc" name="gwolle_gb_addon_likes_loc" value="2" <?php echo checked('2', $option, false); ?> /><?php esc_html_e('In metabox.', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_likes_loc" name="gwolle_gb_addon_likes_loc" value="3" <?php echo checked('3', $option, false); ?> /><?php esc_html_e('None.', 'gwolle-gb'); ?>
				</label><br />
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gwolle-gb-sitemap"><?php /* translators: Settings page, option for sitemap */ esc_html_e('Sitemap', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-sitemap', 'true') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gwolle-gb-sitemap" id="gwolle-gb-sitemap">
				<label for="gwolle-gb-sitemap">
					<?php esc_html_e('Enable sitemap for guestbook pages.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('This option will enable generation of sitemap for the guestbook pages. The sitemap in WordPress Core is supported, while several sitemap plugins are also supported.', 'gwolle-gb'); ?><br />
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="delete_link"><?php /* translators: Settings page, option for delete link */ esc_html_e('Delete link', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-delete_link', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="delete_link" id="delete_link">
				<label for="delete_link">
					<?php esc_html_e('Show delete link in Metabox for moderators.', 'gwolle-gb'); ?>
				</label><br />
				<input <?php
					if (get_option( 'gwolle_gb_addon-delete_link_author', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="delete_link_author" id="delete_link_author">
				<label for="delete_link_author">
					<?php esc_html_e('Show delete link in Metabox for author.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('A link to delete the entry will be added to the metabox. Only visible for moderators and the author.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gwolle_gb_addon_auto_anonymize"><?php /* translators: Settings page, option for auto delete */ esc_html_e('Auto Anonymize', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-auto_anonymize', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gwolle_gb_addon_auto_anonymize" id="gwolle_gb_addon_auto_anonymize">
				<label for="gwolle_gb_addon_auto_anonymize">
					<?php esc_html_e('Auto Anonymize entries after a certain time.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('This setting will enable automatic anonymization of entries older than a certain date.', 'gwolle-gb');
					echo '<br />';
					esc_html_e('Be very carefull with this option.', 'gwolle-gb'); ?>
				</span><br /><br />

				<?php
				$option = (int) get_option( 'gwolle_gb_addon-auto_anonymize_time', 5 );
				esc_html_e('Auto Anonymize entries older than:', 'gwolle-gb'); ?><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="1" <?php echo checked('1', $option, false); ?> /><?php esc_html_e('1 Day', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="2" <?php echo checked('2', $option, false); ?> /><?php esc_html_e('2 Days', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="3" <?php echo checked('3', $option, false); ?> /><?php esc_html_e('1 Week', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="4" <?php echo checked('4', $option, false); ?> /><?php esc_html_e('2 Weeks', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="5" <?php echo checked('5', $option, false); ?> /><?php esc_html_e('1 Month', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="6" <?php echo checked('6', $option, false); ?> /><?php esc_html_e('6 Months', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_anonymize_time" name="gwolle_gb_addon_auto_anonymize_time" value="7" <?php echo checked('7', $option, false); ?> /><?php esc_html_e('12 Months', 'gwolle-gb'); ?>
				</label><br />
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="gwolle_gb_addon_auto_delete"><?php /* translators: Settings page, option for auto delete */ esc_html_e('Auto Delete', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb_addon-auto_delete', 'false') === 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="gwolle_gb_addon_auto_delete" id="gwolle_gb_addon_auto_delete">
				<label for="gwolle_gb_addon_auto_delete">
					<?php esc_html_e('Auto Delete entries after a certain time.', 'gwolle-gb'); ?>
				</label><br />
				<span class="setting-description">
					<?php esc_html_e('This setting will enable automatic deletion of entries older than a certain date.', 'gwolle-gb');
					echo '<br />';
					esc_html_e('Be very carefull with this option.', 'gwolle-gb'); ?>
				</span><br /><br />

				<?php
				$option = (int) get_option( 'gwolle_gb_addon-auto_delete_time', 5 );
				esc_html_e('Auto Delete entries older than:', 'gwolle-gb'); ?><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="1" <?php echo checked('1', $option, false); ?> /><?php esc_html_e('1 Day', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="2" <?php echo checked('2', $option, false); ?> /><?php esc_html_e('2 Days', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="3" <?php echo checked('3', $option, false); ?> /><?php esc_html_e('1 Week', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="4" <?php echo checked('4', $option, false); ?> /><?php esc_html_e('2 Weeks', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="5" <?php echo checked('5', $option, false); ?> /><?php esc_html_e('1 Month', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="6" <?php echo checked('6', $option, false); ?> /><?php esc_html_e('6 Months', 'gwolle-gb'); ?>
				</label><br />
				<label>
					<input type="radio" id="gwolle_gb_addon_auto_delete_time" name="gwolle_gb_addon_auto_delete_time" value="7" <?php echo checked('7', $option, false); ?> /><?php esc_html_e('12 Months', 'gwolle-gb'); ?>
				</label><br />
			</td>
		</tr>

		<tr>
			<th colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_addon_misc" id="gwolle_gb_addon_misc" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
