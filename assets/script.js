jQuery(document).ready(function($) {
    var rawPrice = 0;
    var discountVal = 0;
    var discountType = '';
    var isFormDirty = false;

    function formatMoney(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.') + ' đ';
    }

    function calculateTotal() {
        var qty = parseInt($('#swQbQty').val()) || 1;
        var total = rawPrice * qty;

        if (discountVal > 0) {
            if (discountType === 'percent') {
                total = total - (total * (discountVal / 100));
            } else {
                total = total - discountVal;
            }
        }
        $('#swQbTotal').text(formatMoney(Math.max(0, total)));
    }

    function loadSavedData() {
        var data = JSON.parse(localStorage.getItem('sw_qb_customer_info') || '{}');
        if (data.name) $('#sw_name').val(data.name);
        if (data.phone) $('#sw_phone').val(data.phone);
        if (data.email) $('#sw_email').val(data.email);
        if (data.state) $('#sw_state').val(data.state);
        if (data.address) $('#sw_address').val(data.address);
    }

    $(document).on('click', '.sw-quick-buy-btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var pType = btn.data('type');

        // Xử lý biến thể nếu đang ở trang chi tiết
        var varId = 0;
        if (pType === 'variable') {
            varId = $('input[name="variation_id"]').val();
            if (!varId || varId == "0") {
                alert('Vui lòng chọn đầy đủ các thuộc tính (Màu sắc, Size...) trước khi mua!');
                return;
            }
            $('#swQbVariationId').val(varId);
        }

        rawPrice = parseFloat(btn.data('raw-price')) || 0;
        $('#swQbProductId').val(btn.data('id'));
        $('#swQbHeaderTitle').text(btn.data('title'));
        $('#swQbTitle').text(btn.data('title'));
        $('#swQbPrice').html(btn.data('price'));
        $('#swQbImg').attr('src', btn.data('image'));

        calculateTotal();
        loadSavedData();
        isFormDirty = false;
        $('#swQuickBuyModal').css('display', 'flex');
    });

    $('#swQbQty').on('input change', calculateTotal);
    $('#sw_name, #sw_phone').on('input', function() { isFormDirty = true; });

    $('#swApplyCouponBtn').on('click', function() {
        var code = $('#sw_coupon_code').val();
        if (!code) return;

        $.post(sw_qb_params.ajax_url, {
            action: 'sw_qb_apply_coupon',
            coupon: code
        }, function(res) {
            if (res.success) {
                discountVal = parseFloat(res.data.discount);
                discountType = res.data.type;
                calculateTotal();
                $('#swCouponMsg').css('color', 'green').text(res.data.msg);
            } else {
                $('#swCouponMsg').css('color', 'red').text(res.data);
            }
        });
    });

    function handleCloseModal() {
        var phone = $('#sw_phone').val();
        var name = $('#sw_name').val();
        var pId = $('#swQbProductId').val();

        if (sw_qb_params.exit_intent === '1' && isFormDirty && phone.length >= 8) {
            $.post(sw_qb_params.ajax_url, {
                action: 'sw_qb_save_lead',
                name: name,
                phone: phone,
                product_id: pId
            });

            if (!confirm(sw_qb_params.exit_msg)) {
                return false;
            }
        }
        $('#swQuickBuyModal').hide();
        return true;
    }

    $('.sw-qb-close, .sw-qb-overlay').on('click', function(e) {
        if (e.target === this) handleCloseModal();
    });

    $('#swQbForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        
        var customerData = {
            name: $('#sw_name').val(),
            phone: $('#sw_phone').val(),
            email: $('#sw_email').val(),
            state: $('#sw_state').val(),
            address: $('#sw_address').val()
        };
        localStorage.setItem('sw_qb_customer_info', JSON.stringify(customerData));

        $('#swQbSubmit').text('ĐANG XỬ LÝ...').prop('disabled', true);

        var dataStr = form.serialize() + '&action=sw_qb_submit_order&quantity=' + $('#swQbQty').val() + '&coupon=' + $('#sw_coupon_code').val();

        $.post(sw_qb_params.ajax_url, dataStr, function(res) {
            if (res.success) {
                alert(res.data);
                isFormDirty = false;
                $('#swQuickBuyModal').hide();
                form[0].reset();
            } else {
                alert(res.data);
            }
            $('#swQbSubmit').text('ĐẶT HÀNG NGAY').prop('disabled', false);
        });
    });
});
