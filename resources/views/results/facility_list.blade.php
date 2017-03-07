@extends(($sect == 'admin') ? 'auth.layout' : 'layout')

@section(($sect == 'admin') ? 'admin_content' : 'content')
<br>
<?php 
$h_limit = "";
if(\Request::has('h')) $h_limit = "?h=".\Request::get('h');
?>
@if(empty(Auth::user()->facility_id) AND empty(Auth::user()->hub_id))
    {!! Form::text('hub','', ['id'=>'hub','class' => 'form-control input-sm input_md', 'autocomplete'=>'off', 'placeholder'=>"Search Hub"] ) !!}
    <div class='live_drpdwn' id="worksheet_dropdown" style='display:none'></div>
@endif()
<br>
<table id="results-table" class="table table-condensed table-bordered  table-striped">
<thead>
    <tr>
        <th>Hub</th>  
        <th>Facility</th>                     
        <th>Contact Person</th>
        <th>Phone</th>
        <th>Email</th>
        <th># Pending</th>
        <th># Printed</th>
        <th># Downloaded</th>
        @if($sect == 'admin')<th># Last Printed/ Downloaded</th>@endif 
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
        ajax: '{!! url("/results/data$h_limit") !!}',
        columns: [    
            {data: 'hub', name: 'h.hub'},
            {data: 'facility', name: 'f.facility'},
            {data: 'contactPerson', name: 'f.contactPerson'},
            {data: 'phone', name: 'f.phone'},
            {data: 'email', name: 'f.email'},
            {data: 'num_pending', name: 'num_pending', searchable: false},
            {data: 'num_printed', name: 'num_printed', searchable: false},
            {data: 'num_downloaded', name: 'num_downloaded', searchable: false },
            @if($sect == 'admin'){data: 'printed_at', name: 'p.printed_at'},@endif
            @if($sect == 'results') {data: 'action', name: 'action', orderable: false, searchable: false}, @endif
        ]
    });
});

drpdwn= $(".live_drpdwn");

    function get_data(q,drpdwn,link){
        if(q && q.length>=3){   
            //console.log("this is what you have just typed:"+ q+"link"+link);      
            $.get(link+q+"/", function(data){
                drpdwn.show();
                drpdwn.html(data);
            });
        }else{
            drpdwn.hide();
            drpdwn.html("");
        }
    }

    $("#hub").keyup(function(){
        var q = $(this).val();
        var dd = $("#worksheet_dropdown");
        get_data(q, dd, "/searchbyhub/");
    });
</script>
@endsection()