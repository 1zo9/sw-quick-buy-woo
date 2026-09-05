<?php

/* create menu & page dashboard */
add_action('admin_menu', 'swpbw_add_admin_pages');
function swpbw_add_admin_pages()
{
	// Then the submenus
	add_submenu_page(
		'edit.php?post_type=product', // parent plug
		'Cài đặt mua nhanh sản phẩm', // Submenu Page Title
		'Cài đặt mua nhanh', //  Submenu Title
		'manage_options', // capability
		'swcustom-woopact', // slug
		'swqbwoo_cb_option_page' // callback
	);
	// add setting option
	add_action('admin_init','swqbwoo_register_settings');
}

function swqbwoo_register_settings() {
	//register our settings	
	register_setting( 
		'swqbw_options_woo', //  Option group
		'swqbw_setting_woo',//option name
		'sanitize' // callback
	);
}

function swqbwoo_cb_option_page()
{
?>
	<div class="wrap">

		<h2 class="title-ponsw"><?php _e(' Cài đặt mua nhanh - SonWeb', 'sonweb'); ?></h2>
		<div class="donate">
			<a class="button button-large" href="<?php echo esc_url(' https://www.paypal.com/paypalme/sonwebtl/2usd'); ?>" target="_blank">
				<?php _e('Click to Donate - Paypal', 'sonweb'); ?>
			</a>
		</div><!--end donate-->
		<div id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox qbwsw-settings">
						<h3 class="hndle"><?php _e('Plugin Settings', 'sonweb'); ?></h3>
						<hr>
						<div class="inside">
							<form method="post" action="options.php" class="cnb-container">
								<?php settings_fields('swqbw_options_woo'); ?>
								<table class="form-table">
									<tr valign="top">
										<th scope="row"><?php _e('Bật hiển thị:', 'sonweb'); ?> </th>
										<td>
											<label>
												<input type="checkbox" 
													name="swqbw_setting_woo[ck_turnon]" 
													id="ck_turnon" value="1" 
													<?php checked('1', intval(isset( $this->swqbw_options_setting_woo['ck_turnon']) ),true); ?>
												 />
												<?php _e('Bật hiển thị', 'sonweb'); ?>
											</label>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php esc_html_e('Chữ ở nút mua nhanh:'); ?></th>
										<td>
											<input type="text"
												name="wqbw_setting_woo[txtBuy1]"
												value="<?php echo esc_attr($this->swqbw_options_setting_woo['txtBuy1']);?>"
											/>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php esc_html_e('Chữ nhỏ ở nút mua nhanh:'); ?></th>
										<td>
											<input type="text" 
												name="wqbw_setting_woo[txtBuy2]"
												value="<?php echo esc_attr($this->swqbw_options_setting_woo['txtBuy2']);?>"
											/>
										</td>
									</tr>
								</table>
								<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
							</form>
						</div><!--end inside-->
					</div>
				</div>
			</div><!--end post-body-->
		</div><!--end poststuff-->
	</div><!--end wrap-->
<?php
}
?>