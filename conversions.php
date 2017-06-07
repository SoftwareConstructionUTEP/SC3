<?php
	function convert($value, $src = "str", $dest = "num"){
		if(!is_numeric($value)){
			$value = floatval($value);
		}
		if($src === "str" && $dest === "num"){
			return $value;
		}
		if($src === "mm"){
			if($dest === "in"){
				return $value * 0.0393701;
			}
		}
		else if($src === "kN"){
			if($dest === "lbf"){
				return $value * 224.809;
			}
		}
	}
	function stamp2sec($stamp){
		$hrminsec = explode(":", $stamp);
		$secs = convert($hrminsec[2]);
		$secs += convert($hrminsec[1]) * 60;
		$secs += convert($hrminsec[0]) * 3600;
		return $secs;
	}
?>
