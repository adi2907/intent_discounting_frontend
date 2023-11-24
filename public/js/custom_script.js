
var submit_contact = true; // needs to be set by admin
var CONTACT_POPUP_TIME = 20000; // 20 seconds


// create user token and store in local storage
// if submit_contact is true then set show popup after 20 seconds
function createUserToken(){
    var timestamp = Date.now().toString().slice(-5);
    var random = Math.random().toString(36).substring(2, 8);
    var token = timestamp + random;
    localStorage.setItem('alme_user_token', token);
    

    // if submit_contact is true then set a timeout of 20 seconds to show popup
    if (submit_contact){
        if (localStorage.getItem('alme_contact_popupDisplayed') == 'true') {
            return;
        }
        setTimeout(function(){
            handleShowingPopup();
            localStorage.setItem('alme_contact_popupDisplayed', 'true');
        }, CONTACT_POPUP_TIME);
       
    }
}

// Log page load

if (document.readyState !== 'loading') {
    var page_load_event = {target: {innerText: ''}};
    logEvent('page_load', 'page_load', page_load_event);

} else {
    document.addEventListener('DOMContentLoaded', async function (event) {
        await logEvent('page_load', 'page_load', event);
    });
}

// Log clicks
document.addEventListener('click', async function(event) {
await logEvent('click', '', event);
await handleShowingPopup();
});



function sendEventsToServer() {
    var alme_user_token = localStorage.getItem('alme_user_token');
    var lastEventTimestamp = localStorage.getItem('lastEventTimestamp');
    if (!alme_user_token) {
        createUserToken();
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

setInterval(sendEventsToServer, 10000);
async function logEvent(event_type, event_name, event) {
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
        product_price = meta.product.variants[0].price;

        var cartContents = await fetch(window.Shopify.routes.root + 'cart.js')
        .then(response => response.json())
        .then(data => { return data });
        console.log('Contents here');
        console.log(cartContents);
        /*
        var hiddenInputId = 'almetoken';
        var alme_user_token = localStorage.getItem('alme_user_token');
        var domInput = document.getElementById(hiddenInputId);
        var elExists = domInput !== null && domInput.length;
        if(!elExists && alme_user_token !== null && alme_user_token.length > 0) {
            const formElement = document.querySelector('[data-type="add-to-cart-form"]');
            var hiddenInput = document.createElement("input");
            hiddenInput.setAttribute("type", "hidden");
            hiddenInput.setAttribute("name", "properties[ALMETOKEN]");
            hiddenInput.setAttribute("value", alme_user_token);
            //append to form element that you want .
            formElement.appendChild(hiddenInput);
        }
        */
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
        'app_name': Shopify.shop,
        'product_id': product_id,
        'product_name': product_name,
        'product_price': product_price,
        'product_category': product_category,
    };
    var events = JSON.parse(localStorage.getItem("events") || "[]");
    events.push(eventDetails);
    localStorage.setItem("events", JSON.stringify(events));
}


async function handleShowingPopup(){
    if (localStorage.getItem('alme_contact_popupDisplayed') == 'true') {
        return;
    }
    // Define HTML and CSS
    var popupHTML = null;
    var code = null;

    let obj;
    var baseURL = 'https://almeapp.co.in/';
    const res = await fetch(baseURL+'theme_popups?shop='+Shopify.shop);
    obj = await res.json();
   
    code = obj.code;
    popupHTML = obj.html;

    // Insert HTML
    var el = document.getElementById('newUserForm');
    if(popupHTML !== null && (el == null || el.length < 1)) {
        console.log('Inserting HTML')
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        document.getElementById('popupModal').click();
        handleFormSubmission(code);
        handleCloseButtonClick();
    }
}

// Function to handle form submission
function handleFormSubmission(code = null) {
  
    document.getElementById('submitBtn').addEventListener('click', function(event) { 
        console.log('submitting form')
        event.preventDefault();
        var name = document.querySelector('[name="fullname"]').value;
        var phone = document.querySelector('[name="mobile"]').value;
        var alme_user_token = localStorage.getItem('alme_user_token');
        var newUserDetails = {
            name: name,
            phone: phone,
            alme_user_token: alme_user_token,
            app_name: Shopify.shop,
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
                    document.getElementById('newUserPopup').innerHTML = '<center>Thanks for submitting! Use the discount code <b>'+code+'</b> to get 10% off your order!</center>'
                } else {
                    document.getElementById('newUserPopup').style.display = 'none';
                }
                //localStorage.setItem('alme_contact_popupDisplayed', 'true');
                console.log('Form submitted successfully');
            }
        }).catch(function(error) {
            console.log(error);
        }).finally(function() {
            console.log('finally')
            // Hide the popup after form submission attempt
            document.getElementById('newUserPopup').style.display = 'none';
            //localStorage.setItem('alme_contact_popupDisplayed', 'true');
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
        //localStorage.setItem('alme_contact_popupDisplayed', 'true');
    });
    } else {
    console.error('Close button not found in DOM');
    }
}


