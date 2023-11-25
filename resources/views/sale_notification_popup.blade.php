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
                background: #d2a679;
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
        </style>
    </head>
    <body>
        <div id="saleNotificationPopup" class="overlay">
            <div class="modal-content">
                <span class="close" id="closeBtn">&times;</span>
                <div class="title">You are a regular visitor</div>
                <div class="subtitle">Try out our collection<br>Get 20% off on your first purchase</div>
                <span class="discount-code">20PERCENT</span>
            </div>
        </div>
    </body>
</html>
