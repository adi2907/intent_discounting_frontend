if (document.readyState !== 'loading') {
    var page_load_event = {target: {innerText: ''}};
    logEvent('page_load', 'page_load', page_load_event);

} else {
    document.addEventListener('DOMContentLoaded', function (event) {
        logEvent('page_load', 'page_load', event);
    });
}

function createUserToken(){
    var timestamp = Date.now().toString().slice(-5);
    var random = Math.random().toString(36).substring(2, 8);
    return timestamp + random;
}

async function handleShowingPopup(){
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
    console.log('el '+el);
    if(popupHTML !== null && (el == null || el.length < 1)) {
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        document.getElementById('popupModal').click();
        handleFormSubmission(code)
    }
}

// Function to handle form submission
function handleFormSubmission(code = null) {
  
    document.getElementById('newUserForm').addEventListener('submit', function(event) { 
        console.log('submitting form')
        event.preventDefault();
        var name = document.getElementById('userName').value;
        var phone = document.getElementById('userPhone').value;    
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
                    document.getElementById('popup1').innerHTML = '<center>Thanks for submitting! Use the discount code <b>'+code+'</b> to get 10% off your order!</center>'
                } else {
                    document.getElementById('popup1').style.display = 'none';
                }
                localStorage.setItem('alme_contact_popupDisplayed', 'true');
                console.log('Form submitted successfully');
            }
        }).catch(function(error) {
            console.log(error);
        }).finally(function() {
            // Hide the popup after form submission attempt
            document.getElementById('popup1').style.display = 'none';
            localStorage.setItem('alme_contact_popupDisplayed', 'true');
        });
    });
  }



// Function to handle close button click
function handleCloseButtonClick() {
    // Ensure the element is available in the DOM
    let closeBtn = document.getElementById('closeBtn');
    console.log('Closing button')
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

setInterval(sendEventsToServer, 10000);
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
        product_price = meta.product.variants[0].price;

        var cartContents = fetch(window.Shopify.routes.root + 'cart.js')
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


document.addEventListener('click', async function(event) {
    logEvent('click', '', event);
    await handleShowingPopup();
});

