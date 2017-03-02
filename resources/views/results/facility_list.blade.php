@extends('layout')

@section('content')  

<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">Facilities :: {!! \Auth::user()->hub_name !!}</h3> </div>
    <div class="panel-body">

      <table id="results-table" class="table table-condensed table-bordered  table-striped">
        <thead>
            <tr>
                <th>Facility</th>
                <th>Hub</th>               
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <!-- <th># Pending printing</th>
                <th># Printed</th> -->
                <!-- <th></th> -->
            </tr>
        </thead>
      </table>          
      
 </div>
</div>

<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: '{!! url("/facility_list/data") !!}',
        columns: [
           
            {data: 'hub', name: 'hub'},
            {data: 'contactPerson', name: 'contactPerson'},
            {data: 'phone', name: 'phone'},
            {data: 'email', name: 'email'},
        ]
    });
});
</script>
@endsection()