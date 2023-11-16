
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    .overlay {
    display: none; /* Hide by default */
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1;
}

.modal {
    background: white;
    margin: 10% auto;
    padding: 20px;
    width: 50%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand-logo {
    width: 50px; /* Adjust as needed */
}

.close {
    cursor: pointer;
}

.form-group {
    margin-bottom: 10px;
}

input[type="text"],
input[type="tel"] {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    box-sizing: border-box;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

/* Additional styles as needed */

</style>


<!-- <a class="button" id="popupModal" href="#popup1">Let me Pop up</a>
<div class="overlay" id="popup1" style="font-family: Arial, sans-serif; height: 100vh;">
    <div class="modal">
        <div class="modal-header">
            <img src="images/brand_logo.png" alt="Brand Logo" class="brand-logo">
            <h5>Become an Insider</h5>
            <button type="button" class="close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Receive Whatsapp notifications on New Collections and Sale Updates.</p>
            <form id="newUserForm">
                <div class="form-group">
                    <input type="text" id="userName" placeholder="Full Name" name="fullname">
                </div>
                <div class="form-group">
                    <input type="tel" id="userPhone" placeholder="Mobile" name="mobile">
                </div>
                <button id="submit" type="submit" name="newUserSubmit">Submit</button>
            </form>
        </div>
    </div>
</div> -->



<a class="button" id="popupModal" style="visibility:hidden" href="#popup1">Let me Pop up</a>
<div style="font-family: Arial, sans-serif; height: 100vh;" id="popup1" class="overlay">
    <div class="popup">
        <h2>Please give us your information</h2>
        <a class="close" href="#">&times;</a>
        <div class="content">
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
        </div>
    </div>
</div>  
