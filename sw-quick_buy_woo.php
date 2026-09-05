<?php 
/**
 * Plugin Name: SW Quick buy Woo
 * Plugin URI: https://sonweb.net/plugin-gui-thong-bao-don-hang-moi-woocommerce-telegram.html/
 * Description: Mua hàng nhanh Woocommerce
 * Version: 1.0.0
 * Author: SonWeb
 * Author URI: https://sonweb.net
 * Text Domain: sonweb
 * License: GPLv2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists('swqbwoSWQuickBuyWoo') ) {
	class swqbwoSWQuickBuyWoo {
		public $plugin;
		private $swqbw_options_setting_woo = array( 
			'ck_turnon'	=>'1',
			'txtBuy1'	=> 'Mua ngay',
			'txtBuy2'	=> 'Gọi điện xác nhận và giao hàng tận nơi',
			'popup_mess' => 'Bạn vui lòng nhập đúng số điện thoại để chúng tôi sẽ gọi xác nhận đơn hàng trước khi giao hàng. Xin cảm ơn!',
            'popup_sucess' => '<div class="popup-message success" style="color:#333;"><p class="clearfix" style="font-size:22px;color: #00c700;text-align:center">Đặt hàng thành công!</p><p class="clearfix" style="color: #00c700;padding: 10px 0;">Mã đơn hàng <span style="color: #333;font-weight: bold">#%%order_id%%</span></p><p class="clearfix">SonWeb SHOP sẽ liên hệ với bạn trong 12h tới. Cám ơn bạn đã cho chúng tôi cơ hội được phục vụ.<br><strong>Hotline:</strong> 0932.644.101</p><p class="clearfix"><strong>Ghi chú: </strong>Đơn hàng chỉ có hiệu lực trong vòng 48h</p><div></div><div></div></div>',
            'popup_error' => 'Đặt hàng thất bại. Vui lòng đặt hàng lại. Xin cảm ơn!',
		);

		function __construct() {
			$this->swqbw_define_constants();
			$this->plugin = plugin_basename(__FILE__);
			$this->swqbw_options_setting_woo = $this->swqbw_get_options();
		}

		function swqbw_define_constants() {

			if ( !defined('SWQBW_PLUGIN_PATH' ) ) {
				define('SWQBW_PLUGIN_PATH', plugin_dir_path(__FILE__));
			}
			if ( !defined('SWQBW_PLUGIN_URL' ) ) {
				define('SWQBW_PLUGIN_URL', plugin_dir_url(__FILE__));
			}
		}

		function register() {
			// add shortcode button quick buy
			add_shortcode('sw_quickbuy', array($this, 'sw_add_button_quick_buy'));
			// hook woocommerce_single_product_summary button cart
			add_action('woocommerce_single_product_summary', array($this, 'display_button_quick_buy'), 35);
			//add_action('wp_enqueue_scripts',array($this,'swqbw_enqueue'),10 );
			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
			// enqueue 
			include_once(SWQBW_PLUGIN_PATH .'/includes/swqbw-enqueue.php');
			//include_once(SWQBW_PLUGIN_PATH .'/includes/process.php');
			// register option
			add_action('admin_init',array($this, 'swqbwoo_register_settings') );
			// option page 
			//include_once(SWQBW_PLUGIN_PATH .'/includes/option-page.php');
			add_action( 'admin_notices', array( $this, 'admin_notices_sw' ) );
			// hook display popup form quick buy
			add_action('woocommerce_after_single_product', array($this, 'swqbw_quick_buy_popup_content'));
			// add to cart use popup 
			add_action('sw_prod_variable','woocommerce_template_single_add_to_cart');

			add_action( 'wp_ajax_swqbwprocess', array($this, 'swqbw_process_order_init') );
			add_action( 'wp_ajax_nopriv_swqbwprocess',array($this, 'swqbw_process_order_init') );
		}

		function admin_notices_sw() {
			if (isset($_GET['settings-updated'])) :
				?>
					<div class="notice notice-success is-dismissible update_swqbw">
						<p><?php esc_html_e( 'Cập nhật cài đặt thành công', 'sonweb' ); ?></p>
					</div>
				<?php
			endif;
		}
		// create content popup display 
		function swqbw_quick_buy_popup_content() {
			global $product;
			?>
				<div class="sw-popup-quickbuy" data-popup="popup-quickbuy">
					<div class="sw-popup-inner">
						<div class="sw-popup-title">
                            <span><?php echo get_the_title();?></span>
                            <button type="button" class="sw-popup-close"></button>
                        </div><!--end popup-title-->
						<div class="sw-popup-content">
							<div class="sw-popup-content-left">
                                <div class="sw-popup-prod">
                                    <?php if(has_post_thumbnail()):?>
                                        <div class="sw-popup-img"><?php the_post_thumbnail('shop_thumbnail');?></div>
                                    <?php endif;?>
                                    <div class="sw-popup-info">
                                        <span class="sw_title"><?php the_title();?></span>
                                        <?php if($product->get_type() == 'simple'):?>
											<span class="sw_price"><?php echo $product->get_price_html(); ?></span>
										<?php endif;?>
                                    </div>
                                </div> <!--end prod left-->
								<div class="sw_prod_variable" data-simpleprice="<?php echo $product->get_price();?>">
                                    <?php do_action('sw_prod_variable');?>
                                </div>
                            </div><!--end left-->
							<div class="sw-popup-content-right">
								<form class="sw_cusstom_info" id="sw_cusstom_info" method="post">
									<div class="popup-customer-info">
										<div class="popup-customer-info-title">
											<?php _e('Thông tin người mua','sw-quickbuy')?>
										</div>
										<div class="popup-customer-info-group popup-customer-info-radio">
                                            <label>
                                                <input type="radio" name="customer-gender" value="1" checked/>
                                                <span>Anh</span>
                                            </label>
                                            <label>
                                                <input type="radio" name="customer-gender" value="2"/>
                                                <span>Chị</span>
                                            </label>
                                        </div><!--info-radio-->
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-2">
                                                <input type="text" class="customer-name" name="customer-name" required placeholder="Họ và tên">
                                            </div>
                                            <div class="popup-customer-info-item-2">
                                                <input type="text" class="customer-phone" name="customer-phone" required placeholder="Số điện thoại">
                                            </div>
                                        </div><!--info-customer-->
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1">
												<input type="email" class="customer-email" name="customer-email" data-required="true" required placeholder="Địa chỉ email">
                                            </div>
                                        </div>
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1">
                                                <textarea class="customer-address" name="customer-address" placeholder="Địa chỉ nhận hàng (Không bắt buộc)"></textarea>
                                             </div>
                                        </div>
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1">
                                                <textarea class="order-note" name="order-note" placeholder="Ghi chú đơn hàng (Không bắt buộc)"></textarea>
                                            </div>
                                        </div>
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1 popup_quickbuy_shipping">
                                                <div class="popup_quickbuy_shipping_title">Tổng:</div>
                                                <div class="popup_quickbuy_total_calc"></div>
                                            </div>
                                        </div>
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1">
                                                <button type="button" class="sw-order-btn">Đặt hàng ngay</button>
                                            </div>
                                        </div>
										<div class="popup-customer-info-group">
                                            <div class="popup-customer-info-item-1">
                                                <div id="sw_mess" class="sw_quickbuy_mess"></div>
                                            </div>
                                        </div>
									</div>
									<input type="hidden" name="prod_id" id="prod_id" value="<?php the_ID();?>">
								</form>
							</div><!--end right-popup-->
						</div><!--end popup-content-->
					</div>
				</div><!--end popup-->
			<?php
		}
		// load page to menu
		public function add_admin_pages() {
			// Then the submenus
			add_submenu_page(
				'edit.php?post_type=product', // parent plug
				'Cài đặt mua nhanh sản phẩm', // Submenu Page Title
				'Cài đặt mua nhanh', //  Submenu Title
				'manage_options', // capability
				'swcustom-woopact', // slug
				array($this,'swqbwoo_cb_option_page') // callback
			);
		
		}

		function swqbwoo_register_settings() {
			//register our settings	
			register_setting( 
				'swqbw_options_woo', //  Option group
				'swqbw_setting_woo',//option name
				'sanitize' // callback
			);
		}
		// callback option page
		function swqbwoo_cb_option_page() {
			require_once SWQBW_PLUGIN_PATH . 'includes/admin.php';
		}

		function swqbw_get_options() {
			return wp_parse_args(get_option('swqbw_setting_woo'),$this->swqbw_options_setting_woo );
		}

		function sw_add_button_quick_buy() {
			//echo 'test';
			global $product;
                ob_start();
				if( $product->is_in_stock()):
                    ?>
                    <a href="javascript:void(0);" class="sw_buy_now" id="sw_buy_now">
                        <strong><?php echo $this->swqbw_options_setting_woo['txtBuy1']?></strong>
                        <span><?php echo $this->swqbw_options_setting_woo['txtBuy2']?></span>
                    </a>
                    <?php
                endif;
                return ob_get_clean();
		}

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
			wp_send_json_success($result);
				
			die();//bắt buộc phải có khi kết thúc
		}

		function display_button_quick_buy() {
			echo do_shortcode('[sw_quickbuy]');
		}

		// css frontend hook
		
	}// end class
	$objqbw = new swqbwoSWQuickBuyWoo();
	$objqbw->register();

}

?>