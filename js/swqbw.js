(function($){
    $(document).ready(function(){
        $('.sw_buy_now').click(function(){
             // Prevents the default action to be triggered. 
            
            $('.sw-popup-quickbuy').bPopup({
                speed: 450,
                transition: "slideDown",
                zIndex: 9999999,
                closeClass: "sw-popup-close",
                onOpen: function() {
                    getTotal(1);
                    $('.sw_prod_variable input[name="quantity"]').on('input', function() {
                        n =  $('.sw_prod_variable input[name="quantity"]').val();
                        getTotal(n);

                        //alert(n);
                    });
                    //getTotal(1);
                    
                },
                
            });
        });
       
        $('.sw-popup-close').click(function(){
            $('.sw-popup-quickbuy').bPopup().close();
        });

        function getTotal(sl) {
            var total,n,pr = 0;
            pr = $(".sw_prod_variable").data("simpleprice");
            total = pr * sl;
            $(".popup_quickbuy_total_calc").html(total);
        }
    }),

    $(".sw-order-btn").on("click",function() {
        var r = 0;
        var cus = $("#sw_cusstom_info").serialize();
        var proinfo = $(".sw_prod_variable .cart").serialize();
        r = $("#prod_id").val();
        var form = {
            action: 'swqbwprocess',
            customer_info: cus,
            product_info: proinfo,
            prod_id: r,
        };
        //alert(r);
        /*
        $.post(swqbw_obj.ajax_url,form,function(response){
             // Log the response to the console
            console.log("Response: "+response);
            $("#sw_mess").html(response.data);

        });
        */
        $.ajax({
            type: "post",
            dataType: "json",
            url: swqbw_obj.ajax_url,
            data:form,
            context: this,
            beforeSend: function() {
                $(".sw-order-btn").addClass("loading")
            },
            success: function(e) {
                e.success ? $(".sw-popup-content-right").html(e.data.content) : 'Mua hàng thành công',
                $(".sw-order-btn").removeClass("loading")
            },
            error: function() {
                alert(swqbw_obj.popup_error),
                $(".sw-order-btn").removeClass("loading")
            }
        })
        
    })
})(jQuery);