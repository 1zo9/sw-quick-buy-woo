<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('wp_enqueue_scripts','swqbw_enqueue',10);
function swqbw_enqueue() {
	wp_enqueue_style('swqbwoo', SWQBW_PLUGIN_URL . 'assets/swqbwoo.css' );
	wp_enqueue_script( 
		'bpopup', SWQBW_PLUGIN_URL . 'js/jquery.bpopup.min.js', ['jquery'], '1.0.0', true 
	);

	wp_register_script( 
			'sw_qbw', SWQBW_PLUGIN_URL . 'js/swqbw.js', ['jquery'], '1.0.0', true 
	);
	
	wp_localize_script( 'sw_qbw', 'swqbw_obj', [
			'ajax_url'      =>  admin_url( 'admin-ajax.php' )
	]);
	
	wp_enqueue_script( 'sw_qbw' );
	
}
// admin script
add_action('admin_enqueue_scripts','swqbwoo_enqueue_admin_js',100);
function swqbwoo_enqueue_admin_js() {
	
	wp_enqueue_style('admin_qbwoosw', SWQBW_PLUGIN_URL . 'assets/admin_qbwoosw.css' );
	 
}

?>