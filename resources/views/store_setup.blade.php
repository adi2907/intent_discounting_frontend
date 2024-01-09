<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alme setup</title>
<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
<style>
    .modal-dialog {
        max-width: 80%;
        margin: 30px auto;
    }
    .modal-content {
        position: relative;
        overflow: hidden; /* This will contain the absolute footer */
    }
    .modal-header, .modal-footer {
        justify-content: center; /* Center the items in header and footer */
    }
    .modal-body {
        max-height: 75vh;
        overflow-y: auto;
        padding-bottom: 60px; /* Make space for the 'Customize' button */
    }
    .modal-footer {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        background: white;
    }
    .img-fluid {
        max-height: 65vh;
        width: auto;
        display: block;
        margin: 0 auto;
    }
    .close {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 1050;
    }
</style>
</head>
<body>
  <div class="container">
    <div class="row" style="margin-top:40px !important">
      <div class="card" >
        <div class="card-title mt-4 text-center">
          <a href="{{$url}}" target="_blank" class="btn btn-primary">Go to theme editor</a>
          <a href="{{url()->previous()}}" class="btn btn-danger">Back</a>
        </div>
        <div class="card-body">
          <p class="mt-1"><b>Step 1:</b> Please click on "Customize Button". To add <b>Home page recommendations</b>, select Home page from the top and click on "Add block" under Apps from the left and add the Alme app block which you want to show on your Home page.</p>
          <img src="{{asset('images/homepage.png')}}" class="img-fluid" alt="Responsive image">
          <p><p><p></p>
          <p class="mt-4"><b>Step 2:</b> To add <b>Product page recommendations </b>, go to Home page on top center and select "Default product" from the dropdown</p>
          <img src="{{asset('images/product nav.png')}}" class="img-fluid" alt="Responsive image">
          <p><p><p></p>
          <p class="mt-4"><b>Step 3:</b> Now click on "Add block" under Apps from the left and add the Alme app block which you want to show on your Product page.</p>
          <img src="{{asset('images/product page.png')}}" class="img-fluid" alt="Responsive image">
          <p><p><p></p>
          <p class="mt-4"><b>Step 4:</b> To enable <b>smart notifications</b>, you will need to enable Alme script in app embeds. Go to "App embeds" on top left and toggle "Alme Script" to on</p>
          <img src="{{asset('images/app embed.png')}}" class="img-fluid" alt="Responsive image">
        </div>
        <div class="card-footer text-center mb-4">
          <a href="{{$url}}" target="_blank" class="btn btn-primary">Go to theme editor</a>
          <a href="{{url()->previous()}}" class="btn btn-danger">Back</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
