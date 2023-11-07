function createUserToken(){
    var timestamp = Date.now().toString().slice(-5);
    var random = Math.random().toString(36).substring(2, 8);
    return timestamp + random;
}

function sendEventsToServer() {
    var alme_user_token = localStorage.getItem('alme_user_token');
    var lastEventTimestamp = localStorage.getItem('lastEventTimestamp');
    if (!alme_user_token) {
        alme_user_token = createUserToken();
        localStorage.setItem('alme_user_token', alme_user_token);
    }
    
    var events = localStorage.getItem('events');
    if (events) {
        events = JSON.parse(events);
        var session_id = localStorage.getItem('session_id') || "";
        var dataToSend = {
            events: events,
            session_id: session_id,
            alme_user_token: alme_user_token,
            lastEventTimestamp: lastEventTimestamp
        };
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'https://almeapp.com/events/', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        // if response is 200 then store session id in local storage
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var responseData = JSON.parse(this.responseText);
                if (responseData && responseData.session_id) {
                    localStorage.setItem('session_id', responseData.session_id);
                }
            }
        };
        // send timestamp and session id alongwith events
        xhr.send(JSON.stringify(dataToSend));
        // get the last event timestamp store locally, remove events
        var lastEventTimestamp = events[events.length - 1].click_time;
        localStorage.setItem('lastEventTimestamp', lastEventTimestamp);
        localStorage.removeItem('events');
    }
}

setInterval(sendEventsToServer, 5000);
function logEvent(event_type, event_name, event) {
    var cust_email = '{{ customer.email }}'
    var cust_id = '{{ customer.id }}'
    var product_id = null;
    var product_name = null;
    var product_price = null;
    var product_category = null;
    if(meta.product) {
        product_id = meta.product.id;
        product_name = meta.product.variants[0].name
        product_category = meta.product.type
        product_price = meta.product.variants[0].price
    } 
    
    var timestamp = Math.floor(Date.now() / 1000);
    var eventDetails = {
        'user_login': cust_email,
        'user_id': cust_id,
        'user_regd': "",
        'click_time': timestamp,
        'click_text': event.target.innerText,
        'event_type': event_type,
        'event_name': event_name,
        'source_url': window.location.href,
        'app_name': "test_shopify",
        'product_id': product_id,
        'product_name': product_name,
        'product_price': product_price,
        'product_category': product_category,
    };
    var events = JSON.parse(localStorage.getItem("events") || "[]");
    events.push(eventDetails);
    localStorage.setItem("events", JSON.stringify(events));
}

document.addEventListener('click', function(event) {
    logEvent('click', '', event);
    console.log('CLick captured');
});

document.addEventListener('DOMContentLoaded', function(event) {
    console.log('Event page loaded');
    logEvent('page_load', 'page_load', event);
});

/*
home_url = window.location.origin;
var ajax_url = home_url + "/wp-json/alme/v1/data";
*/