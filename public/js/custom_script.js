// capture click events in the page
var user_data = "";
var baseURL = 'https://almeapp.co.in/';
document.addEventListener('click', async function(event) {
    // create a token identifier for this session and store in browser local storage
    var alme_user_token = localStorage.getItem('alme_user_token');
    if (!alme_user_token) {
        alme_user_token = Math.random().toString(36).substring(2);
        localStorage.setItem('alme_user_token', alme_user_token);
    }

    // create a session identifier for this session and remove it after 30 minutes
    var session = localStorage.getItem('session');
    if (!session) {
        session = Math.random().toString(36).substring(2);
        localStorage.setItem('session', session);
        setTimeout(function() {
            localStorage.removeItem('session');
        }, 30 * 60 * 1000);
    }

    var cust_email = '{{ customer.email }}'
    var cust_id = '{{ customer.id }}'
    var current_user = meta.product.id;
    var user_login =cust_email;
    var user_id =cust_id;
    var user_regd = "";

    // extract product details if any
    product_id = meta.product.id;
    product_name = meta.product.variants[0].name;
    product_price = meta.product.variants[0].price;
    product_category = meta.product.type;

    // create a epoch timestamp
    var timestamp = Math.floor(Date.now() / 1000);

    var event_type = 'click';
    var event_name = '';
    // get the element text that was clicked
    var element_text = event.target.innerText;
    // get the url
    var url = window.location.href;
    var appName = "test_shopify";
    // push the event to a stack
    var event = {
        'token': alme_user_token,
        'session': session,
        'user_login': user_login,
        'user_id': user_id,
        'user_regd': user_regd,
        'click_time': timestamp,
        'click_text': element_text,
        'event_type': event_type,
        'event_name': event_name,
        'source_url': url,
        'app_name': appName,
        'product_id': product_id,
        'product_name': product_name,
        'product_price': product_price,
        'product_category': product_category,
    };
    // Intiailize events array if not already done
    events = JSON.parse(localStorage.getItem("events") || "[]");
    events.push(event);
    localStorage.setItem("events", JSON.stringify(events));

    var isNew = localStorage.getItem('new_almee_user');
    console.log('isNew ' + isNew);
    await handleShowingPopup();
    if ((isNew !== false && isNew !== 'false') || isNew == 'true' || isNew === null) {
        localStorage.setItem('new_almee_user', 'false');
        
    } else {
        localStorage.setItem('new_almee_user', 'false');
        localStorage.setItem('alme_contact_popupDisplayed', 'true');
    }
    
    // send event to server in gap of 10 seconds
    setTimeout(function() {
        var events = localStorage.getItem('events');
        if (events) {
            events = JSON.parse(events);
            localStorage.removeItem('events');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://almeapp.com/events/', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(events));
        }
    },10000);
    
});

async function handleShowingPopup(){
    // Define HTML and CSS
    var popupHTML = null;
    var code = null;

    let obj;

    const res = await fetch(baseURL+'theme_popups?shop='+Shopify.shop);

    obj = await res.json();

    console.log('outside');
    console.log(obj)
    code = obj.code;
    popupHTML = obj.html;

    // Insert HTML
    var el = document.getElementById('newUserForm');
    if(el == null || el.length < 1) {
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        document.getElementById('popupModal').click();
        handleFormSubmission(code)
    }
}

// Function to handle form submission
function handleFormSubmission(code = null) {
  
    document.getElementById('newUserForm').addEventListener('submit', function(event) { 
        //alert('submitting');
        event.preventDefault();
        var name = document.getElementById('userName').value;
        var phone = document.getElementById('userPhone').value;    
        var alme_user_token = localStorage.getItem('alme_user_token');
        var newUserDetails = {
            name: name,
            phone: phone,
            alme_user_token: alme_user_token,
            app_name: 'desi_sandook',
        };
  
        // Send data to server
        fetch('https://almeapp.com/notification/submit_contact/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(newUserDetails),
        }).then(function(response) {
            if (response.ok) {
                if(code !== null) {
                    console.log('here code '+code);
                    document.getElementById('popupModal').innerHTML = '<center>Thanks for submitting! Use the discount code <b>'+code+'</b> to get 10% off your order!</center>'
                } else {
                    document.getElementById('popupModal').style.display = 'none';
                }
                localStorage.setItem('alme_contact_popupDisplayed', 'true');
                console.log('Form submitted successfully');
            }
        }).catch(function(error) {
            console.log(error);
        });
        
    });
  }
      

// Function to handle close button click
function handleCloseButtonClick() {

    // Ensure the element is available in the DOM
    let closeBtn = document.getElementById('closeBtn');

    if (closeBtn) {
    closeBtn.addEventListener('click', function(event) {
        console.log('Handling close button click');
        event.stopPropagation();
        document.getElementById('newUserPopup').style.display = 'none';
        localStorage.setItem('alme_contact_popupDisplayed', 'true');
    });
    } else {
    console.error('Close button not found in DOM');
    }
}

// Check if user is new and if popup has not been displayed for this user
window.onload = async function() {
    var alme_user_token = localStorage.getItem('alme_user_token');
    if (!alme_user_token) {
        alme_user_token = Math.random().toString(36).substring(2);
        localStorage.setItem('alme_user_token', alme_user_token);
    }
      var popupDisplayed = localStorage.getItem('alme_contact_popupDisplayed');
      if (popupDisplayed === 'true') {
        return;
      } else {
        var isNew = localStorage.getItem('new_alme_user') === 'true';
        if (isNew) {
          setTimeout(function(){
            document.getElementById('newUserPopup').style.display = 'block';
            handleFormSubmission();
            handleCloseButtonClick();
        }, 20000);}
  
        else {
          localStorage.setItem('alme_contact_popupDisplayed', 'true');
        }
      }
};

// capture  page load events
document.addEventListener('DOMContentLoaded', function(event) {
    // create a token identifier for this session and store in browser local storage
    var alme_user_token = localStorage.getItem('alme_user_token');
    if (!alme_user_token) {
        alme_user_token = Math.random().toString(36).substring(2);
        localStorage.setItem('alme_user_token', alme_user_token);
    }

    // create a session identifier for this session and remove it after 30 minutes
    var session = localStorage.getItem('session');
    if (!session) {
        session = Math.random().toString(36).substring(2);
        localStorage.setItem('session', session);
        setTimeout(function() {
            localStorage.removeItem('session');
        }, 30 * 60 * 1000);
    }

    // extract product details if any
    product_id = meta.product.id;
    product_name = meta.product.variants[0].name;
    product_price = meta.product.variants[0].price;
    product_category = meta.product.type;

    var cust_email = '{{ customer.email }}'
    var cust_id = '{{ customer.id }}'
    var current_user = meta.product.id;
    var user_login =cust_email;
    var user_id =cust_id;
    var user_regd = "";



    // create a epoch timestamp
    var timestamp = Math.floor(Date.now() / 1000);

    var event_type = 'page_load';
    var event_name = 'page_load';
    // get the element text that was clicked
    var element_text = event.target.innerText;
    // get the url
    var url = window.location.href;
    var appName = "test_shopify";
    // push the event to a stack
    var event = {
        'token': alme_user_token,
        'session': session,
        'user_login': user_login,
        'user_id': user_id,
        'user_regd': user_regd,
        'click_time': timestamp,
        'click_text': element_text,
        'event_type': event_type,
        'event_name': event_name,
        'source_url': url,
        'app_name': appName,
        'product_id': product_id,
        'product_name': product_name,
        'product_price': product_price,
        'product_category': product_category,
    };
    // Intiailize events array if not already done
    events = JSON.parse(localStorage.getItem("events") || "[]");
    events.push(event);
    localStorage.setItem("events", JSON.stringify(events));

    // send event to server in gap of 10 seconds
    setTimeout(function() {
        var events = localStorage.getItem('events');
        if (events) {
            events = JSON.parse(events);
            localStorage.removeItem('events');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://almeapp.com/events/', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(events));
        }
    },10000);
});

/*
home_url = window.location.origin;
var ajax_url = home_url + "/wp-json/alme/v1/data";



// define a function to make ajax call to get the user data
async function get_user_rack_data() {
    base_url = 'https://almeapp.com';

    //alme_user_token = 'garbage_change_this';
    alme_user_token = localStorage.getItem('alme_user_token');
    var max_items = 5;
    // most_visited and visits
    most_visited_url = base_url + '/api/most_visited/'+ '?max_items='+max_items;
    visited_for_user = base_url+'/api/test_visits/' + '?token='+alme_user_token+'&max_items='+max_items;

    // most carted and carts
    most_carted_url = base_url + '/api/test_carts/'+ '?max_items='+max_items;
    carted_for_user = base_url+'/api/carts/' + '?token='+alme_user_token+'&max_items='+max_items;

    // make async calls to all the above urls and store the response in variables
    try{
        results = await Promise.all([
            fetch(most_visited_url),
            fetch(visited_for_user),
            fetch(most_carted_url),
            fetch(carted_for_user),
        ]);
        api_response = await Promise.all(results.map(r => r.json()));
        user_data = {
            'most_viewed': api_response[0],
            'viewed_for_user': api_response[1],
            'most_carted': api_response[2],
            'carted_for_user': api_response[3]
        };

        // append user token to the response
        user_data['user_token'] = alme_user_token;
        // send consolidated response to the endpoint

    }
    catch(error) {
        console.log(error);
    }

}


function checkAndUpdateData() {
    var last_data_call = localStorage.getItem('last_data_call');
    var currentTime = Math.floor(Date.now() / 1000);

    if (!last_data_call || (currentTime - last_data_call) >= (5 * 60)) {
        last_data_call = currentTime;
        localStorage.setItem('last_data_call', last_data_call);
        get_user_rack_data();
    }

}
// get_user_rack_data();
checkAndUpdateData();
setInterval(checkAndUpdateData, 5 * 60 * 1000);
*/




















window.shopUrl = '{{ request.origin }}';
window.routes = {
    cart_add_url: '{{ routes.cart_add_url }}',
    cart_change_url: '{{ routes.cart_change_url }}',
    cart_update_url: '{{ routes.cart_update_url }}',
    cart_url: '{{ routes.cart_url }}',
    predictive_search_url: '{{ routes.predictive_search_url }}',
};

window.cartStrings = {
    error: `{{ 'sections.cart.cart_error' | t }}`,
    quantityError: `{{ 'sections.cart.cart_quantity_error_html' | t: quantity: '[quantity]' }}`,
};

window.variantStrings = {
    addToCart: `{{ 'products.product.add_to_cart' | t }}`,
    soldOut: `{{ 'products.product.sold_out' | t }}`,
    unavailable: `{{ 'products.product.unavailable' | t }}`,
    unavailable_with_option: `{{ 'products.product.value_unavailable' | t: option_value: '[value]' }}`,
};

window.accessibilityStrings = {
    imageAvailable: `{{ 'products.product.media.image_available' | t: index: '[index]' }}`,
    shareSuccess: `{{ 'general.share.success_message' | t }}`,
    pauseSlideshow: `{{ 'sections.slideshow.pause_slideshow' | t }}`,
    playSlideshow: `{{ 'sections.slideshow.play_slideshow' | t }}`,
};
