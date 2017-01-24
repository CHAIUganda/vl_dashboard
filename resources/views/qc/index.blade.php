@extends('layout')

@section('content')
<link   href="{{ asset('/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('/js/select2.min.js') }}" type="text/javascript"></script>

<div class="row">
    <div class="drop_down_container col-lg-4">
        <label for="b">Search worksheet:</label> 
        {!! Form::text('worksheet', '', array('class'=>'form-control input-sm input_md', 'id'=>'b', 'autocomplete'=>'off')) !!}
        <div class='live_drpdwn' id="worksheet_dropdown" style='display:none'></div>
    </div>

     <div class="col-lg-4">
        <label for="hub">Filter by hub:</label> <br>
        {!! Form::select('hub', [""=>""]+$hubs,'', array('class'=>'form-control input-sm input_md', 'id'=>'hub', 'autocomplete'=>'off')) !!}
        <div class='live_drpdwn' id="hub_dropdown" ></div>
    </div>

     <div class="col-lg-4">
        <label for="facility">Filter by facility:</label> <br>
        {!! Form::select('facility', [""=>""]+$facilities,'', array('class'=>'form-control input-sm input_md', 'id'=>'facility', 'autocomplete'=>'off')) !!}
        <div class='live_drpdwn' id="facility_dropdown" ></div>
    </div>
</div>


<script type="text/javascript">
    $('#qc').addClass('active');

    drpdwn= $(".live_drpdwn");

    function get_data(q,drpdwn,link){
        if(q && q.length>=3){       
            $.get(link+q+"/", function(data){
                drpdwn.show();
                drpdwn.html(data);
            });
        }else{
            drpdwn.hide();
            drpdwn.html("");
        }
    }

    $("#b").keyup(function(){
        var q = $(this).val();
        var dd = $("#worksheet_dropdown");
        get_data(q, dd, "/qc/wk_search/");
    });

    $(".drop_down_container").mouseover(function(){ drpdwn.show(); });

    $(".drop_down_container").mouseout(function(){ drpdwn.hide(); });


    $("#hub").select2({  placeholder:"Select hub", allowClear:true});
    $("#facility").select2({  placeholder:"Select facility", allowClear:true});

    $("#hub").on("change",function(){
        $.get("/qc/byhub/"+$(this).val()+"/", function(data){
            $("#hub_dropdown").show();
            $("#hub_dropdown").html($('#hub option:selected').text()+"<br>"+data);
        });
    });

    $("#facility").on("change",function(){
        $.get("/qc/byfacility/"+$(this).val()+"/", function(data){
            $("#facility_dropdown").show();
            $("#facility_dropdown").html($('#facility option:selected').text()+"<br>"+data);
        });
    });

</script>


@endsection()