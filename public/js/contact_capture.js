// Define HTML and CSS
var popupHTML = `
  <div id="newUserPopup">
    <div class="row justify-content-center align-items-center vh-100">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="modal-content position-relative">
          <button id="closeBtn" type="button" class="close position-absolute" style="right: 15px; top: 10px;" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="modal-body px-4 py-3">
            <img src="https://desisandook.in/wp-content/themes/desisandook/images/logo.svg" alt="Desisandook" class="img-fluid mb-3 mx-auto d-block">
            <h5 class="text-center mb-3">Become a Dil se Desi Member</h5>
            <p class="text-center mb-4">Receive Whatsapp notifications on New Collections, Live Shows and Sale Updates.</p>
            <form id="newUserForm">
              <div class="form-group">
                <label for="userName">Full Name</label>
                <input id="userName" type="text" name="fullName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="userPhone">Mobile</label>
                <input id="userPhone" type="number" name="phone" class="form-control">
              </div>
              <button id="submit" type="submit" name="newUserSubmit" class="add-cart-cta">Submit</button>
            </form>
            <div class="new-customer-capture">
              Thanks for submitting the details.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
`;



// Insert HTML
document.body.insertAdjacentHTML('beforeend', popupHTML);

// Insert CSS
// var style = document.createElement('style');
// style.innerHTML = popupCSS;
// document.head.appendChild(style);

// Function to handle form submission
function handleFormSubmission() {
    
    document.getElementById('newUserForm').addEventListener('submit', function(event) { 
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
                document.getElementById('newUserPopup').style.display = 'none';
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