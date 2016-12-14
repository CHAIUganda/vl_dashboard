@extends('layout')

@section('content')

<div class="row">
    <div class="drop_down_container col-lg-4">
        <label for="b">Search worksheet:</label> 
        {!! Form::text('worksheet', '', array('class'=>'form-control input-sm input_md', 'id'=>'b', 'autocomplete'=>'off')) !!}
        <div class='live_drpdwn' id="worksheet_dropdown" style='display:none'></div>
    </div>
</div>



<script type="text/javascript">
$('#qc').addClass('active');

    drpdwn= $(".live_drpdwn");

    function get_data(q,drpdwn,link){
        if(q && q.length>=3){   
            console.log("this is what you have just typed:"+ q+"link"+link);      
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
        console.log("Dededed");
        var dd = $("#worksheet_dropdown");
        get_data(q, dd, "/qc/wk_search/");
    });

    $(".drop_down_container").mouseover(function(){ drpdwn.show(); });

    $(".drop_down_container").mouseout(function(){ drpdwn.hide(); });

</script>


@endsection()