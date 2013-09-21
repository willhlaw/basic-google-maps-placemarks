<div class="wrap"> 
	<?php // maybe rename this so it doesn't match settings.php in the root dir ?>
	
	<div id="icon-options-general" class="icon32"><br /></div>	<?php // @todo - why br here? use style instaed? ?>
	<h2><?php printf( __( '%s Settings', 'bgmp' ), BGMP_NAME ); ?></h2>

	<form method="post" action="options.php">
		<?php do_action( BasicGoogleMapsPlacemarks::PREFIX . 'settings-before' ); ?>
		
		<?php // @todo add nonce for settings? ?>
		
		<div id="<?php echo BasicGoogleMapsPlacemarks::PREFIX; ?>settings-fields">
			<?php settings_fields( BasicGoogleMapsPlacemarks::PREFIX . 'settings' ); ?>
			<?php do_settings_sections( BasicGoogleMapsPlacemarks::PREFIX . 'settings' ); ?>

			<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>"  /></p>
		</div> <!-- /#<?php echo BasicGoogleMapsPlacemarks::PREFIX; ?>settings-fields -->
		
		<div id="<?php echo BasicGoogleMapsPlacemarks::PREFIX; ?>settings-meta-boxes" class="metabox-holder">
			<div class="postbox-container">
				<?php do_meta_boxes( 'settings_page_' . BasicGoogleMapsPlacemarks::PREFIX .'settings', 'side', NULL ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce' ); ?>
			</div>
		</div>
		
		<?php do_action( BasicGoogleMapsPlacemarks::PREFIX . 'settings-after' ); ?>
	</form>
</div> <!-- .wrap -->