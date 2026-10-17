<?php
/*
 * Settings page for the guestbook
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_page_settingstab_form_v2() {

	if ( ! current_user_can('manage_options') ) {
		die(esc_html__('You need a higher level of permission.', 'gwolle-gb'));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_form" />
	<?php
	settings_fields( 'gwolle_gb_addon_options' );
	do_settings_sections( 'gwolle_gb_addon_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_addon_form_nonce' );
	echo '<input type="hidden" id="gwolle_gb_addon_form_nonce" name="gwolle_gb_addon_form_nonce" value="' . esc_attr( $nonce ) . '" />';
	?>
	<table class="form-table" style="max-width:700px;">
		<tbody>

		<tr>
			<td colspan="3">
				<h3><?php esc_html_e('Configure the extra fields that you want.', 'gwolle-gb'); ?></h3>
			</td>
		</tr>
		<tr>
			<td style="width:15%;"></td>
			<td colspan="2">
				<p><?php esc_html_e('The name of the field is what you will see in the label in the form.', 'gwolle-gb'); ?></p>
				<p><?php esc_html_e('The slug of the field is where your data is attached to. Only change the slug if you know what you are doing.', 'gwolle-gb'); ?></p>
				<p><?php esc_html_e('Reserved slugs are: ', 'gwolle-gb');
				echo 'starrating, likes, unlikes, report-abuse.'; ?>
				</p>
			</td>
		</tr>


		<tr>
			<th scope="row"><label for="form_top"><?php esc_html_e('Top', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'top' ); ?>


		<tr>
			<th scope="row"><label for="form_name"><?php esc_html_e('Name', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'name' ); ?>


		<tr>
			<th scope="row"><label for="form_city"><?php esc_html_e('City', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'city' ); ?>


		<tr>
			<th scope="row"><label for="form_email"><?php esc_html_e('Email', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'email' ); ?>


		<tr>
			<th scope="row"><label for="form_website"><?php esc_html_e('Website', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'website' ); ?>


		<tr>
			<th scope="row"><label for="form_message"><?php esc_html_e('Message', 'gwolle-gb'); ?></label></th>
		</tr>
		<?php gwolle_gb_addon_page_settingstab_formfields_v2( 'message' ); ?>


		<tr>
			<th colspan="3">
				<p class="submit">
					<input type="submit" name="gwolle_gb_addon_form" id="gwolle_gb_addon_form" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}


/*
 * @param string $request the formfields that this request is for.
 * @since 2.0.0
 */
function gwolle_gb_addon_page_settingstab_formfields_v2( $request ) {
	$items = gwolle_gb_addon_get_meta_fields_v2( $request );
	if ( is_array( $items ) ) {
		foreach ( $items as $item ) {
			if ( ! isset($item['slug']) || ! isset($item['name']) ) {
				continue;
			}
			?>

			<tr>
				<td style="width:15%;"></td>
				<td style="width:70%;">
					<table>
						<tr>
							<td><?php esc_html_e('Name:', 'gwolle-gb'); ?></td>
							<td><input type="text" name="gb_form[<?php echo esc_attr( $request ); ?>][name][]" value="<?php echo esc_attr( $item['name'] ); ?>" required /></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Slug:', 'gwolle-gb'); ?></td>
							<td><input type="text" name="gb_form[<?php echo esc_attr( $request ); ?>][slug][]" value="<?php echo esc_attr( $item['slug'] ); ?>" required /></td>
						</tr>
						<tr>
							<td><?php esc_html_e('Required:', 'gwolle-gb'); ?></td>
							<td><?php
								$on = '';
								$value = 'off';
								if ( isset($item['required']) && (int) $item['required'] == 1 ) {
									$on = 'checked="checked"';
									$value = 'on';
								} ?>
								<input type="checkbox" <?php echo $on; ?> name="gb_form[<?php echo esc_attr( $request ); ?>][required_checkbox][]" class="gwolle-gb-formfield-required-checkbox" />
								<input type="hidden" name="gb_form[<?php echo esc_attr( $request ); ?>][required][]" value="<?php echo esc_attr( $value ); ?>" class="gwolle-gb-formfield-required-text" />
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e('Type:', 'gwolle-gb'); ?></td>
							<td><?php if ( ! isset($item['type']) ) {
									$item['type'] = 'text';
								} ?>
								<select class="gb-addon-form-type" name="gb_form[<?php echo esc_attr( $request ); ?>][type][]">
									<option value="text" <?php if ($item['type'] == 'text') { echo 'selected="selected"'; } ?>><?php esc_html_e('Text', 'gwolle-gb'); ?></option>
									<option value="checkbox" <?php if ($item['type'] == 'checkbox') { echo 'selected="selected"'; } ?>><?php esc_html_e('Checkbox', 'gwolle-gb'); ?></option>
									<option value="radio" <?php if ($item['type'] == 'radio') { echo 'selected="selected"'; } ?>><?php esc_html_e('Radio buttons', 'gwolle-gb'); ?></option>
									<option value="select" <?php if ($item['type'] == 'select') { echo 'selected="selected"'; } ?>><?php esc_html_e('Select dropdown', 'gwolle-gb'); ?></option>
									<option value="textarea" <?php if ($item['type'] == 'textarea') { echo 'selected="selected"'; } ?>><?php esc_html_e('Textarea', 'gwolle-gb'); ?></option>
								</select>

								<div class="gb-addon-form-options" <?php if ($item['type'] == 'text' || $item['type'] == 'checkbox' || $item['type'] == 'textarea') { echo 'style="display:none;"'; } ?>>
									<?php if ( ! isset($item['options']) ) {
										$item['options'] = '';
									} ?>
									<span class="setting-description">
										<?php esc_html_e('Enter options for the radio buttons or select dropdown.', 'gwolle-gb'); ?><br />
										<?php esc_html_e( 'Use one option on each line. When the meta field is saved, it will be saved with this value.', 'gwolle-gb' ); ?>
									</span><br />
									<textarea name="gb_form[<?php echo esc_attr( $request ); ?>][options][]" rows="6" cols="50" class="large-text code"><?php echo esc_textarea( $item['options'] ); ?></textarea>
								</div>
							</td>
						</tr>

					</table>
				</td>
				<td style="width:15%;">
					<p class="gb-form-delete">
						<a style="color:red;" href="#" onClick="gwolle_gb_addon_meta_delete( this ); return false;">
							<?php esc_html_e('Delete', 'gwolle-gb'); ?>
						</a>
					</p>
				</td>
			</tr>

			<?php
		}
	} ?>

	<tr class="gb-form-<?php echo esc_attr( $request ); ?>-new" style="display:none;">
		<td style="width:15%;"></td>
		<td style="width:70%;">
			<table>
				<tr class="gwolle-gb-addon-field-name">
					<td><?php esc_html_e('Name:', 'gwolle-gb'); ?></td>
					<td><input type="text" name="gb_form[<?php echo esc_attr( $request ); ?>][name][]" value="" /></td>
				</tr>
				<tr class="gwolle-gb-addon-field-slug">
					<td><?php esc_html_e('Slug:', 'gwolle-gb'); ?></td>
					<td><input type="text" name="gb_form[<?php echo esc_attr( $request ); ?>][slug][]" value="" /></td>
				</tr>
				<tr class="gwolle-gb-addon-field-required">
					<td><?php esc_html_e('Required:', 'gwolle-gb'); ?></td>
					<td>
						<input type="checkbox" name="gb_form[<?php echo esc_attr( $request ); ?>][required_checkbox][]" class="gwolle-gb-formfield-required-checkbox" />
						<input type="hidden" name="gb_form[<?php echo esc_attr( $request ); ?>][required][]" value="off" class="gwolle-gb-formfield-required-text" />
					</td>
				</tr>
				<tr class="gwolle-gb-addon-field-type">
					<td><?php esc_html_e('Type:', 'gwolle-gb'); ?></td>
					<td>
						<select class="gb-addon-form-type" name="gb_form[<?php echo esc_attr( $request ); ?>][type][]">
							<option value="text"><?php esc_html_e('Text', 'gwolle-gb'); ?></option>
							<option value="checkbox"><?php esc_html_e('Checkbox', 'gwolle-gb'); ?></option>
							<option value="radio"><?php esc_html_e('Radio buttons', 'gwolle-gb'); ?></option>
							<option value="select"><?php esc_html_e('Select dropdown', 'gwolle-gb'); ?></option>
							<option value="textarea"><?php esc_html_e('Textarea', 'gwolle-gb'); ?></option>
						</select>

						<div class="gb-addon-form-options" style="display:none;">
							<span class="setting-description">
								<?php esc_html_e('Enter options for the radio buttons or select dropdown.', 'gwolle-gb'); ?><br />
								<?php esc_html_e( 'Use one option on each line. When the meta field is saved, it will be saved with this value.', 'gwolle-gb' ); ?>
							</span><br />
							<textarea name="gb_form[<?php echo esc_attr( $request ); ?>][options][]" rows="6" cols="50" class="large-text code"></textarea>
						</div>
					</td>
				</tr>

			</table>
		</td>
		<td style="width:15%;">
			<p class="gb-form-delete">
				<a style="color:red;" href="#" onClick="gwolle_gb_addon_meta_delete( this ); return false;">
					<?php esc_html_e('Delete', 'gwolle-gb'); ?>
				</a>
			</p>
		</td>
	</tr>

	<tr class="gb-form-<?php echo esc_attr( $request ); ?>-before">
		<td style="width:15%;"></td>
		<td colspan="2">
			<span class="gb-form-<?php echo esc_attr( $request ); ?>-add-new">
				<strong><?php esc_html_e('+ Add new field.', 'gwolle-gb'); ?></strong>
			</span>
		</td>
	</tr>

<?php
}
