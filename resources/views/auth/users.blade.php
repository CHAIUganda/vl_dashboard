@extends('auth.layout')


@section('admin_content')  
{!! Session::get('msge') !!}<br>
&nbsp;&nbsp;<a href="/admin/create_user" class='btn btn-sm btn-danger'>Create new user</a>
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
                <th>Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            </tr>
        </thead>
    </table>

<script type="text/javascript">

$('#users-tab').addClass('active');

$(function() {
    $('#results-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! url("/admin/list_users/data") !!}',
        columns: [
            {data: 'name', name: 'name'},
            {data: 'username', name: 'username'},
            {data: 'telephone', name: 'telephone'},
            {data: 'email', name: 'email'},
            {data: 'roles', name: 'roles'},
            {data: 'hub_name', name: 'hub_name'},
            {data: 'facility_name', name: 'facility_name'},                  
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
});


$(function() {
    $('#results-table').DataTable();
});
</script>
@endsection()