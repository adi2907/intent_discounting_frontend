<html>
    <head>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1050;
            }
            .modal-content {
                background: white;
                border-radius: 5px;
                text-align: center;
                padding: 20px;
                font-family: Arial, sans-serif;
                max-width: 400px; /* Set the max width for the popup */
                width: 100%; /* Use the full width up to the max width */
                margin: auto; /* Center the modal horizontally */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: added shadow for better visibility */
            }
            .title {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .subtitle {
                font-size: 18px;
                margin-bottom: 20px;
            }
            .discount-code {
                display: block;
                background: #4e342e;
                color: white;
                padding: 10px 20px;
                font-size: 20px;
                font-weight: bold;
                margin: 0 auto;
                width: fit-content;
                border-radius: 5px;
                box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            }
            .close {
                position: absolute;
                right: 15px;
                top: 15px;
                font-size: 30px;
                cursor: pointer;
            }
            /* Responsive adjustments */
            @media (max-width: 450px) {
                .modal-content {
                    max-width: 95%; /* Allow for padding and borders on smaller screens */
                    padding: 10px;
                }
                .title, .subtitle {
                    font-size: 16px; /* Adjust font size for smaller screens */
                }
                .discount-code {
                    font-size: 18px; /* Adjust font size for smaller screens */
                }
            }
        </style>

    </head>
    <body>
        <div id="saleNotificationPopup" class="overlay">
        <div class="modal-content">
            <span class="close" id="saleCloseBtn">&times;</span>
            <div class="title">You are a regular visitor</div>
                <div class="subtitle">
                    Here's a special discount of {{ $discountValue }}% applicable on entire site<br>
                    Valid only for {{ $discountExpiry }} hours
                </div>
                <span id="discountAlmeCode" code="{{ $discountCode }}" class="discount-code" style="cursor:pointer" onclick="copyUri(event)">{{ $discountCode }}</span>
            </div>
        </div>
        <script>
            function copyURI(evt) {
                evt.preventDefault();
                navigator.clipboard.writeText(evt.target.getAttribute('code')).then(() => {
                    /* clipboard successfully set */
                    document.getElementById('discountAlmeCode').innerHTML('Copied');
                    }, () => {
                    /* clipboard write failed */
                });
            }
        </script>
    </body>
</html>
