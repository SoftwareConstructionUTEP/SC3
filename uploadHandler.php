<?php
	ini_set('memory_limit', '1024M');
	error_reporting(0);
	ini_set('post_max_size', '64M');
	ini_set('upload_max_filesize', '64M');
	session_start();
	//hnadles file uploads
	$toReturn = array();

	if(isset($_POST['submit'])){
		//if the directory was obtained successfully
		include('utils.php');
		$fromCache = false;
		if(isset($_POST['cache']) AND $_POST['cache'] === "true"){
			$fromCache = true;
		}
		$_SESSION['top'] = $_POST['toplvdt'];
		$toReturn['top'] = $_POST['toplvdt'];
		$_SESSION['LIMS'] = $_POST['LIMS'];
		$lims = $_POST['LIMS'];

		if(isset($_POST['txdotuse2']) && $_POST['txdotuse2'] == "notchecked"){
			$conn = mysqli_connect("irpsrvgis35.utep.edu", "ctis", "19691963", "otdata");
			$query = "SELECT COUNT(LIMS) AS count FROM cache";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_assoc($result);
			mysqli_close($conn);

			$lims = "nontxdot" . $row['count'];
		}

		$toReturn['lims'] = $lims;
		$_SESSION['type'] = $_POST['device'];
		$_SESSION['thickness'] = $_POST['specthickness'];
		$_SESSION['width'] = $_POST['specwidth'];
		$_SESSION['numofspec'] = $_POST['numofspec'];
		if(checkEmpty($_POST['LIMS']) && $_POST['txdotuse'] == "txdotuse"){
            $toReturn['error'] = "Must input LIMS value.";
        }
		else {
			require('config.php');
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
            if ($conn -> connect_error) {
                die("Connection failed: " . $conn -> connecterror);
            }
            $sql = "SELECT * FROM cache WHERE lims = '" . $lims . "'";
            $response = $conn -> query($sql);
			if($response->num_rows > 0 AND $fromCache){
				//get from db
				$row = $response->fetch_assoc();
				if(empty($row['log'])){
					$_SESSION['top'] = 'No';
				}
				$_SESSION['rawfile'][] = $row['path'];
				$_SESSION['logfile'][] = $row['log'];
				$_SESSION['type'] = $row['type'];
				$_SESSION['submitted'] = true;
				include("conversions.php");
			    //uncoment for deployment
			    $thickness = $_SESSION['thickness'];
			    $width = $_SESSION['width'];

			    $firstCycle = array();
			    $secondCycle = array();
			    $normLoads = array();
			    $maxLoadVals = array();
			    $disptime = array();
			    $coeff = array();
			    $area = array();
			    $fenergy = array();
			    $r2 = array();
			    $model = array();



			    for ($k=0; $k < $_SESSION['numofspec']; $k++) {
			        $filepath = $_SESSION['rawfile'][$k];

			        $firstCycle[] = array();
			        $secondCycle[] = array();

			        $maxLoads = array();
			        $normLoads[] = array();
			        $maxLoadIndex = -1;
			        if($_SESSION['type'] == "Shedworks"){
			          $csv = array_map('str_getcsv', file($filepath));
			          //get the first value of displacement
			          if($_SESSION['top'] === "Yes"){
			          	$logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
			          }
			          //number of rows
			          $offset = 21;
			          $initial_displace = convert($csv[$offset][0]);
			          $length = sizeof($csv);
			          $cycle = 0;
			          for($i = $offset; $i < $length; $i++){
			            $cycle = floor(($i - $offset) / 250);//number of rows ber cylcle in shedworks
			            if(!isset($maxLoads[$cycle])){
			              $maxLoads[] = 0;
			            }
			            if(convert($csv[$i][1]) > $maxLoads[$cycle]){//the max load for each cycle
			              $maxLoads[$cycle] = $csv[$i][1];
			              //code to get the index of max load of first cycle
			              if($cycle == 0){
			                $maxLoadIndex = $i;
			              }
			            }
			            //this part is for the first loop graph
			            if($i < 250 + $offset+1){
			              $firstCycle[$k][] = array(convert($csv[$i][0])-$initial_displace, convert($csv[$i][1]));
			            }
			            //this part is for the second loop graph
			            elseif($i < 500 + $offset+1){
			              $secondCycle[$k][] = array(convert($csv[$i][0])-$initial_displace, convert($csv[$i][1]));
			            }
			          }
			          $maxLoadVals[] = $csv[$maxLoadIndex][1];
					  if($_SESSION['top'] === "Yes"){
					  	$disptime[$k] = array();
				          $temp_arr = explode('	',$logfile[15][0]);
				          $temp_time = stamp2sec($temp_arr[1]);
				          $temp_disp = $temp_arr[2];

				          for ($i=15; $i < sizeof($logfile)-1; $i++) {

				            $temp_arr = explode('	', $logfile[$i][0]);
				            $disptime[$k][$i-15][0] = (stamp2sec($temp_arr[1]) - $temp_time); //time
				            $disptime[$k][$i-15][1] = ($temp_arr[2] - $temp_disp)/10 * 0.0393701; //disp
				          }
					  }
			        }
			        else if($_SESSION['type'] == "AMPT"){
			          $csv = array_map('str_getcsv', file($filepath));
			          $i = 1;//offset
			          $length = sizeof($csv);
			          $initial_displace = convert($csv[$i][4], "mm", "in");
			        $initial_displace_top = convert($csv[$i][5], "mm", "in");
			          while($i < $length){
			            $cycle = $csv[$i][0];
			          if(!isset($maxLoads[$cycle-1])){
			              $maxLoads[] = 0;
			            }
			            if((convert($csv[$i][2])) > $maxLoads[$cycle-1]){//the max load for each cycle
			              $maxLoads[$cycle-1] = $csv[$i][2];
			              //code to get the index of max load of first cycle
			              if($cycle == 1){
			                $maxLoadIndex = $i;
			              }
			            }
			            if($cycle == 1){
			              $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace,(-1)*convert($csv[$i][2], "kN", "lbf"));
			            }
			            else if($cycle == 2){
			              $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace,(-1)*convert($csv[$i][2], "kN", "lbf"));
			            }
			          $disptime[$k][] = array(convert($csv[$i][1]), convert($csv[$i][5], "mm", "in")-$initial_displace_top);
			            $i++;
			          }
			          $maxLoadVals[] = convert($csv[$maxLoadIndex][2], "kN", "lbf");
			        }
			        else{//cooper
			          $logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
			          $tmp = array_map('str_getcsv', file($filepath));
			          $tmp = explode("\r", $tmp[0][0]);
			          $csv = array();
			          for ($i=0; $i < sizeof($tmp)-1; $i++) {
			            $csv[] = explode("\t", $tmp[$i]);
			          }
			          $i = 37;//offset
			          $initial_displace = abs(convert($csv[$i][4], "mm", "in"));
			          $length = sizeof($csv);
			          while($i < $length-1){
			            $cycle = $csv[$i][1];
			          if(!isset($maxLoads[$cycle-1])){
			              $maxLoads[] = 0;
			            }
			            if(convert($csv[$i][5]) > $maxLoads[$cycle-1]){//the max load for each cycle
			              $maxLoads[$cycle-1] = $csv[$i][5];
			              //code to get the index of max load of first cycle
			              if($cycle == 1){
			                $maxLoadIndex = $i;
			              }
			            }
			            if($cycle == 1){
			              $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace, convert($csv[$i][5], "kN", "lbf"));
			            }
			            else if($cycle == 2){
			              $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace, convert($csv[$i][5], "kN", "lbf"));
			            }
			            $i++;
			          }

			          $maxLoadVals[] = $csv[$maxLoadIndex][5];
			          // $maxLoadVals[] = convert($csv[$maxLoadIndex][5], "kN", "lbf");

				        if($_SESSION['top'] === "Yes"){
				        	$disptime[$k] = array();
					          $temp_arr = explode('	',$logfile[15][0]);
					          $temp_time = stamp2sec($temp_arr[1]);
					          $temp_disp = convert($temp_arr[2]);
					          for ($i=15; $i < sizeof($logfile)-1; $i++) {
					            $temp_arr = explode('	', $logfile[$i][0]);
					        $disptime[$k][$i-15] = array(stamp2sec($temp_arr[1]) - $temp_time, convert(preg_replace('/\s+/', '', $temp_arr[2])));
					          }
				        }
			        }

			        //formula for normalized (currLoad / firstLoad)
			        for($i = 0; $i < sizeof($maxLoads); $i++){
			          $normLoads[$k][] = $maxLoads[$i]/$maxLoads[0];
			        }
			        //echo json_encode($first2cycles);
			        //code to calculate the power regression coeficient
			        $valtop= 0;
			        $valbot = 0;
			        for($j = 0; $j < count($normLoads[$k]); $j++){
			          if($normLoads[$k][$j] > 0){
			            $valtop += log($j+1) * log(abs($normLoads[$k][$j]));
			            $valbot += log($j+1) * log($j+1);
			          }
			        }
			        $coeff[] = abs($valtop/$valbot);
			      //code for r squared
			      $valtop= 0;
			        $valbot = 0;
			      for($j = 0; $j < count($normLoads[$k]); $j++){
			        $model[$k][] = array($j+1, pow($j+1, -$coeff[$k]));
			          $valtop += ($normLoads[$k][$j] - pow($j+1, -$coeff[$k]))*($normLoads[$k][$j] - pow($j+1, -$coeff[$k])); //(yi - yreal)^2
			          $valbot += ($normLoads[$k][$j])*($normLoads[$k][$j]);
			        }
			      //$r2[] = 0.99;
			      $r2[] = 1 - $valtop/$valbot;
			        //code to calculate the area
			        $area[] = 0;
			        for($i = 1; $i <= ($maxLoadIndex - 21); $i++){
			          $area[$k] += (($firstCycle[$k][$i-1][1] + $firstCycle[$k][$i][1])*abs($firstCycle[$k][$i][0]-$firstCycle[$k][$i-1][0])) / 2;
			        }
			        $fenergy[] = $area[$k] / ($thickness*$width);


			    }// For loop ends
			    $toReturn['r2'] = $r2;
				$toReturn['firstCycle'] = $firstCycle;
				$toReturn['secondCycle'] = $secondCycle;
				$toReturn['maxLoadVals'] = $maxLoadVals;
				$toReturn['fenergy'] = $fenergy;
				$toReturn['coeff'] = $coeff;
				$toReturn['normLoads'] = $normLoads;
				$toReturn['maxLoadVals'] = $maxLoadVals;
				$toReturn['maxIndex'] = $maxLoadIndex;
				if($_SESSION['top'] === "Yes"){
					$toReturn['disptime'] = $disptime;
				}
				$toReturn['repetitions'] = 1;
			}
			else if($response -> num_rows > 0){
                //lims already exists
                $toReturn['error'] = "LIMS value already exists";
            }
			else if($fromCache){
				$toReturn['error'] = "LIMS value doesn't exist" . $_SESSION['username'];
			}
            else{
                //everythijng ok
                if($_FILES['rawData']['error'][0] == UPLOAD_ERR_OK){
					$_SESSION['rawfile'] = array();
					$_SESSION['logfile'] = array();
					$toReturn['repetitions'] = $_POST['numofspec'];
					$response = false;
					for($i=0; $i<$_POST['numofspec']; $i++){
						$tmp_name = $_FILES["rawData"]["tmp_name"][$i];
						$directory = 'rawfiles';
		        		$name = $_FILES['rawData']['name'][$i];
						$_SESSION['rawfile'][$i] = "$directory/$name";
				        move_uploaded_file($tmp_name, "$directory/$name");
						if($_POST['device'] != 'AMPT' AND $_SESSION['top'] === "Yes"){
							// Log File
							$tmp_name = $_FILES["logfile"]["tmp_name"][$i];
							$directory = 'rawfiles';
							$name = $_FILES['logfile']['name'][$i];
							$_SESSION['logfile'][$i] = "$directory/$name";
							move_uploaded_file($tmp_name, "$directory/$name");
							$sql = "INSERT INTO cache(lims, path, log, type, username) VALUES('$lims', '".$_SESSION['rawfile'][$i]."', '".$_SESSION['logfile'][$i]."', '".$_POST['device']."', '".$_SESSION['username']."')";
						}
						else{
							$sql = "INSERT INTO cache(lims, path, type, username) VALUES('$lims', '".$_SESSION['rawfile'][$i]."', '".$_POST['device']."', '".$_SESSION['username']."')";
						}
						$toReturn['sql'] = $sql;
						$response = $conn->query($sql);
					}
					if($response){
						$_SESSION['submitted'] = true;
						include("conversions.php");
					    //uncoment for deployment
					    $thickness = $_SESSION['thickness'];
					    $width = $_SESSION['width'];

					    $firstCycle = array();
					    $secondCycle = array();
					    $normLoads = array();
					    $maxLoadVals = array();
					    $disptime = array();
					    $coeff = array();
					    $area = array();
					    $fenergy = array();
					    $r2 = array();
					    $model = array();



					    for ($k=0; $k < $_SESSION['numofspec']; $k++) {
					        $filepath = $_SESSION['rawfile'][$k];

					        $firstCycle[] = array();
					        $secondCycle[] = array();

					        $maxLoads = array();
					        $normLoads[] = array();
					        $maxLoadIndex = -1;
					        if($_SESSION['type'] == "Shedworks"){
					          $csv = array_map('str_getcsv', file($filepath));
					          //get the first value of displacement
					          if($_SESSION['top'] === "Yes"){
					          	$logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
					          }
					          //number of rows
					          $offset = 21;
					          $initial_displace = convert($csv[$offset][0]);
					          $length = sizeof($csv);
					          $cycle = 0;
					          for($i = $offset; $i < $length; $i++){
					            $cycle = floor(($i - $offset) / 250);//number of rows ber cylcle in shedworks
					            if(!isset($maxLoads[$cycle])){
					              $maxLoads[] = 0;
					            }
					            if(convert($csv[$i][1]) > $maxLoads[$cycle]){//the max load for each cycle
					              $maxLoads[$cycle] = $csv[$i][1];
					              //code to get the index of max load of first cycle
					              if($cycle == 0){
					                $maxLoadIndex = $i;
					              }
					            }
					            //this part is for the first loop graph
					            if($i < 250 + $offset+1){
					              $firstCycle[$k][] = array(convert($csv[$i][0])-$initial_displace, convert($csv[$i][1]));
					            }
					            //this part is for the second loop graph
					            elseif($i < 500 + $offset+1){
					              $secondCycle[$k][] = array(convert($csv[$i][0])-$initial_displace, convert($csv[$i][1]));
					            }
					          }
					          $maxLoadVals[] = $csv[$maxLoadIndex][1];
							  if($_SESSION['top'] === "Yes"){
							  	$disptime[$k] = array();
						          $temp_arr = explode('	',$logfile[15][0]);
						          $temp_time = stamp2sec($temp_arr[1]);
						          $temp_disp = $temp_arr[2];

						          for ($i=15; $i < sizeof($logfile)-1; $i++) {

						            $temp_arr = explode('	', $logfile[$i][0]);
						            $disptime[$k][$i-15][0] = (stamp2sec($temp_arr[1]) - $temp_time); //time
						            $disptime[$k][$i-15][1] = ($temp_arr[2] - $temp_disp)/10 * 0.0393701; //disp
						          }
							  }
					        }
					        else if($_SESSION['type'] == "AMPT"){
					          $csv = array_map('str_getcsv', file($filepath));
					          $i = 1;//offset
					          $length = sizeof($csv);
					          $initial_displace = convert($csv[$i][4], "mm", "in");
					        $initial_displace_top = convert($csv[$i][5], "mm", "in");
					          while($i < $length){
					            $cycle = $csv[$i][0];
					          if(!isset($maxLoads[$cycle-1])){
					              $maxLoads[] = 0;
					            }
					            if((convert($csv[$i][2])) > $maxLoads[$cycle-1]){//the max load for each cycle
					              $maxLoads[$cycle-1] = $csv[$i][2];
					              //code to get the index of max load of first cycle
					              if($cycle == 1){
					                $maxLoadIndex = $i;
					              }
					            }
					            if($cycle == 1){
					              $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace,(-1)*convert($csv[$i][2], "kN", "lbf"));
					            }
					            else if($cycle == 2){
					              $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace,(-1)*convert($csv[$i][2], "kN", "lbf"));
					            }
					          $disptime[$k][] = array(convert($csv[$i][1]), convert($csv[$i][5], "mm", "in")-$initial_displace_top);
					            $i++;
					          }
					          $maxLoadVals[] = convert($csv[$maxLoadIndex][2], "kN", "lbf");
					        }
					        else{//cooper
					          $logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
					          $tmp = array_map('str_getcsv', file($filepath));
					          $tmp = explode("\r", $tmp[0][0]);
					          $csv = array();
					          for ($i=0; $i < sizeof($tmp)-1; $i++) {
					            $csv[] = explode("\t", $tmp[$i]);
					          }
					          $i = 37;//offset
					          $initial_displace = abs(convert($csv[$i][4], "mm", "in"));
					          $length = sizeof($csv);
					          while($i < $length-1){
					            $cycle = $csv[$i][1];
					          if(!isset($maxLoads[$cycle-1])){
					              $maxLoads[] = 0;
					            }
					            if(convert($csv[$i][5]) > $maxLoads[$cycle-1]){//the max load for each cycle
					              $maxLoads[$cycle-1] = $csv[$i][5];
					              //code to get the index of max load of first cycle
					              if($cycle == 1){
					                $maxLoadIndex = $i;
					              }
					            }
					            if($cycle == 1){
					              $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace, convert($csv[$i][5], "kN", "lbf"));
					            }
					            else if($cycle == 2){
					              $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in")-$initial_displace, convert($csv[$i][5], "kN", "lbf"));
					            }
					            $i++;
					          }

					          $maxLoadVals[] = $csv[$maxLoadIndex][5];
					          // $maxLoadVals[] = convert($csv[$maxLoadIndex][5], "kN", "lbf");

					        $disptime[$k] = array();
					          $temp_arr = explode('	',$logfile[15][0]);
					          $temp_time = stamp2sec($temp_arr[1]);
					          $temp_disp = convert($temp_arr[2]);
					          for ($i=15; $i < sizeof($logfile)-1; $i++) {
					            $temp_arr = explode('	', $logfile[$i][0]);
					        $disptime[$k][$i-15] = array(stamp2sec($temp_arr[1]) - $temp_time, convert(preg_replace('/\s+/', '', $temp_arr[2])));
					          }
					        }

					        //formula for normalized (currLoad / firstLoad)
					        for($i = 0; $i < sizeof($maxLoads); $i++){
					          $normLoads[$k][] = $maxLoads[$i]/$maxLoads[0];
					        }
					        //echo json_encode($first2cycles);
					        //code to calculate the power regression coeficient
					        $valtop= 0;
					        $valbot = 0;
					        for($j = 0; $j < count($normLoads[$k]); $j++){
					          if($normLoads[$k][$j] > 0){
					            $valtop += log($j+1) * log(abs($normLoads[$k][$j]));
					            $valbot += log($j+1) * log($j+1);
					          }
					        }
					        $coeff[] = abs($valtop/$valbot);
					      //code for r squared
					      $valtop= 0;
					        $valbot = 0;
					      for($j = 0; $j < count($normLoads[$k]); $j++){
					        $model[$k][] = array($j+1, pow($j+1, -$coeff[$k]));
					          $valtop += ($normLoads[$k][$j] - pow($j+1, -$coeff[$k]))*($normLoads[$k][$j] - pow($j+1, -$coeff[$k])); //(yi - yreal)^2
					          $valbot += ($normLoads[$k][$j])*($normLoads[$k][$j]);
					        }
					      //$r2[] = 0.99;
					      $r2[] = 1 - $valtop/$valbot;
					        //code to calculate the area
					        $area[] = 0;
					        //TODO make the "-21" not static should be offest deppends on each type of machine
					        for($i = 1; $i <= ($maxLoadIndex-21); $i++){
					          $area[$k] += (($firstCycle[$k][$i-1][1] + $firstCycle[$k][$i][1])*abs($firstCycle[$k][$i][0]-$firstCycle[$k][$i-1][0])) / 2;
					        }
					        $fenergy[] = $area[$k] / ($thickness*$width);


					    }// For loop ends
					    $toReturn['r2'] = $r2;
						$toReturn['firstCycle'] = $firstCycle;
						$toReturn['secondCycle'] = $secondCycle;
						$toReturn['maxLoadVals'] = $maxLoadVals;
						$toReturn['fenergy'] = $fenergy;
						$toReturn['coeff'] = $coeff;
						$toReturn['normLoads'] = $normLoads;
						$toReturn['maxLoadVals'] = $maxLoadVals;
						$toReturn['maxIndex'] = $maxLoadIndex;
						if($_SESSION['top'] === "Yes"){
							$toReturn['disptime'] = $disptime;
						}
					}
					else{
						$toReturn['error'] = "There was a problem with response from Database, please try again.";
					}
				}
				else{
					$message = "";
					switch ($_FILES['rawData']['error']) {
			            case UPLOAD_ERR_INI_SIZE:
			                $message = "The uploaded file exceeds the Max file size";
			                break;
			            case UPLOAD_ERR_FORM_SIZE:
			                $message = "The uploaded file exceeds the Max file size";
			                break;
			            case UPLOAD_ERR_PARTIAL:
			                $message = "The uploaded file was only partially uploaded";
			                break;
			            case UPLOAD_ERR_NO_FILE:
			                $message = "No file was uploaded";
			                break;
			            case UPLOAD_ERR_NO_TMP_DIR:
			                $message = "Missing a temporary folder on server, contact webmaster for help.";
			                break;
			            case UPLOAD_ERR_CANT_WRITE:
			                $message = "Failed to write file to disk";
			                break;
			            case UPLOAD_ERR_EXTENSION:
			                $message = "File upload stopped by extension";
			                break;

			            default:
			                $message = "Unknown upload error";
			                break;
			        }
					$toReturn['error'] = $message;
				}
            }
        }
	}
	else {
		$toReturn['error'] = "Something went wrong. please reload the page and try again.";
	}
	header('Content-Type: application/json');
	// $_SESSION = array('in'=>true);
	echo json_encode($toReturn);
?>
