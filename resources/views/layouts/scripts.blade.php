@php $v = time(); @endphp
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js?v={{$v}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js?v={{$v}}"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js?v={{$v}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js?v={{$v}}"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js?v={{$v}}"></script>
@if(Auth::check())
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endif