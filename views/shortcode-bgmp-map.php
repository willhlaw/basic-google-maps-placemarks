<script type="text/javascript">
	var bgmpData = {
		options: <?php echo json_encode( $this->getMapOptions( $attributes ) ); ?>,
		markers: <?php echo json_encode( $this->getMapPlacemarks( $attributes ) ); ?>
	};
</script>
	
<div id="<?php echo self::PREFIX; ?>map-canvas">
	<p><?php _e( 'Loading map...', 'bgmp' ); ?></p>
	<p><img src="<?php echo plugins_url( 'images/loading.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading', 'bgmp' ); ?>" /></p>
</div>