@extends('layouts.new_app')
@section('css')
    <link rel="stylesheet" href="{{asset('css/identified_users.css')}}">
@endsection
@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Identified Users</h1>
                <i class="fas fa-user"></i>
            </div>
        </section>
        <section class="main-content mt-3"  style="background-color: white;" >
            <div class="button-group d-flex justify-content-end mt-3 mb-3 mr-3">
                <button id="sendWhatsApp" style="display: none;" class="btn btn-primary mr-2">WhatsApp High Prob Users</button>
                <a id="downloadExcel" class="btn btn-success mt-2" href="{{route('downloadIdentifiedUsersExcel')}}">Download as Excel</a>
            </div> 
            <div class="table-responsive">
                <table class="table table-bordered mr-4 ml-4" id="idUsersTable">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
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
<script>
    $(document).ready(function () {
        $('#idUsersTable').dataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        dom: 'rtip',
        info: false,
        ajax: "{{route('list.identified.users')}}",
        columns: [
          {data: 'serial_number', name: 'serial_number'},
          {data: 'name', name: 'name'},
          {data: 'last_visited', name: 'last_visited'},
          {data: 'phone', name: 'phone'},
          {data: 'visited', name: 'visited'},
          {data: 'added_to_cart', name: 'added_to_cart'},
          {data: 'purchased', name: 'purchased'},
          
        ]
      });
    });
</script>
@endsection