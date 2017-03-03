@extends('auth.layout')
@section('admin_content')  
{!! Session::get('msge') !!}<br>
    <table id="results-table" class="table table-condensed table-bordered">
        <thead>
            <tr>

                <th>Printed at</th>   
                <th>Printed by</th>
                <th>Form Number</th> 
                <th>Facility</th>  
                <th>Hub</th>        
            </tr>
        </thead>
    </table>

<script type="text/javascript">

$('#logs-tab').addClass('active');

$(function() {
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! url("/logs/data") !!}',
        columns: [
            {data: 'printed_at', name: 'printed_at'},
            {data: 'printed_by', name: 'printed_by'},
            {data: 'formNumber', name: 'formNumber'},
            {data: 'facility', name: 'facility'},
            {data: 'hub', name: 'hub'}
        ]
    });
});

$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()