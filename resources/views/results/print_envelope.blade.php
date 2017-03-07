<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet"> 

    <link href="{{ asset('/css/vl2.css') }}" rel="stylesheet">
    <link rel="Shortcut Icon" href="{{ asset('/images/icon.png') }}" />  
    <script src="{{ asset('/js/jquery-2.1.3.min.js') }}" type="text/javascript" ></script>
   
</head>

<body>
    <div id="print-btn-div" style='text-align:center; padding:20px;'>
       
        <button id="print-btn" class='btn btn-danger' >Print</button>
    </div>

    <page size="A4" layout="landscape" style="font-size:50px;">
            <div style="margin-left:40px;"> 
                <span>From: CPHL</span>

                <div style="display:inline-block; margin-left:200px;">
                    <img src="/images/hub_bike.png">
                    <span style="font-weight:bolder;">{{ $facility->hub }}</span>
                </div>
                <br><br> <br>               
                <div class="row">
                    <div class="col-xs-1"><span>To:</span></div>
                    <div class="col-xs-8">              
                        
                        <br><span>{{ $facility->facility }}</span><br>
                        <span>District: {{ $facility->district }}</span><br><br>
                        <span style="font-weight:bold;font-size:30px">Viral Load Results</span>
                    </div>
                    
                    <h1 style=" margin-top:50px;margin-left:700px;margin-right:-250px;transform:rotate(90deg);">{{ $facility->hub }} &nbsp;  &nbsp; {{ $facility->facility }}</h1>
                    

                </div>  
            </div>
        </page>

    <script type="text/javascript">

    $('#print-btn').click(function(){
        $('#print-btn-div').hide();
         window.print(); 
         setTimeout(window.close, 0);          
    });

    </script>


</body>

</html>
