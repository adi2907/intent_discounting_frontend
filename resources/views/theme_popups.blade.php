
<!-- <style>
    h1 {
        text-align: center;
        font-family: Tahoma, Arial, sans-serif;
        color: #06D85F;
        margin: 80px 0;
    }
    .box {
        width: 40%;
        margin: 0 auto;
        background: rgba(255,255,255,0.2);
        padding: 35px;
        border: 2px solid #fff;
        border-radius: 20px/50px;
        background-clip: padding-box;
        text-align: center;
    }
    
    .button {
        font-size: 1em;
        padding: 10px;
        color: #fff;
        border: 2px solid #06D85F;
        border-radius: 20px/50px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease-out;
    }
    .button:hover {
        background: #06D85F;
    }
    .overlay {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        transition: opacity 500ms;
        visibility: hidden;
        opacity: 0;
    }
    .overlay:target {
        visibility: visible;
        opacity: 1;
    }
    
    .popup {
        margin: 70px auto;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        width: 30%;
        position: relative;
        transition: all 5s ease-in-out;
    }
    
    .popup h2 {
        margin-top: 0;
        color: #333;
        font-family: Tahoma, Arial, sans-serif;
    }
    .popup .close {
        position: absolute;
        top: 20px;
        right: 30px;
        transition: all 200ms;
        font-size: 30px;
        font-weight: bold;
        text-decoration: none;
        color: #333;
    }
    .popup .close:hover {
        color: #06D85F;
    }
    .popup .content {
        max-height: 30%;
        overflow: auto;
    }
    
    @media screen and (max-width: 700px){
        .box{
        width: 70%;
        }
        .popup{
        width: 70%;
        }
    }
</style> -->
<!-- <a class="button" id="popupModal" style="visibility:hidden" href="#popup1">Let me Pop up</a>
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
</div> -->
<html>
    <head>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

        <style>
            .overlay {
            position: fixed; /* Use fixed positioning */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex; /* Use flexbox to center the child elements */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
            z-index: 1050;
            }
            .modal-dialog-centered {
            /* Add this class to center the modal dialog */
            display: flex;
            align-items: center;
            justify-content: center;
        }
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
    </head>
    <body>



        <a class="button" id="popupModal" style="visibility:hidden" href="#popup1">Let me Pop up</a>
        <div style="font-family: Arial, sans-serif; height: 100vh;" id="popup1" class="overlay">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="header-box mx-auto text-center">
                            <img src="images/brand_logo.png" alt="Brand Logo" class="brand-logo">
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
                        <input type="text" class="form-control" placeholder="Full Name" name="fullname">
                    </div>
                    <div class="form-group">
                        <input type="tel" class="form-control" placeholder="Mobile" name="mobile">
                    </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-submit">Submit</button>
                </div>
                </div>
            </div>
        </div>
    </body>