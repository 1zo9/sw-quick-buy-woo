<?php
/**
 * Plugin Name: SW Quick Buy Woo Advanced
 * Description: Plugin Đặt Hàng Nhanh WooCommerce tối ưu Mobile, Biến thể, Coupon, Tỉnh thành VN & Exit-Intent.
 * Version: 3.0.0
 * Author: Custom Developer
 */

if (!defined('ABSPATH')) exit;

class SW_Quick_Buy_Woo_Adv {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_popup_modal'));
        
        // Shortcode
        add_shortcode('sw_quick_buy', array($this, 'quick_buy_shortcode'));
        
        // Admin Settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX Order & Coupon
        add_action('wp_ajax_sw_qb_submit_order', array($this, 'handle_quick_order'));
        add_action('wp_ajax_nopriv_sw_qb_submit_order', array($this, 'handle_quick_order'));
        add_action('wp_ajax_sw_qb_apply_coupon', array($this, 'handle_apply_coupon'));
        add_action('wp_ajax_nopriv_sw_qb_apply_coupon', array($this, 'handle_apply_coupon'));
        add_action('wp_ajax_sw_qb_save_lead', array($this, 'handle_save_lead'));
        add_action('wp_ajax_nopriv_sw_qb_save_lead', array($this, 'handle_save_lead'));
    }

    public function add_admin_menu() {
        add_menu_page('SW Quick Buy', 'SW Quick Buy', 'manage_options', 'sw-quick-buy-settings', array($this, 'settings_page_html'), 'dashicons-cart', 56);
    }

    public function register_settings() {
        register_setting('sw_qb_group', 'sw_qb_btn_text');
        register_setting('sw_qb_group', 'sw_qb_main_color');
        register_setting('sw_qb_group', 'sw_qb_enable_exit_intent');
        register_setting('sw_qb_group', 'sw_qb_exit_msg');
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Cấu Hình SW Quick Buy Woo</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('sw_qb_group');
                $btn_text = get_option('sw_qb_btn_text', 'MUA NGAY');
                $main_color = get_option('sw_qb_main_color', '#c59841');
                $exit_intent = get_option('sw_qb_enable_exit_intent', '1');
                $exit_msg = get_option('sw_qb_exit_msg', 'Ưu đãi đặc biệt sắp hết hạn! Bạn có chắc muốn thoát?');
                ?>
                <table class="form-table">
                    <tr>
                        <th>Chữ hiển thị trên nút:</th>
                        <td><input type="text" name="sw_qb_btn_text" value="<?php echo esc_attr($btn_text); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Màu chủ đạo:</th>
                        <td><input type="color" name="sw_qb_main_color" value="<?php echo esc_attr($main_color); ?>"></td>
                    </tr>
                    <tr>
                        <th>Bật bẫy giữ chân (Exit-Intent):</th>
                        <td><input type="checkbox" name="sw_qb_enable_exit_intent" value="1" <?php checked('1', $exit_intent); ?>></td>
                    </tr>
                    <tr>
                        <th>Cảnh báo khi đóng Popup:</th>
                        <td><input type="text" name="sw_qb_exit_msg" value="<?php echo esc_attr($exit_msg); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button('Lưu cấu hình'); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style('sw-qb-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '3.0.0');
        $main_color = get_option('sw_qb_main_color', '#c59841');
        wp_add_inline_style('sw-qb-style', ".sw-qb-header, .sw-qb-submit-btn { background: {$main_color} !important; }");

        wp_enqueue_script('sw-qb-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '3.0.0', true);
        wp_localize_script('sw-qb-script', 'sw_qb_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'exit_intent' => get_option('sw_qb_enable_exit_intent', '1'),
            'exit_msg' => get_option('sw_qb_exit_msg', 'Ưu đãi đặc biệt sắp hết hạn! Bạn có chắc muốn thoát?')
        ));
    }

    public function quick_buy_shortcode($atts) {
        $atts = shortcode_atts(array('id' => 0), $atts);
        $product_id = $atts['id'] ? $atts['id'] : get_the_ID();
        $product = wc_get_product($product_id);
        if (!$product) return '';

        $btn_text = get_option('sw_qb_btn_text', 'MUA NGAY');
        return sprintf(
            '<button type="button" class="sw-quick-buy-btn" data-id="%s" data-type="%s" data-title="%s" data-price="%s" data-raw-price="%s" data-image="%s">%s</button>',
            $product->get_id(),
            $product->get_type(),
            esc_attr($product->get_name()),
            esc_attr($product->get_price_html()),
            $product->get_price(),
            esc_url(wp_get_attachment_image_url($product->get_image_id(), 'medium')),
            esc_html($btn_text)
        );
    }

    public function render_popup_modal() {
        $states = WC()->countries->get_states('VN');
        ?>
        <div id="swQuickBuyModal" class="sw-qb-overlay">
            <div class="sw-qb-container">
                <div class="sw-qb-drag-bar"><span class="drag-handle"></span></div>
                <div class="sw-qb-header">
                    <span id="swQbHeaderTitle" class="sw-qb-title">ĐẶT MUA SẢN PHẨM</span>
                    <span class="sw-qb-close">&times;</span>
                </div>
                <div class="sw-qb-body">
                    <div class="sw-qb-prod-col">
                        <div class="sw-qb-prod-summary">
                            <img id="swQbImg" src="" alt="">
                            <div>
                                <h4 id="swQbTitle"></h4>
                                <div id="swQbPrice" class="sw-qb-price"></div>
                            </div>
                        </div>
                        <div class="sw-qb-qty-box">
                            <label>Số lượng:</label>
                            <input type="number" id="swQbQty" value="1" min="1">
                        </div>
                    </div>
                    <form id="swQbForm" class="sw-qb-form-col">
                        <div class="sw-qb-gender">
                            <label><input type="radio" name="gender" value="Anh" checked> Anh</label>
                            <label><input type="radio" name="gender" value="Chị"> Chị</label>
                        </div>
                        <div class="sw-qb-row">
                            <input type="text" name="name" id="sw_name" placeholder="Họ và tên *" required>
                            <input type="tel" name="phone" id="sw_phone" placeholder="Số điện thoại *" required>
                        </div>
                        <div class="sw-qb-row">
                            <input type="email" name="email" id="sw_email" placeholder="Địa chỉ email">
                            <?php if (!empty($states)) : ?>
                                <select name="state" id="sw_state">
                                    <option value="">-- Chọn Tỉnh / Thành --</option>
                                    <?php foreach ($states as $code => $name) : ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <textarea name="address" id="sw_address" placeholder="Địa chỉ nhận hàng (Không bắt buộc)" rows="2"></textarea>
                        <textarea name="note" id="sw_note" placeholder="Ghi chú đơn hàng" rows="2"></textarea>
                        
                        <div class="sw-qb-coupon-box">
                            <input type="text" id="sw_coupon_code" placeholder="Mã giảm giá">
                            <button type="button" id="swApplyCouponBtn">Áp dụng</button>
                        </div>
                        <div id="swCouponMsg"></div>

                        <div class="sw-qb-total-box">
                            Tổng tiền: <span id="swQbTotal">0 đ</span>
                        </div>

                        <input type="hidden" name="product_id" id="swQbProductId">
                        <input type="hidden" name="variation_id" id="swQbVariationId">
                        <button type="submit" class="sw-qb-submit-btn" id="swQbSubmit">ĐẶT HÀNG NGAY</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_apply_coupon() {
        $code = sanitize_text_field($_POST['coupon']);
        if (!$code) wp_send_json_error('Vui lòng nhập mã.');
        
        $coupon = new WC_Coupon($code);
        if (!$coupon->is_valid()) {
            wp_send_json_error('Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }
        
        wp_send_json_success(array(
            'discount' => $coupon->get_amount(),
            'type' => $coupon->get_discount_type(),
            'msg' => 'Áp dụng mã giảm giá thành công!'
        ));
    }

    public function handle_quick_order() {
        $product_id = intval($_POST['product_id']);
        $var_id     = intval($_POST['variation_id']);
        $qty        = max(1, intval($_POST['quantity']));
        $name       = sanitize_text_field($_POST['name']);
        $phone      = sanitize_text_field($_POST['phone']);
        $email      = sanitize_email($_POST['email']);
        $state      = sanitize_text_field($_POST['state']);
        $address    = sanitize_textarea_field($_POST['address']);
        $note       = sanitize_textarea_field($_POST['note']);
        $gender     = sanitize_text_field($_POST['gender']);
        $coupon     = sanitize_text_field($_POST['coupon']);

        if (!$product_id || !$name || !$phone) {
            wp_send_json_error('Vui lòng điền các thông tin bắt buộc.');
        }

        $order = wc_create_order();
        $target_id = $var_id ? $var_id : $product_id;
        $order->add_product(wc_get_product($target_id), $qty);

        if ($coupon) $order->apply_coupon($coupon);

        $address_data = array(
            'first_name' => $gender . ' ' . $name,
            'phone'      => $phone,
            'email'      => $email,
            'state'      => $state,
            'country'    => 'VN',
            'address_1'  => $address ? $address : 'N/A',
        );

        $order->set_address($address_data, 'billing');
        $order->set_address($address_data, 'shipping');
        if ($note) $order->set_customer_note($note);

        $order->calculate_totals();
        $order->update_status('processing', 'Đơn hàng SW Quick Buy Woo.', true);

        wp_send_json_success('Đặt hàng thành công! Chúng tôi sẽ liên hệ sớm nhất.');
    }

    public function handle_save_lead() {
        $phone = sanitize_text_field($_POST['phone']);
        $name = sanitize_text_field($_POST['name']);
        $prod_id = intval($_POST['product_id']);

        if ($phone && $prod_id) {
            set_transient('sw_qb_lead_' . $phone, array('name' => $name, 'phone' => $phone, 'product_id' => $prod_id), DAY_IN_SECONDS);
        }
        wp_send_json_success();
    }
}

new SW_Quick_Buy_Woo_Adv();
