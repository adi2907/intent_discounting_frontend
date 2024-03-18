@extends('layouts.new_app')
@section('css')
    <link rel="stylesheet" href="{{asset('css/identified_users.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection
@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Identified Users</h1>
                <i class="fas fa-user"></i>
            </div>
        </section>
        <section class="main-content mt-3 mr-3"  style="background-color: white;" >
            <div class="button-group d-flex justify-content-end mt-3 mb-3 mr-3">
                <label for="" class="mt-3">Select Date: </label><input id="date-range" class="form-control mt-2" style="width:20%;border-radius:15%" type="text" name="daterange" value="01/01/2018 - 01/15/2018"/>
                <input type="hidden" id="date-start">
                <input type="hidden" id="date-end">
                <button id="sendWhatsApp" style="display: none;" class="btn btn-primary mr-2">WhatsApp High Prob Users</button>
                <a id="downloadExcel" class="btn btn-success mt-2" style="padding:8px 8px 8px 8px" href="{{route('downloadIdentifiedUsersExcel')}}">Download as Excel</a>
            </div> 
            <div class="table-responsive mr-4">
                <table class="table table-bordered mr-4 ml-4" id="idUsersTable">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <!-- <th scope="col">User ID</th> -->
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Last Visited</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Products Visited</th>
                        <th scope="col">Added To Cart</th>
                        <th scope="col">Purchases</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>  
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    var start_date = null;
    var end_date = null;
    var dTable;
    function getIdentifiedUsersDataURL() {
        return "{{route('list.identified.users')}}";
    }

    $(document).ready(function () {
        setDateTimePicker();
        setDataTable();
    });

    function reloadDataTable(start, end) {
        dTable.fnDestroy();
        dTOptions.ajax.data.start_date = $('#date-start').val();
        dTOptions.ajax.data.end_date = $('#date-end').val();
        setDataTable();
    } 

    var dTOptions = {
        processing: true,
        serverSide: true,
        searching: false,
        pageLength: 50,
        order: [[0, 'desc']],
        columnDefs: [{ 
            //targets: [0,1,2,3], 
            orderable: false 
        }],
        dom: 'rtip',
        //info: false,
        ajax: {
            url: getIdentifiedUsersDataURL(),
            data: {
                "start_date": $("#date-start").val(),
                "end_date": $('#date-end').val()
            }
        },
        columns: [
            {data: 'serial_number', name: 'serial_number'},
            //{data: 'regd_user_id', name: 'regd_user_id'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'last_visited', name: 'last_visited'},
            {data: 'phone', name: 'phone'},
            {data: 'visited', name: 'visited'},
            {data: 'added_to_cart', name: 'added_to_cart'},
            {data: 'purchased', name: 'purchased'}
        ]
    }

    function setDataTable() {
        console.log(dTOptions.ajax.data);
        dTable = $('#idUsersTable').dataTable(dTOptions);
    }

    function setDateTimePicker() {
        var startDate = moment().subtract(14, 'days');
        var endDate = moment();
        // $("#date-start").val(startDate.unix());
        // $("#date-end").val(endDate.unix());
        $('#date-range').daterangepicker({
            opens: 'left',
            showDropdowns: true,
            startDate,
            endDate,
            orientation: 'bottom',
            alwaysShowCalendars: true,
            drops: 'down',
            locale: {
                format: 'L',
                // cancelLabel: 'Clear'
            },
            ranges: {
                'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 60 Days': [moment().subtract(59, 'days'), moment()],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'Last 180 Days': [moment().subtract(179, 'days'), moment()],
            }
        }, function(start, end, label) {
            $("#date-start").val(Math.round(start/1000) + 86400);
            $("#date-end").val(Math.round(end/1000));
            reloadDataTable();
        });
    }
</script>
@endsection