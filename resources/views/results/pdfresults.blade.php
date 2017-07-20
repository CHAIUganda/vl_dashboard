<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>@yield('meta-title', 'Uganda Viral Load Dashboard')</title> 

     <style type="text/css">

     .print-container{
    width: 1000px;
    margin-left: 50px;
    min-height: 1400px;
}

.print-header{
    text-transform: uppercase;
    font-size: 16px;
    text-align: center;
}

.print-header-moh{
    font-weight: bolder;
}

.print-ttl{
    font-weight: bolder;
    text-transform: uppercase;
    /*background-color: #D8D8D8;*/
    padding: 5px;
    font-size: 16px;
    width: 100%;
    margin-top: 10px;
}

.print-sect{
    font-size: 14px;
    padding: 5px;
    border: 2px solid #E8E8E8;
    min-height: 75px;
}

.print-sect>table{
    min-width: 60%;
}

.print-sect table td{
    padding: 4px;
}


.print-val{
    text-decoration: underline;
}

.print-check,.print-uncheck{
    font-size: 20px;
}


page {
  display: block;
  margin: 0 auto;
  margin-bottom: 0.5cm;
  padding: 5px;
}
page[size="A4"] {  
  width: 21cm;
  height: 29.7cm; 
}
page[size="A4"][layout="portrait"] {
  width: 29.7cm;
  height: 21cm;  
}

.printmm-container{
    width: 20cm;
}

.stamp{
  position: relative;
}

.stamp-date{
 position: absolute;
 margin-top: 55px;
 margin-left: -145px;
 font-size: 18px;
 font-weight: bold;
 color: #F01319;
}

.date-released{
  font-size:12px;color:#000;
  border-top:dotted 1px;
  font-weight: lighter;
}

     </style>

</head>

<body>
  <?php 
 $local_today = date('d M Y');
 $local_today = strtoupper($local_today);
  ?>
      
    @foreach ($vldbresult AS $result_obj) 
     @include('results._pdfresult_slip') 
        
    @endforeach

    <script type="text/javascript">
        jQuery(function(){
            jQuery('.qrcode-output').each(function (index){
                var val = $(this).attr("value");
                $(this).qrcode({
                    text: val,
                    width: 75,
                    height:75
                });
            });         
        });
        </script>


</body>

</html>
