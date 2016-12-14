@extends('layout')

@section('content')
    <div class="tab-pane active" id="print">  
        <input type="button" id="download" name="download" class='btn btn-sm btn-danger' value="PRINT PREVIEW SELECTED" onclick="viewSelected();"   /> 
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
                    <td><?= isset($facility['pending'])?"<a href='/results?f=$k'>view pending</a>":""; ?></td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

<script type="text/javascript">

$('#results').addClass('active');

$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()