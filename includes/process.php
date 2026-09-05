<?php

add_action( 'wp_ajax_swqbwprocess', 'swqbw_process_order_init' );
add_action( 'wp_ajax_nopriv_swqbwprocess', 'swqbw_process_order_init' );

function swqbw_process_order_init() {
	$prod_id = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : '';
	$prod_check = wc_get_product($prod_id);

	parse_str($_POST['customer_info'], $customer_info);
	parse_str($_POST['product_info'], $product_info);

	$qty = isset($product_info['quantity']) ? (float) $product_info['quantity'] : 1;
	  // Now we create the order
	$order = wc_create_order();
	if(!is_wp_error($order)) {
		$args  = array();
		$order->add_product($prod_check, $qty, $args);
		$order->calculate_totals();

		$customer_gender = (isset($customer_info['customer-gender']) && $customer_info['customer-gender'] == 1)  ? 'Anh' : 'Chị';
		$customer_name = isset($customer_info['customer-name']) ? sanitize_text_field($customer_info['customer-name']): '';
		$customer_email = isset($customer_info['customer-email']) ? sanitize_email($customer_info['customer-email']): '';
		$customer_phone = isset($customer_info['customer-phone']) ? sanitize_text_field($customer_info['customer-phone']): '';
		$customer_address = isset($customer_info['customer-address']) ? sanitize_textarea_field($customer_info['customer-address']): '';
		$customer_note = isset($customer_info['order-note']) ? sanitize_textarea_field($customer_info['order-note']): '';

		$address = array(
			'first_name' => $customer_gender,
			'last_name'  => $customer_name,
			'email'      => $customer_email,
			'phone'      => $customer_phone,
			'address_1'  => $customer_address,
			'address_2'  => '', 
			'state'      => '',
			'country'    => 'VN'
		);

		$order->set_address( $address, 'billing' );
		$order->set_address( $address, 'shipping' );
		$order->update_status("processing", 'Đơn hàng nhanh', TRUE);

		$result['content'] = str_replace('%%order_id%%', $order->get_order_number(), $this->swqbw_options_setting_woo['popup_sucess']);
		
	}
	wp_send_json_success('Chào mừng bạn đến với '. $prod_id);
		
	die();//bắt buộc phải có khi kết thúc
}
?>