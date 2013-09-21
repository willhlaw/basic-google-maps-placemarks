<?php do_action( self::PREFIX . 'meta-address-before' ); ?>

<p><?php _e( 'Enter the address of the placemark. You can type in anything that you would type into a Google Maps search field, from a full address to an intersection, landmark, city or just a zip code.', 'bgmp' ); ?></p>

<table id="bgmp-placemark-coordinates">	<?php // @todo should use self::PREFIX, but too late b/c users already styling w/ this ?>
	<tbody>
		<tr>
			<th><label for="<?php echo self::PREFIX; ?>address"><?php _e( 'Address:', 'bgmp' ); ?></label></th>
			<td>
				<input id="<?php echo self::PREFIX; ?>address" name="<?php echo self::PREFIX; ?>address" type="text" class="regular-text" value="<?php echo $address; ?>" />
				
				<?php if( $showGeocodeResults ) : ?>
					<em><?php printf( __( '(Geocoded to: %f, %f)', 'bgmp' ), $latitude, $longitude ); ?></em>
			
				<?php elseif( $showGeocodeError ) : ?>
					<em><?php _e( "(Error geocoding address. Please make sure it's correct and try again.)", 'bgmp' ); ?></em>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>

<?php do_action( self::PREFIX . 'meta-address-after' ); ?>