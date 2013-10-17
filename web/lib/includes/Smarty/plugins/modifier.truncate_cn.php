<?php 
//中文截取
function smarty_modifier_truncate_cn($string,$length, $etc = '...', $code = 'UTF-8')   
{
	$string = preg_replace("/\s+/", " ", strip_tags($string));
	if ($length == 0) {
        return '';
    } else if(mb_strlen($string, $code) <= $length) {
    	return $string;
    }
	$start = 0;
	$chars = $string;   
    $i=0;   
    do{   
        if (preg_match ("/[0-9a-zA-Z]/", $chars[$i])){//纯英文   
            $m++;   
        }
    	else{
    		$n++; 
    	}//非英文字节,   
        $k = $n/3+$m/2;   
        $l = $n/3+$m; 
        $i++;   
    } while($k < $length);
     $l2 = $l+1;
     $string = mb_substr($string,$start,$l,$code);
     $string2 = mb_substr($string,$start,$l2,$code);
     $check =  mb_substr($string2, -1);
     if(preg_match('/[a-zA-Z]/', $check)){
     	$record = checkWord($string);
     }
     else{
     	$record = $string;
     	
     }
     
     return $record.$etc;   
}

function checkWord($string){
	$str = mb_substr($string, -1);
	$i = -1;
	$j = 1;
	
	if($str != " "){
		if (preg_match("/^[a-zA-Z]+$/", $str)) {
			$comChar = mb_substr($string,--$i,1);
			
			if($comChar == " ") return mb_substr($string, 0, $i);
			else{
				$k = mb_strlen($string)-2;
				while ($comChar<>" "&&mb_strlen($string) != $j){
					//echo $comChar."++".mb_strlen($string)."--".$j."<br>";
		           $comChar = mb_substr($string, --$i, 1);
		           if ($comChar == " "){
		           		//echo $string;
		           		//echo mb_substr($string, 0,-12);
						return mb_substr($string, 0, $i);
		           }
		           elseif(mb_strlen($string) == $j+1){
		           		$stripstring = preg_replace('/(^.*[^a-zA-z])[a-zA-Z]+$/', "\${1}", $string);
						return $stripstring;
		           } else {
		               $max = $max-1;
		           }
		           $j++;
	         	}
			}
			
		} else {
			return $string;
		}
	}
	else{
		return $string;
	}
}
?>