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
        <section class="main-content mt-3">
            @if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0)        
            <div class="button-group d-flex justify-content-end mt-3 mb-3 mr-3">
                <button id="sendWhatsApp" style="display: none;" class="btn btn-primary mr-2">WhatsApp High Prob Users</button>
                <a id="downloadExcel" class="btn btn-success" href="{{route('downloadIdentifiedUsersExcel')}}">Download as Excel</a>
            </div> 
            @endif
            <div class="table-responsive">
                <table class="table table-bordered mr-4 ml-4">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Products Visited</th>
                        <th scope="col">Added To Cart</th>
                        <th scope="col">Purchases</th>
                        <!-- <th scope="col">Conversion Prob</th> -->
                        <!-- <th scope="col">Action</th> -->
                    </tr>
                    </thead>
                    <tbody>
                    @if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0)
                    @foreach($data['body'] as $info)
                    <tr>
                        <td>{{$info['serial_number'] ?? 'N/A'}}</td>
                        <td>{{$info['name'] ?? 'N/A'}}</td>
                        <td>{{$info['phone'] ?? 'N/A'}}</td>
                        <td>{{$info['visited'] ?? 'N/A'}}</td>
                        <td>{{$info['added_to_cart'] ?? 'N/A'}}</td>
                        <td>{{$info['purchased'] ?? 'N/A'}}</td>
                        <!-- 
                        <td>High</td>
                        <td>
                            <i class="fab fa-whatsapp"></i>
                            <i class="fa fa-envelope"></i>
                        </td> -->

                    </tr>
                    @endforeach
                    @endif
                    
                    <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
                
            


        </section>
    </div>  

@endsection