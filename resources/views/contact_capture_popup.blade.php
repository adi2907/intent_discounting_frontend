
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



        <a class="button" id="popupModal" style="visibility:hidden" href="#newUserPopup">Let me Pop up</a>
        <div style="font-family: Arial, sans-serif; height: 100vh;" id="newUserPopup" class="overlay">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="header-box mx-auto text-center">
                            <img src="images/brand_logo.png" alt="Brand Logo" class="brand-logo">
                            <h5 class="modal-title">{{$settings['title']}}</h5>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeBtn">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <div class="modal-body text-center">
                    <p class="modal-description">{{$settings['description']}} </p>
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
                    <button type="submit" class="btn btn-submit" id="submitBtn">Submit</button>
                </div>
                </div>
            </div>
        </div>
    </body>