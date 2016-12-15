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
                    <th># Pending printing</th>
                    <th># Printed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($facilities AS $k => $facility)
                 <tr>
                    <td>{{ $facility['facility'] }}</td>               
                    <td>{{ $facility['contactPerson'] }}</td>
                    <td>{{ $facility['phone'] }}</td>
                    <td>{{ $facility['email'] }}</td>
                    <td><?= isset($facility['pending'])?$facility['pending']:0; ?></td>
                    <td><?= isset($facility['printed'])?$facility['printed']:0; ?></td>
                    <td><?= isset($facility['pending'])?"<a href='/results_list?f=$k'>view pending</a>":""; ?></td>
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