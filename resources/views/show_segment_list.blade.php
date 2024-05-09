@extends('layouts.new_app')

@section('css')
<link href="{{asset('css/createList.css')}}" type="text/css" rel="stylesheet" />
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Show List</h1>
                <i class="fas fa-chart-pie"></i>
            </div>
        </section>
        <section class="main-content mt-3 mr-2 ml-2">
        <form method="POST" action="{{route('store.identified.user.segments')}}"> 
            @csrf
            <div class="container"> 
                <div  class="form-group bg-white list-name-group">
                    <label for="listName" class="list-name-label">List Name:</label>
                    <input type="text" id="listName" value="{{$segment->listName ?? ''}}" name="listName" class="form-control list-name-input">
                </div>
                
                @include('partials.segments.top_part', ['rule' => $segment->top_rules])
            
                <div class="container behavioral-container" id="did_do_events_card">
                    <h2 class="settings-heading" >Behavioral</h2>
                    <div id="did_do_events_card_container">
                        @php $rules = $segment->getRules(); @endphp
                        @if($rules !== null && count($rules) > 0)
                            @foreach($rules as $key => $rule) 
                                @include('partials.segments.did_do_events', ['rule' => $rule, 'counter' => ($key + 1)])
                            @endforeach
                        @else 
                            @include('partials.segments.did_do_events', ['counter' =>  1])
                        @endif
                    </div>
                    <!-- Placeholder for additional event-criteria-cards -->
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button" id="resetForm">Reset</button>
                </div>
                <div class="container behavioral-container" id="did_not_do_events_card">
                    <h2 class="settings-heading">Behavioral</h2>
                    <div id="did_not_do_events_card_container">
                        @php $rules = $segment->getNotRules(); @endphp
                        @if($rules !== null && is_array($rules) && count($rules) > 0)
                            @foreach($rules as $key => $rule)     
                                @include('partials.segments.did_not_do_events', ['rule' => $rule, 'counter' => ($key + 1)])
                            @endforeach
                        @else 
                            @include('partials.segments.did_not_do_events', ['counter' => 1])
                        @endif
                    </div>
                    <!-- Placeholder for additional event-criteria-cards -->
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button" id="resetNotForm">Reset</button>
                </div>
            </div>
            {{-- <div class="text-center mt-2 mb-2">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div> --}}
        </form> 
        </section> 
        @if(isset($segmentData) && is_array($segmentData) && isset($segmentData['body']) && count($segmentData) > 0)
            <section class="main-content mt-3 mr-4 ml-4">
                <div class="container">
                    <h4 class="viewsegment">Users in segment</h4><br>
                    <!-- added excel button to download -->
                    <button class="excelbtn" onclick="downloadTableAsExcel()">Download</button>

                    <table class="table">
                        <thead class="viewsegment-2">
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </thead>
                        <tbody>
                            @foreach($segmentData['body'] as $userRow)
                            <tr>
                                <td>{{$userRow['name'] ?? ''}}</td>
                                <td>{{$userRow['phone'] ?? ''}}</td>
                                <td>{{$userRow['email'] ?? ''}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>           
        @endif
    </div>
@endsection

@section('scripts')
<!-- Added download as excel functionality -->
<script>
    function downloadTableAsExcel() {

    var table = document.querySelector('.table');
   
    var workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
    
    var filename = 'users_data.xlsx';
   
    XLSX.writeFile(workbook, filename);
}

    $(document).ready(function () {
        $(document).on('click', '.btn-logic', function (e) {
            e.preventDefault();
            var el = $(this);
            var parent = el.parent();
            parent.find('.btn-logic').each(function () {
                $(this).removeClass('active-and-or-button');
            });
            el.addClass('active-and-or-button');
            var value = el.data('value');
            parent.find('.and_or_val').val(value);
        });

        $('#resetNotForm').click(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_not_do_events.defaultHTML')}}",
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        $('#did_not_do_events_card').find('.event-criteria-card').html(response.html);
                    }
                }
            })
        });

        $('#resetForm').click(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_do_events.defaultHTML')}}",
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        $('#did_do_events_card').find('.event-criteria-card').html(response.html);
                    }
                }
            })
        });

        $(document).on('change', '.time-select', function (e) {
            e.preventDefault();
            var el = $(this);
            var val = el.val();
            var parentEl = el.parent().parent().parent();
            parentEl.find('.within-last-days').css({'display': val == 'within_last_days' ? 'block' : 'none'});
            parentEl.find('.before-days').css({'display': val == 'before_days' ? 'block' : 'none'});
        });

        $(document).on('click', '.addNotRule', function (e) {
            e.preventDefault();
            var el = $(this);
            var noOfElements = $('#did_not_do_events_card').find('.event-criteria-card').length;
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_not_do_events.defaultHTML')}}",
                data: {counter: noOfElements},
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        el.parent().parent().append(response.html);
                    }
                }
            })
        });

        $(document).on('click', '.addRule', function (e) {
            e.preventDefault();
            var el = $(this);
            var noOfElements = $('.event-criteria-card').length;
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_do_events.defaultHTML')}}",
                data: {counter: noOfElements},
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        el.parent().parent().append(response.html);
                    }
                }
            })
        });

        $(document).on('click', '.deleteRule', function (e) {
            e.preventDefault();
            var noOfElements = $('.event-criteria-card').length;
            var el = $(this);
            if(noOfElements > 1) {
                el.parent().remove();
            }
        });
    });
</script>
@endsection