
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    .modal-header {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .header-box {
    text-align: center;
  }
  
  .brand-logo {
    max-width: 25%;
    margin: 0 auto;
    opacity: 0.7;
    display: block; /* ensures the image does not inline with the title */
  }
  
  .modal-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    color: #4e342e;
    margin-top: 10px; /* spacing between logo and title */
  }
  
  .modal-description{
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    /*dark grey color*/
    color: #616161;
    margin-top: 10px; /* spacing between title and description */
  }

  .close {
    position: absolute;
    right: 15px; /* or your preferred value */
    top: 15px; /* aligns with the top of the modal */
  }
  
  .btn-submit {
    background-color: #4e342e;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
  }
</style>

<!-- Bootstrap Modal -->
<a class="button" id="popupModal" style="visibility:hidden" href="#popup1">Let me Pop up</a>
<div class="modal" id="popup1" tabindex="-1" aria-labelledby="desiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="header-box mx-auto text-center">
                <!-- <img src="images/brand_logo.png" alt="Brand Logo" class="brand-logo"> -->
                <h5 class="modal-title">Become an Insider</h5>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <p class="modal-description">Receive Whatsapp notifications on New Collections and Sale Updates.</p>
            <form id="newUserForm">
              <div class="form-group">
                  <input type="text" id="userName" class="form-control" placeholder="Full Name" name="fullname">
              </div>
              <div class="form-group">
                  <input type="tel" id="userPhone" class="form-control" placeholder="Mobile" name="mobile">
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-center">
              <button id="submit" type="submit" name="newUserSubmit" class="btn btn-submit">Submit</button>
          </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Commented html code for reference -->
<!-- Include Bootstrap CSS if needed 
<div class="modal" id="customPopup" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Custom Popup</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>This is a custom popup from your Laravel app!</p>
      </div>
    </div>
  </div>
</div>

<script>
// JS to trigger the modal
document.addEventListener('DOMContentLoaded', (event) => {
    $('#customPopup').modal('show');
});
</script>
-->