@extends('layout')

@section('content')  

<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">Facilities :: {!! \Auth::user()->hub_name !!}</h3> </div>
    <div class="panel-body">

      <table id="results-table" class="table table-condensed table-bordered">
        <thead>
            <tr>
                <th>Facility</th>               
              <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <!-- <th># Pending printing</th>
                <th># Printed</th> -->
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($facilities AS $facility)
            <tr>
                <td><a href='/results_list?f={{$facility->id}}'>{{ $facility->facility }}</a></td>               
                <td>{{ $facility->contactPerson }}</td>
                <td>{{ $facility->phone }}</td>
                <td>{{ $facility->email }}</td>
                
                <td>
                    <?= "<a class='btn btn-danger btn-xs' href='/results_list?f=$facility->id'>view pending</a>" ?>
                    <?= "<a class='btn btn-danger btn-xs' href='/results_list?f=$facility->id&printed=YES'>printed/downloaded</a>" ?>
                </td>
            </tr>
            @endforeach

        </tbody>
      </table>          
      
 </div>
</div>

<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()