<?php namespace EID\Closet;

//use HTML;
use Form;

class MyHTML{
	public static function text($name='',$val='',$clss='input_md',$id=null){
		return Form::text($name,$val,array('class'=>$clss,'id'=>$id));
	}

	public static function email($name='',$val='',$clss='input_md',$id=null){
		return Form::email($name,$val,array('class'=>$clss,'id'=>$id));
	}

	public static function hidden($name='',$val='',$id=null,$clss=null){
		return Form::hidden($name,$val,array('id'=>$id,'class'=>$clss));
	}

	public static function select($name,$arr,$default='',$id=null,$clss=null,$onchange=null){
		return Form::select($name,$arr,$default,array('id'=>$id,'class'=>$clss,'onchange'=>$onchange));
	}

	public static function submit($label='Submit',$clss='btn btn-primary',$name=null){
		return Form::submit($label,array('class'=>$clss,'name'=>$name));
	}

	public static function link_to($url='/',$label='link',$clss=null,$onclick=null){
		return link_to($url,$label,array('class'=>$clss,'onclick'=>$onclick));
	}

	public static function checkbox($name="",$value="",$label="",$id=null,$onclick=null){
		$checkbox=Form::checkbox($name,$value,0,['id'=>$id,'onclick'=>$onclick]);
		return "<label class='checkbox-inline'> $checkbox $label</label>";
	}

	public static function datepicker($name,$value,$id){
		$txt=MyHTMl::text($name,$value,null,$id);
		$script="<script> $(function() { $( \"#$id\" ).datepicker(); }); </script>";
		return "$txt $script";
	}

    public static function datepicker2($name,$value,$id){
		$txt=MyHTMl::text($name,$value,null,$id);
		$script="<script> $(function() { $( \"#$id\" ).datepicker(); });";
		return "$txt $script";
	}

	public static function tinyImg($img,$hite=25,$wdth=25){
		return "<img src='/images/$img' height='$hite' width='$wdth'>";
	}

	public static function radio($name="name",$value="1",$fld_value="",$label="",$clss="",$id="",$onchange=""){
		$sChecked=$value==$fld_value?'checked':'';
		$sClss=!empty($clss)?"class='$clss'":"";
		$sId=!empty($id)?"id='$id'":"";
		$sOnChange=!empty($onchange)?"onchange='$onchange'":"";
		return "<label><input type=radio name='$name' value='$value' $sChecked $sClss $sId $sOnChange > $label</label>";
	}

	public static function localiseDate($date,$format='m/d/Y'){
		return date($format,strtotime($date));
	}

	public static function formatDate2STD($date){
		$date_arr=explode("/", $date);
		if(count($date_arr)==3) return $date_arr[2]."-".$date_arr[1]."-".$date_arr[0];
		else return "";
		
	}

	public static function monthYear($name,$is_arr,$y=null,$m=''){
		$y_name=$is_arr==1?$name."_y[]":$name."_y";
		$m_name=$is_arr==1?$name."_m[]":$name."_m";
		if(empty($y) || $y==0) $y=date('Y');
		return MyHTML::selectMonth($m_name,$m)." ".Form::text($y_name,$y,array('class'=>'input_tn','place_holder'=>'YYYY','maxlength'=>'4'));
	}

	public static function selectMonth($name,$val,$id=null){
		$months=[1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Dec'];
		return MyHTML::select($name,$months,$val,$id);
	}

	//to be used under javascript
	public static function text2($name,$val){
		return "<input type='text' name='$name' value='$val'>";
	}

	public static function select2($name='',$arr=array(),$default=""){
		$ret="<select name='$name'>";
		foreach ($arr as $k => $v) {
			$slcted=$k==$default?'selected':'';
			$ret.="<option $slcted value='$k'>$v</option>";
		}
		return $ret."</select>";
	}

	public static function getFileExt($file_name){
		$arrr=explode('.', $file_name);
		return array_pop($arrr);
	}

	public static function anchor($url="",$label="",$permission="",$attributes=[]){
		$lnk="";
		if(in_array($permission,session('permission_parents')) || in_array($permission,session('permissions')) || session('is_admin')==1){
			$attr_str="";
			foreach ($attributes as $k => $v)  $attr_str.=" $k='$v' ";
			$lnk="<a $attr_str href='$url'>".$label."</a>";
			//$lnk=Form::link_to($url,$label,$attributes);
		}
		return $lnk;
	}

	public static function permit($permission){
		if(in_array($permission,session('permission_parents')) || in_array($permission,session('permissions')) || session('is_admin')==1){
			return true;
		}else{
			return false;
		}

	}



	public static function lowNumberMsg($nSamples, $nSamplesNeeded = 22){
		$ret="";
		if($nSamples==0){
			$ret="<p class='alert alert-danger'>Sorry no samples approved for worksheet creation</p>";
		}elseif($nSamples<$nSamplesNeeded){
			$x=$nSamplesNeeded-$nSamples;
			$ret="<p class='alert alert-danger'>Sorry you need more $x samples for worksheet creation</p>";
		}else{
			$ret="";
		}
		return $ret;
	}

	public static function months(){
		return [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Dec'];
	}

	public static function initMonths(){
		$ret=[];
		for($i=1;$i<=12;$i++){
			$ret[$i]=0;
		}
		return $ret;
	}

	public static function years($min="",$max=""){
		if(empty($min)) $min=1900;
		if(empty($max)) $max=date('Y');
		if($max<$min) return [];
		$yrs_arr=[];
		for($i=$max;$i>=$min;$i--) $yrs_arr[$i]=$i;
		return $yrs_arr;
	}

	public static function selectYearMonth($min="",$max="",$name="",$default="",$options=[],$label=""){
		if(empty($min)) $min=1900;
		if(empty($max)) $max=date('Y');
		if($max<$min) return [];

		$yrs_arr=!empty($label)?[""=>$label]:[];

		for($i=$max;$i>=$min;$i--){
			$yrs_arr[$i]=["$i-1"=>'Jan',"$i-2"=>'Feb',"$i-3"=>'Mar',"$i-4"=>'Apr',"$i-5"=>'May',"$i-6"=>'Jun',"$i-7"=>'Jul',"$i-8"=>'Aug',"$i-9"=>'Sept',"$i-10"=>'Oct',"$i-11"=>'Nov',"$i-12"=>'Dec'];
		} 
		return Form::select($name,$yrs_arr,$default,$options);
	}

	public static function boolean_draw($arr,$val){
		$ret="";
		// $checked="<span class='glyphicon glyphicon-check print-check'></span>";
		// $unchecked="<span class='glyphicon glyphicon-unchecked print-uncheck'></span>";
		$checked="<input type='checkbox' checked disabled readonly>";
		$unchecked="<input type='checkbox' disabled readonly>";
		foreach ($arr as $x => $label) {
			$prefix = $x==$val?$checked:$unchecked;
			$ret .= "$prefix $label &nbsp;&nbsp; ";		
		}
		return $ret;
	}


	public static function getVLNumericResult($result,$machineType,$factor) {
	//check machine types
		if($machineType=="roche" || $machineType=="abbott") {
			//check conditions
			if($result=="Not detected" || $result=="Target Not Detected" || $result=="Failed" || $result=="Invalid") {
				return $result;
			} elseif(substr(trim($result),0,1)=="<") {
				//clean the result remove "Copies / mL" and "," from $result
				$result=preg_replace("/Copies \/ mL/s","",$result);
				$result=preg_replace("/,/s","",$result);
				$result=preg_replace("/\</s","",$result);
				$result=trim($result);
				/*
				* do not multiply by factor, based on a 17/Sept/14 discussion 
				* with Christine at the CPHL Viral Load Lab
				* $result*=$factor;
				*/
			
				//return
				return "&lt; ".number_format((float)$result,2)." Copies / mL";
			} elseif(substr(trim($result),0,1)==">") 
{				//clean the result remove "Copies / mL" and "," from $result
				$result=preg_replace("/Copies \/ mL/s","",$result);
				$result=preg_replace("/,/s","",$result);
				$result=preg_replace("/\>/s","",$result);
				$result=trim($result);
				//factor
				$result*=$factor;
			
				//return
				return "&gt; ".number_format((float)$result,2)." Copies / mL";
			} else {
				//clean the result remove "Copies / mL" and "," from $result
				$result=preg_replace("/Copies \/ mL/s","",$result);
				$result=preg_replace("/,/s","",$result);
				$result=preg_replace("/\</s","",$result);
				$result=preg_replace("/\>/s","",$result);
				$result=trim($result);
				//factor
				$result*=$factor;
			
				//return
				return number_format((float)$result)." Copies / mL";
			}
		}
	}

	public static function isResultFailed($machineType,$result,$flag, $interpretation=""){
		$check = 0;

		if($machineType=='abbott'){
			$abbott_flags = array(
				"4442 Internal control cycle number is too high.",
				"4450 Normalized fluorescence too low.",
				"4447 Insufficient level of Assay reference dye.",
				"4457 Internal control failed.",
				"3153 There is insufficient volume in the vessel to perform an aspirate or dispense operation.",
				"3109 A no liquid detected error was encountered by the Liquid Handler.",
				"A no liquid detected error was encountered by the Liquid Handler.",
				"Unable to process result, instrument response is invalid.",
				"3118 A clot limit passed error was encountered by the Liquid Handler.",
				"3119 A no clot exit detected error was encountered by the Liquid Handler.",
				"3130 A less liquid than expected error was encountered by the Liquid Handler.",
				"3131 A more liquid than expected error was encountered by the Liquid Handler.",
				"3152 The specified submerge position for the requested liquid volume exceeds the calibrated Z bottom",
				"4455 Unable to process result, instrument response is invalid.",
				"A no liquid detected error was encountered by the Liquid Handler.",
				"Failed          Internal control cycle number is too high. Valid range is [18.48, 22.48].",
				"Failed          Failed            Internal control cycle number is too high. Valid range is [18.48,",
				"Failed          Failed          Internal control cycle number is too high. Valid range is [18.48, 2",
				"There is insufficient volume in the vessel to perform an aspirate or dispense operation.",
				"Unable to process result, instrument response is invalid.",
			);

			$abbott_result_fails = array_merge($abbott_flags, array( "-1","-1.00","OPEN"));
			
			if(empty($result) || in_array($result, $abbott_result_fails) || in_array($flag, $abbott_flags)){
				$check = 1;
			}
			/*if($flag != 'OPEN' && $interpretation != 'OPEN'){
				$check = 1;
			}*/
		}elseif($machineType=='roche'){
			if(trim($result) == 'Failed' || trim($result) == 'Invalid'){
				$check = 1;
			}
		}
		return $check;		
	}

	public static function abbott_fail_sql(){
		$abbott_result_fails = array(
			"-1.00",
			"3153 There is insufficient volume in the vessel to perform an aspirate or dispense operation.",
			"3109 A no liquid detected error was encountered by the Liquid Handler.",
			"A no liquid detected error was encountered by the Liquid Handler.",
			"Unable to process result, instrument response is invalid.",
			"3118 A clot limit passed error was encountered by the Liquid Handler.",
			"3119 A no clot exit detected error was encountered by the Liquid Handler.",
			"3130 A less liquid than expected error was encountered by the Liquid Handler.",
			"3131 A more liquid than expected error was encountered by the Liquid Handler.",
			"3152 The specified submerge position for the requested liquid volume exceeds the calibrated Z bottom",
			"4455 Unable to process result, instrument response is invalid.",
			"A no liquid detected error was encountered by the Liquid Handler.",
			"Failed          Internal control cycle number is too high. Valid range is [18.48, 22.48].",
			"Failed          Failed            Internal control cycle number is too high. Valid range is [18.48,",
			"Failed          Failed          Internal control cycle number is too high. Valid range is [18.48, 2",
			"OPEN",
			"There is insufficient volume in the vessel to perform an aspirate or dispense operation.",
			"Unable to process result, instrument response is invalid.",
			);
		$abbott_flags = array(
			"4442 Internal control cycle number is too high.",
			"4450 Normalized fluorescence too low.",
			"4447 Insufficient level of Assay reference dye.",
			"4457 Internal control failed.",
		);
		return 1;
		//return " (a.result IN (".implode(", ", $abbott_result_fails).") OR a.flags IN (".implode(",", $abbott_flags)."))";
	}

	public static function getNumericalResult($result=""){
		$numericVLResult = 0;
		$numericVLResult = preg_replace("/Copies \/ mL/s","",$result);
		$numericVLResult = preg_replace("/,/s","",$numericVLResult);
		$numericVLResult = preg_replace("/\</s","",$numericVLResult);
		$numericVLResult = preg_replace("/\&lt;/s","",$numericVLResult);
		$numericVLResult = preg_replace("/\&gt;/s","",$numericVLResult);
		$numericVLResult = trim($numericVLResult);
		return $numericVLResult;
	}

	public static function isSuppressed2($result,$sample_type="",$test_date=""){
		$ret="";
		$valid = self::isResultValid($result);
		$test_date_str=strtotime($test_date);	
		if($valid=='YES'){
			if(empty($sample_type) && empty($test_date)){
				$ret = $result<=1000?"YES":"NO";
				return $ret;
			}
			if($test_date_str<1459458000){//use previous suppression criteria if before 2016-04-01 00:00:00
				if($sample_type=="DBS"){
					$ret=$result>5000?"NO":"YES";
				}else{
					$ret=$result>1000?"NO":"YES";
				}
			}else{
				$ret=$result<=1000?"YES":"NO";
			}		
		}else{
			$ret="UNKNOWN";
		}
		return $ret;
	}

	private static function isResultValid($result){
		$ret="";
		$invalid_cases=array(
			"Failed","Failed.","Invalid",
			"Invalid test result. There is insufficient sample to repeat the assay.",
			"There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.",
			"There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.");

		if(in_array($result, $invalid_cases)) $ret="NO";
		else $ret="YES";
		return $ret;
	}

	public static function getRecommendation($suppressed,$test_date,$sample_type, $dob){
		$today = date('Y-m-d');
		$ret="";
		$nxt_date = "";
		$rec_suppressed_adults="Below 1,000 copies/mL: Patient is suppressing their viral load. <br>Please continue adherence counseling. Do another viral load after 12 months.";
		$rec_suppressed_kids="Below 1,000 copies/mL: Patient is suppressing their viral load. <br>Please continue adherence counseling. Do another viral load after 6 months.";
		$rec_unsuppressed="Above 1,000 copies/mL: Patient has elevated viral load. <br>Please initiate intensive adherence counseling and conduct a repeat viral load test within 4-6 months.";
		$msg = "Expected in ";
		if($suppressed=='NO'){
			$ret = $rec_unsuppressed;
			$nxt_date =  " ($msg ".date('M, Y', strtotime($test_date)+(121*24*3600)).")";
		}elseif($suppressed == 'YES'){
			$yrs = (strtotime($today)-strtotime($dob))/(3600*24*365);
			if($yrs<20){
				$ret = $rec_suppressed_kids;
				$nxt_date =  " ($msg ".date('M, Y', strtotime($test_date)+(182*24*3600)).")";
			}else{
				$ret = $rec_suppressed_adults;
				$nxt_date =  " ($msg ".date('M, Y', strtotime($test_date)+(365*24*3600)).")";
			}
		}else{
			$ret = "";
			$nxt_date = "";
		}

		return $ret.$nxt_date;
	}

	public static function get_arr_pair($result, $name=''){
		$ret = [];
		foreach ($result as $res) {
			$ret[$res->id] = $res->$name;
		}
		return $ret;
	}

	public static function getImageData($file){
		$ret = "";
		$path = base_path('public/'.$file);
		if(file_exists($path)){
			$type = pathinfo($path, PATHINFO_EXTENSION);
			$data = file_get_contents($path);
			$ret = 'data:image/' . $type . ';base64,' . base64_encode($data);
		}	
		return $ret;		
	}

	public static function dropdownLinks($links=[]){
		$ret = "<div class='dropdown'><button class='btn btn-xs btn-danger dropdown-toggle' type='button' id='menu1' data-toggle='dropdown'>";
		$ret .= "Options <span class='caret'></span></button>";
		$ret .= "<ul class='dropdown-menu' role='menu' aria-labelledby='menu1'>";
		foreach ($links as $k => $v) {
			$ret .= "<li role='presentation'><a role='menuitem' href=\"$v\">$k</a></li>";			
		}
		$ret .= "</ul></div>";
		return $ret;
	}

	public static function dateNMonthsBack(){
    	$ret;
    	$n=env('INIT_MONTHS');
        $m=date('m');
        $y=date('Y');
        for($i=1;$i<=$n;$i++){
        	if($i==$n) 
        		$ret=$y.str_pad($m, 2,0, STR_PAD_LEFT);
            if($m==0){
                $m=12;
                $y--;
            }
            $m--;
        }
        return $ret;
    }

    public static function interpretCobas8800($result){
		$ret = array();
		if($result == 'Target Not Detected'){
			$numerical_result = 0;
			$suppressed = 'YES';
			$alpha_numerical_result = $result;
		}elseif($result == 'Invalid'){
			$numerical_result = 0;
			$suppressed = 'UNKNOWN';
			$alpha_numerical_result = 'Failed';
		}elseif(substr($result, 2) == 'Titer min'){
			$numerical_result = 50;
			$suppressed = 'YES';
			$alpha_numerical_result = substr($result, 0,1)." 50 Copies / mL";
		}elseif(substr($result, 2) == 'Titer max'){
			$numerical_result = 10000000;
			$suppressed = 'NO';
			$alpha_numerical_result = substr($result, 0,1)." 10,000,000 Copies / mL";
		}else{
			$numerical_result = number_format((float)$result);
			$n_result =  str_replace(",", "", $numerical_result)+0;
			$suppressed = $n_result>1000?'NO':'YES';
			$alpha_numerical_result = "$numerical_result Copies / mL";
		}

		return compact("numerical_result", "suppressed", "alpha_numerical_result");
	}

}
//{1:'Jan',2:'Feb',3:'Mar',4:'Apr',5:'May',6:'Jun',7:'Jul',8:'Aug',9:'Sept',10:'Oct',11:'Nov',12:'Dec'};