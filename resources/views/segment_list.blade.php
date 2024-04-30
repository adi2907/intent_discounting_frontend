@extends('layouts.new_app')
@section('css')
    <!--<link rel="stylesheet" href="{{asset('css/identified_users.css')}}">-->
    <link rel="stylesheet" type="text/css" href="{{asset('css/segment_list.css')}}" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
@endsection
@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Segments</h1>
                <i class="fas fa-user"></i>
                <a href="{{ route('create.identified.user.segments') }}" class="plusicon fas fa-plus" title="Create New List"></a>
            </div>
        </section>
        <section class="main-content mt- mr-3"  style="background-color: white;" >
            <div class="table-responsive mr-4">
                <table class="table table-bordered mr-4 ml-4" id="idUsersTable">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <!-- <th scope="col">User ID</th> -->
                        <th scope="col">Name</th>
                        <th scope="col">Number of Users</th>
                        <th scope="col">List Type</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if($segments !== null && $segments->count() > 0) 
                            @foreach($segments as $key => $row) 
                                <tr>
                                    <td>{{$key + 1}}</td>
                                    <td>{{$row['listName']}}</td>
                                    <td>{{$row['no_of_users']}} {{$row['users_measurement']}}</td>
                                    <td>Dynamic</td>
				    <td>
					<div class="button-container">
                                        	<a class="btn btn-primary" href="{{route('show.identified.user.segments', ['id' => $row['id']])}}">View</a>
                                        	<a class="btn btn-danger" href="{{route('delete.identified.user.segments', ['id' => $row['id']])}}">Delete</a>
				    	</div>
				    </td>
                                </tr>
                            @endforeach
                        @else 
                        <tr>
                            <td colspan=""></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
