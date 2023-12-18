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
        <section class="main-content">
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Products Visited</th>
                        <th scope="col">Added To Cart</th>
                        <th scope="col">Purchases</th>
                        <th scope="col">Conversion Prob</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Rohan Patel</td>
                        <td>rohan.patel@gmail.com</td>
                        <td>9876543210</td>
                        <td>13</td>
                        <td>10</td>
                        <td>7</td>
                        <td>High</td>
                        <td>
                            <i class="fab fa-whatsapp"></i>
                            <i class="fa fa-envelope"></i>
                        </td>

                    </tr>
                    <tr>
                        <td>Aria Kapoor</td>
                        <td>aria.kapoor@gmail.com</td>
                        <td>8765432109</td>
                        <td>12</td>
                        <td>8</td>
                        <td>5</td>
                        <td>Medium</td>
                        <td>
                            <i class="fab fa-whatsapp"></i>
                            <i class="fa fa-envelope"></i>
                        </td>
                    </tr>
                    <!-- Add more rows as needed -->
                    </tbody>
                </table>
                </div>
                
                <div class="button-group d-flex justify-content-end mt-3">
                <button id="sendWhatsApp" class="btn btn-primary mr-2">WhatsApp High Prob Users</button>
                <button id="downloadExcel" class="btn btn-success">Download as Excel</button>
                </div> 


        </section>
    </div>  

@endsection