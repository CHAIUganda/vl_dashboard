@extends('auth.layout')


@section('admin_content')  
{!! Session::get('msge') !!}<br>
    <table id="results-table" class="table table-condensed table-bordered">
        <thead>
            <tr>
                <th>Hub</th>   
                <th># Pending</th>            
            	<th># printed</th>
                <th># reprinted</th>
                <th># downloaded</th>
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
            {data: 'hub', name: 'hub'},
            {data: 'num_pending', name: 'num_pending'},
            {data: 'num_printed', name: 'num_printed'},
            {data: 'num_reprinted', name: 'num_reprinted'},
            {data: 'num_downloaded', name: 'num_downloaded'}
        ]
    });
});

$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()