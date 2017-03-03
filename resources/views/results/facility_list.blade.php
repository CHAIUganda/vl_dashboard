@extends(($sect == 'admin') ? 'auth.layout' : 'layout');

@section(($sect == 'admin') ? 'admin_content' : 'content')
<table id="results-table" class="table table-condensed table-bordered  table-striped">
<thead>
    <tr>
        <th>Facility</th>
        <th>Hub</th>               
        <th>Contact Person</th>
        <th>Phone</th>
        <th>Email</th>
        <th># Pending</th>
        <th># Printed</th>
        <th># Downloaded</th>
        <th># Last Printed/ Downloaded</th> 
        @if($sect == 'results')<th></th>@endif
    </tr>
</thead>
</table>          

<script type="text/javascript">
@if($sect == 'admin') 
    $('#monitoring-tab').addClass('active'); 
@else
    $('#results').addClass('active');
@endif

$(function() {
    $('#results-table').DataTable({

        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: '{!! url("/results/data") !!}',
        columns: [    
            {data: 'hub', name: 'h.hub'},
            {data: 'facility', name: 'f.facility'},
            {data: 'contactPerson', name: 'f.contactPerson'},
            {data: 'phone', name: 'f.phone'},
            {data: 'email', name: 'f.email'},
            {data: 'num_pending', name: 'num_pending', searchable: false},
            {data: 'num_printed', name: 'num_printed', searchable: false},
            {data: 'num_downloaded', name: 'num_downloaded', searchable: false },
            {data: 'printed_at', name: 'p.printed_at'},
            @if($sect == 'results') {data: 'action', name: 'action', orderable: false, searchable: false}, @endif
        ]
    });
});
</script>
@endsection()