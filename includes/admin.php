<div class="wrap wrap-swqbw">

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
                                            name="swqbw_setting_woo[txtBuy1]"
                                            value="<?php echo esc_attr($this->swqbw_options_setting_woo['txtBuy1']);?>"
                                        />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Chữ nhỏ ở nút mua nhanh:'); ?></th>
                                    <td>
                                        <input type="text" 
                                            name="swqbw_setting_woo[txtBuy2]"
                                            value="<?php echo esc_attr($this->swqbw_options_setting_woo['txtBuy2']);?>"
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="popup_mess"><?php _e('Lời ghi chú trong popup','sonweb');?></label></th>
                                    <td>
                                        <?php
                                            $settings = array(
                                                'textarea_name' =>'swqbw_setting_woo[popup_mess]',
                                                'teeny' => true,
                                                'textarea_rows' => 5,
                                                'tabindex' => 1,
                                                'wpautop' => false,
                                                'media_buttons' => false,
                                            );
                                            wp_editor( $this->swqbw_options_setting_woo['popup_mess'], 'popup_mess', $settings );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="popup_sucess">
                                            <?php _e('Lời nhắn khi mua hàng thành công','sonweb');?>
                                        </label><br>
                                    </th>
                                    <td>
                                        <?php
                                            $settings = array(
                                                'textarea_name' =>'swqbw_setting_woo[popup_sucess]',
                                                'teeny' => true,
                                                'textarea_rows' => 10,
                                                'tabindex' => 1,
                                                'wpautop' => false,
                                                'media_buttons' => false,
                                            );
                                            wp_editor( $this->swqbw_options_setting_woo['popup_sucess'], 'popup_sucess', $settings );
                                        ?>
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