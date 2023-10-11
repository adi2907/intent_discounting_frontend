<html>
<head>
    <meta charset=”UTF-8”>
    <meta name=”viewport” content=”width=device-width, initial-scale=1.0”>
    <meta http-equiv=”X-UA-Compatible” content=”ie=edge”>
    <title>Restrictions</title>

<!--    <link rel="stylesheet" media="screen" href="--><?php //echo SHOPIFY_APP_URL; ?><!--/assets/css/polaris.css" />-->
    <link rel="stylesheet" href="https://sdks.shopifycdn.com/polaris/1.9.1/polaris.min.css" />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="https://unpkg.com/@shopify/app-bridge@2"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<style>
    a:not([data-polaris-unstyled]):active, a:not([data-polaris-unstyled]):focus, a:not([data-polaris-unstyled]):hover{
        text-decoration: none;
    }
    a:not([data-polaris-unstyled]){
        color: black;
        font-weight: bold;
        margin-right: 1%;
    }
    .header_menu_desktop{
        width: 100%;
    }
</style>
<body class="comman-polaris-css">
    @yield('content')
    @yield('scripts')
</body>
</html>