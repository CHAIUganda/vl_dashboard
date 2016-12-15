@extends('layout')

@section('content')  

<div class="panel panel-default">
    <div class="panel-heading"> <h3 class="panel-title">Users</h3> </div>
    <br>&nbsp;&nbsp;<a href="/admin/create_user" class='btn btn-sm btn-danger'>CREATE NEW USER</a>
    <div class="panel-body">
        <table id="results-table" class="table table-condensed table-bordered">
            <thead>
                <tr>
                    <th>Name</th>               
                	<th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Hub</th>
                    <th>Facility</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users AS $user)
                 <tr>
                    <td>{{ $user->name }}</td>               
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->telephone }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->first()->display_name }}</td>
                    <td>{{ $user->hub_name }}</td>
                    <td>{{ $user->facility_name }}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
 </div>
</div>

<script type="text/javascript">

$('#admin').addClass('active');

$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()