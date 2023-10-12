var product_page_selector_main = "";
var additional_page_selector_main = "";

var pathname = window.location.pathname;

var baseURL = 'https://603e-103-50-83-46.ngrok-free.app/'; //Change here when production URL is a bit different
console.log('here');
//alert('here');
function validateZipCodes(e){
    e.preventDefault();

    var zipCode = $("#zipCode").val();
    var url = "";
    var productId = "";
    if(pathname != "/cart"){
         url = baseURL+"apis/validate_product_zipcode.php?shopURL=" + Shopify.shop+"&zipcode="+zipCode+"&product="+meta.product.id;
    }
    else{
        $.ajax({
            type: 'GET',
            url: '/cart.js',
            cache: false,
            dataType: 'json',
            success: function(cart) {
                console.log(cart);
                productId = cart.items[0].id
                // whatever you want to do with the cart obj
            }
        });
         url = baseURL+"apis/validate_product_zipcode.php?shopURL=" + Shopify.shop+"&zipcode="+zipCode+"&product="+productId;
    }
    $.ajax({
        url: url,
        method: "GET",
        dataType: 'json',
        cache: false,
        success: function (response) {
            console.log(response.allowed_shipping);
            if(response.allowed_shipping==false){
                $("#jobo_success").fadeOut(300);
                $("#jobo_error").fadeIn(300);
                $('#restrict_shipping_btn').addClass('shaker');
                $("#zipCode").css({"border-color": "RED",
                    "border-width":"3px",
                    "border-style":"solid"});
                setTimeout(function(){
                    $('#restrict_shipping_btn').removeClass('shaker');
                },300);
            }
            else{
                $("#jobo_success").fadeIn(300);
                $("#jobo_error").fadeOut(300);
                $("#restrict-shipping-form").fadeOut(300);
                $('#restrict_shipping_btn').addClass('shaker');
                $("#zipCode").css({"border-color": "GREEN",
                    "border-width":"3px",
                    "border-style":"solid"});
                setTimeout(function(){
                    $('#restrict_shipping_btn').removeClass('shaker');
                    $(product_page_selector_main).show();
                    if(additional_page_selector_main != ""){
                        $(additional_page_selector_main).show();
                    }

                },300);
            }
        }
    });
    return false;
}


function add_scripts(){
    //var params = new window.URLSearchParams(window.location.search);


    jQuery.ajax({
        url: baseURL+"apis/get_shop_data.php?shopURL="+Shopify.shop,
        method: "GET",
        dataType : 'json',
        cache: false,
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "PUT, GET, POST",
            "Access-Control-Allow-Headers": "Origin, X-Requested-With, Content-Type, Accept",
            "ngrok-skip-browser-warning": "true"
        },
        success: function(response) {


            if (response['data'].enabled_on_product_page == 1 && pathname != "/cart") {
                var styleSheet = "<style>#restrict_shipping_btn.shaker {\n" +
                    "    animation: shake 0.3s;\n" +
                    "    /* When the animation is finished, start again */\n" +
                    "    animation-iteration-count: 1; //single shake \n" +
                    "}\n" +
                    "\n" +
                    "@keyframes shake {\n" +
                    "    0% {\n" +
                    "        transform: translate(1px, 1px) rotate(0deg);\n" +
                    "}\n" +
                    "    10% {\n" +
                    "        transform: translate(-1px, -2px) rotate(-1deg);\n" +
                    "}\n" +
                    "    20% {\n" +
                    "        transform: translate(-3px, 0px) rotate(1deg);\n" +
                    "}\n" +
                    "    30% {\n" +
                    "        transform: translate(3px, 2px) rotate(0deg);\n" +
                    "}\n" +
                    "    40% {\n" +
                    "        transform: translate(1px, -1px) rotate(1deg);\n" +
                    "}\n" +
                    "    50% {\n" +
                    "        transform: translate(-1px, 2px) rotate(-1deg);\n" +
                    "}\n" +
                    "    60% {\n" +
                    "        transform: translate(-3px, 1px) rotate(0deg);\n" +
                    "}\n" +
                    "    70% {\n" +
                    "        transform: translate(3px, 1px) rotate(-1deg);\n" +
                    "}\n" +
                    "    80% {\n" +
                    "        transform: translate(-1px, -1px) rotate(1deg);\n" +
                    "}\n" +
                    "    90% {\n" +
                    "        transform: translate(1px, 2px) rotate(0deg);\n" +
                    "}\n" +
                    "    100% {\n" +
                    "        transform: translate(1px, -2px) rotate(-1deg);\n" +
                    "}\n" +
                    "}.form-control {\n" +
                    "  display: inline-block;\n" +
                    "  vertical-align: middle;\n" +
                    "  width: auto;\n" +
                    "  height: 34px;\n" +
                    "  padding: 6px 12px;\n" +
                    "  font-size: 13px;\n" +
                    "  line-height: 1.42857143;\n" +
                    "  color: #555555;\n" +
                    "  background-color: #ffffff;\n" +
                    "  background-image: none;\n" +
                    "  border: 1px solid #cccccc;\n" +
                    "  border-radius: 4px;\n" +
                    "  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;\n" +
                    "  transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;\n" +
                    "  -webkit-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  -moz-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  -o-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "-moz-box-sizing: border-box;\n" +
                    "-webkit-box-sizing: border-box;\n" +
                    "box-sizing: border-box;\n" +
                    "}\n" +
                    ".form-control:focus {\n" +
                    "  border-color: #66afe9;\n" +
                    "  outline: 0;\n" +
                    "  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "  -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "  box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "}\n" +
                    ".form-control::-moz-placeholder {\n" +
                    "  color: #999999;\n" +
                    "  opacity: 1;\n" +
                    "}\n" +
                    ".form-control:-ms-input-placeholder {\n" +
                    "  color: #999999;\n" +
                    "}\n" +
                    ".form-control::-webkit-input-placeholder {\n" +
                    "  color: #999999;\n" +
                    "}\n" +
                    "\n" +
                    ".btn {\n" +
                    "  display: inline-block;\n" +
                    "  margin-bottom: 0;\n" +
                    "  font-weight: normal;\n" +
                    "  text-align: center;\n" +
                    "  vertical-align: middle;\n" +
                    "  cursor: pointer;\n" +
                    "  background-image: none;\n" +
                    "  border: 1px solid transparent;\n" +
                    "  white-space: nowrap;\n" +
                    "  padding: 6px 12px;\n" +
                    "  font-size: 14px;\n" +
                    "  line-height: 1.42857143;\n" +
                    "  border-radius: 4px;\n" +
                    "  -webkit-user-select: none;\n" +
                    "  -moz-user-select: none;\n" +
                    "  -ms-user-select: none;\n" +
                    "  user-select: none;\n" +
                    "  color: #ffffff;\n" +
                    "  background-color: #428bca;\n" +
                    "  border-color: #357ebd;\n" +
                    "}\n" +
                    "\n" +
                    "/* additions */\n" +
                    ".btn-inline {\n" +
                    "  height: 36px;\n" +
                    "  border-radius: 0 4px 4px 0;\n" +
                    "  \n" +
                    "   \n" +
                    "    background-color: #43467f;\n" +
                    "    color: #fff;\n" +
                    "    border: 1px solid;\n" +
                    "    cursor: pointer;\n" +
                    "    margin-bottom: 4px;\n" +
                    "   \n" +
                    "    text-shadow: 1px 1px 1px #262627a3;\n" +
                    "    border-radius: 4px;\n" +
                    "    text-align: center;\n" +
                    "}\n" +
                    "\n" +
                    ".form-control { \n" +
                    "-webkit-box-shadow: none;\n" +
                    "-moz-box-shadow: none;\n" +
                    "box-shadow: none;\n" +
                    "padding-left: 10px;\n" +
                    "      padding: 18px 20px;\n" +
                    "    margin-bottom: 4px;\n" +
                    "    border-width: 1px;\n" +
                    "  \n" +
                    "    min-height: auto;\n" +
                    "}\n" +
                    "\n" +
                    ".form-control:focus { \n" +
                    "-webkit-box-shadow: none;\n" +
                    "-moz-box-shadow: none;\n" +
                    "box-shadow: none;\n" +
                    "}" +
                    ".za_form_msg.error{\n" +
                    "    background-color: #e60603;\n" +
                    "}" +
                    " .za_form_msg {\n" +
                    "    display: none;\n" +
                    "    background-color: #f3f3f3;\n" +
                    "    padding: 5px 8px;\n" +
                    "    margin: 12px 0px;\n" +
                    "    color: #fff;\n" +
                    "    text-align: center;\n" +
                    "}" +
                    ".za_form_msg.success {\n" +
                    "    background-color: #00d439;\n" +
                    "}</style>";
                var html = '<form class="form-inline" id="restrict-shipping-form" onsubmit="return  validateZipCodes(event)" >\n' +
                    '  <label for=""><img src="https://zipcode-validator.joboapps.com/img/land.ico" alt="Check Product Availability At Location" style=" width: 25px; vertical-align: middle;" loading="lazy"> <span id="heading_label_span">Check availability at</span></label>\n' +
                    '  <p></p>\n' +
                    '  <input class="form-control" type="text" name="zipCode" id="zipCode" required placeholder="Enter ZipCode" >\n' +
                    '  <button  class="btn btn-primary btn-small btn-inline" type="submit" id="restrict_shipping_btn">Check</button>\n' +
                    '<div class="sub_heading" style="color:red; font-weight: bolder">Check product delivery at your location to enable Add to Cart.</div>' +

                    '</form>' +
                    '<div id="jobo_success" class="za_form_msg success" style="display: none;"> Awesome! Delivery is available at your zip code.</div>' +
                    '<div id="jobo_error" class="za_form_msg error" style="display: none;">Fiddlesticks! We don’t deliver to this location yet.</div>' +

                document.head.insertAdjacentHTML('beforeend', styleSheet);
                $(html).insertAfter(response['data'].product_page_selector);
                product_page_selector_main = response['data'].product_page_selector;
                $(response['data'].product_page_selector).hide();


            }
            if(response['data'].cart_status == 1 && pathname=="/cart"){
                var styleSheet = "<style>#restrict_shipping_btn.shaker {\n" +
                    "    animation: shake 0.3s;\n" +
                    "    /* When the animation is finished, start again */\n" +
                    "    animation-iteration-count: 1; //single shake \n" +
                    "}\n" +
                    "\n" +
                    "@keyframes shake {\n" +
                    "    0% {\n" +
                    "        transform: translate(1px, 1px) rotate(0deg);\n" +
                    "}\n" +
                    "    10% {\n" +
                    "        transform: translate(-1px, -2px) rotate(-1deg);\n" +
                    "}\n" +
                    "    20% {\n" +
                    "        transform: translate(-3px, 0px) rotate(1deg);\n" +
                    "}\n" +
                    "    30% {\n" +
                    "        transform: translate(3px, 2px) rotate(0deg);\n" +
                    "}\n" +
                    "    40% {\n" +
                    "        transform: translate(1px, -1px) rotate(1deg);\n" +
                    "}\n" +
                    "    50% {\n" +
                    "        transform: translate(-1px, 2px) rotate(-1deg);\n" +
                    "}\n" +
                    "    60% {\n" +
                    "        transform: translate(-3px, 1px) rotate(0deg);\n" +
                    "}\n" +
                    "    70% {\n" +
                    "        transform: translate(3px, 1px) rotate(-1deg);\n" +
                    "}\n" +
                    "    80% {\n" +
                    "        transform: translate(-1px, -1px) rotate(1deg);\n" +
                    "}\n" +
                    "    90% {\n" +
                    "        transform: translate(1px, 2px) rotate(0deg);\n" +
                    "}\n" +
                    "    100% {\n" +
                    "        transform: translate(1px, -2px) rotate(-1deg);\n" +
                    "}\n" +
                    "}.form-control {\n" +
                    "  display: inline-block;\n" +
                    "  vertical-align: middle;\n" +
                    "  width: auto;\n" +
                    "  height: 34px;\n" +
                    "  padding: 6px 12px;\n" +
                    "  font-size: 13px;\n" +
                    "  line-height: 1.42857143;\n" +
                    "  color: #555555;\n" +
                    "  background-color: #ffffff;\n" +
                    "  background-image: none;\n" +
                    "  border: 1px solid #cccccc;\n" +
                    "  border-radius: 4px;\n" +
                    "  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);\n" +
                    "  -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;\n" +
                    "  transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;\n" +
                    "  -webkit-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  -moz-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  -o-transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "  transition: all border-color ease-in-out .15s, box-shadow ease-in-out .15s ease-out;\n" +
                    "-moz-box-sizing: border-box;\n" +
                    "-webkit-box-sizing: border-box;\n" +
                    "box-sizing: border-box;\n" +
                    "}\n" +
                    ".form-control:focus {\n" +
                    "  border-color: #66afe9;\n" +
                    "  outline: 0;\n" +
                    "  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "  -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "  box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);\n" +
                    "}\n" +
                    ".form-control::-moz-placeholder {\n" +
                    "  color: #999999;\n" +
                    "  opacity: 1;\n" +
                    "}\n" +
                    ".form-control:-ms-input-placeholder {\n" +
                    "  color: #999999;\n" +
                    "}\n" +
                    ".form-control::-webkit-input-placeholder {\n" +
                    "  color: #999999;\n" +
                    "}\n" +
                    "\n" +
                    ".btn {\n" +
                    "  display: inline-block;\n" +
                    "  margin-bottom: 0;\n" +
                    "  font-weight: normal;\n" +
                    "  text-align: center;\n" +
                    "  vertical-align: middle;\n" +
                    "  cursor: pointer;\n" +
                    "  background-image: none;\n" +
                    "  border: 1px solid transparent;\n" +
                    "  white-space: nowrap;\n" +
                    "  padding: 6px 12px;\n" +
                    "  font-size: 14px;\n" +
                    "  line-height: 1.42857143;\n" +
                    "  border-radius: 4px;\n" +
                    "  -webkit-user-select: none;\n" +
                    "  -moz-user-select: none;\n" +
                    "  -ms-user-select: none;\n" +
                    "  user-select: none;\n" +
                    "  color: #ffffff;\n" +
                    "  background-color: #428bca;\n" +
                    "  border-color: #357ebd;\n" +
                    "}\n" +
                    "\n" +
                    "/* additions */\n" +
                    ".btn-inline {\n" +
                    "  height: 36px;\n" +
                    "  border-radius: 0 4px 4px 0;\n" +
                    "  \n" +
                    "   \n" +
                    "    background-color: #43467f;\n" +
                    "    color: #fff;\n" +
                    "    border: 1px solid;\n" +
                    "    cursor: pointer;\n" +
                    "    margin-bottom: 4px;\n" +
                    "   \n" +
                    "    text-shadow: 1px 1px 1px #262627a3;\n" +
                    "    border-radius: 4px;\n" +
                    "    text-align: center;\n" +
                    "}\n" +
                    "\n" +
                    ".form-control { \n" +
                    "-webkit-box-shadow: none;\n" +
                    "-moz-box-shadow: none;\n" +
                    "box-shadow: none;\n" +
                    "padding-left: 10px;\n" +
                    "      padding: 18px 20px;\n" +
                    "    margin-bottom: 4px;\n" +
                    "    border-width: 1px;\n" +
                    "  \n" +
                    "    min-height: auto;\n" +
                    "}\n" +
                    "\n" +
                    ".form-control:focus { \n" +
                    "-webkit-box-shadow: none;\n" +
                    "-moz-box-shadow: none;\n" +
                    "box-shadow: none;\n" +
                    "}" +
                    ".za_form_msg.error{\n" +
                    "    background-color: #e60603;\n" +
                    "}" +
                    " .za_form_msg {\n" +
                    "    display: none;\n" +
                    "    background-color: #f3f3f3;\n" +
                    "    padding: 5px 8px;\n" +
                    "    margin: 12px 0px;\n" +
                    "    color: #fff;\n" +
                    "    text-align: center;\n" +
                    "}" +
                    ".za_form_msg.success {\n" +
                    "    background-color: #00d439;\n" +
                    "}</style>";
                var html = '<form class="form-inline" id="restrict-shipping-form" style="display: grid" onsubmit="return  validateZipCodes(event)" >\n' +
                    '  <label for=""><img src="https://zipcode-validator.joboapps.com/img/land.ico" alt="Check Product Availability At Location" style=" width: 25px; vertical-align: middle;" loading="lazy"> <span id="heading_label_span">Check availability at</span></label>\n' +
                    '  <p></p>\n' +
                    '  <input class="form-control" type="text" name="zipCode" id="zipCode" required placeholder="Enter ZipCode" >\n' +
                    '  <button  class="btn btn-primary btn-small btn-inline" type="submit" id="restrict_shipping_btn">Check</button>\n' +
                    '<div class="sub_heading" style="color:red; font-weight: bolder">Check product delivery at your location to enable Add to Cart.</div>' +

                    '</form>' +
                    '<div id="jobo_success" class="za_form_msg success" style="display: none;"> Awesome! Delivery is available at your zip code.</div>' +
                    '<div id="jobo_error" class="za_form_msg error" style="display: none;">Fiddlesticks! We don’t deliver to this location yet.</div>' +
                    document.head.insertAdjacentHTML('beforeend', styleSheet);

                $(html).insertAfter(response['data'].cart_selector);
                product_page_selector_main = response['data'].cart_selector;
                additional_page_selector_main = response['data'].is_additional_checkout;
                $(response['data'].cart_selector).hide();
                if(response['data'].is_additional_checkout == "1"){
                    $(response['data'].is_additional_checkout).hide();
                }

            }
        }

    });
}

// window.onpaint = preloadFunc();
// function preloadFunc() {
//     // jQuery IS NOT loaded, do stuff here.
//     (function (root) {
//         var ta = document.createElement('script');
//         ta.type = 'text/javascript';
//         ta.async = true;
//         ta.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js';
//         var s = document.getElementsByTagName('script')[0];
//         s.parentNode.insertBefore(ta, s);
//     })(window);
//     alert("PreLoad");
// }
// if(window.jQuery){
//     alert("yes worked");
//     add_scripts();
// }
// else{
//     if(window.jQuery){
//         alert("yes worked");
//     }
//     add_scripts();
// }

(function() {

if (window.jQuery) {
    add_scripts();
}
else {
    var startingTime = new Date().getTime();
    // Load the script
    var script = document.createElement("SCRIPT");
    script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
    script.type = 'text/javascript';
    document.getElementsByTagName("head")[0].appendChild(script);

    // Poll for jQuery to come into existance
    var checkReady = function(callback) {
        if (window.jQuery) {
            callback(jQuery);
        }
        else {
            window.setTimeout(function() { checkReady(callback); }, 20);
        }
    };

    // Start polling...
    checkReady(function($) {
        $(function() {
            var endingTime = new Date().getTime();
            var tookTime = endingTime - startingTime;
            console.log("jQuery is loaded, after " + tookTime + " milliseconds!");
            add_scripts();
        });
    });
}

})();