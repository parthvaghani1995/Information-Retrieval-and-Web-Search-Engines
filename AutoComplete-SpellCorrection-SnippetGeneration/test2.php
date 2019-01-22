<?php
	ini_set('memory_limit','2048M');
        include("simple_html_dom.php");

	function generate_snippet($value, $query){

		// echo "In generate".$query.$value;
		// echo $value;
		$file = file_get_contents($value);
		$html = str_get_html($file);
		$s =  strtolower($html->plaintext);
		#echo $s;
//		$s = str_replace('. ',' . ',$s);
		$strips = explode(" ",$query);
		$query = array_pop($strips);
		$s = str_replace("\'","",$s);
		$s = str_replace("!","",$s);
		$s = str_replace("?","",$s);
		$s = str_replace(",","",$s);
		$s = str_replace(",","",$s);
		$piece = explode(" ", $s);
		$pieces = array_values(array_filter($piece)); 
	#	echo (array_search($query, $pieces));
		if (array_search($query,$pieces) == false){
			#echo " no";
			return 0; 
		}else{
			$start = array_search($query,$pieces);
			if (array_search($query,$pieces) >=10){
				$start -= 10;
			}else{
				$start = 0;
			}
			$end = count($pieces);
			$end += 100;
			if($end > count($pieces)){
				$end = count($pieces);
			}
			$str = "";
			for($i = $start; $i< $end; $i++){
				$str .=" ".$pieces[$i];
			}
				#echo $str;
				return $str;
		}
		// if(false !== $start = array_search($query, $pieces)){
		// 	$start -=10;
		// }
		// else{
		// 	return "0"; 
		// }
		// $end = $start+40;
		// if($end>count($pieces))$end=count($pieces)-1;
		// $str = "";
		// if($start<0)$start =0;
		// if($start < $end){
		// 	for($i = $start ; $i<$end; $i++) 
		// 		$str.=" ".$pieces[$i];
		// 	#echo $str;
		// 	#print_r($pieces);
		// 	return "...".substr($str,0,160)."...";
		// }
		// else{
		// 	return "0";
		// }
	}
	
?>