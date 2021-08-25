<?php
include('utils.php');
include('conversions.php');
# INITIALIZATION
ini_set('memory_limit', '1024M');
error_reporting(0);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
session_start();

// WORKFLOW - ON SUBMIT
//  1 GRAB ALL VARIABLES NEEDED FROM _POST
//  2 CHECK TYPE OF OT DEVICE
//  3 PERFORM CALCULATIONS 
//  4 STORE RESULTS
//  5 RETURN DATA

$submitted_clicked = isset($_POST['submit']);

if ($submitted_clicked) {
   
    main();
}

function main()
{
    // * ARRAY TO STORE DATA FOR FRONTEND
    $toReturn = array();

    // * GET DATA FROM _POST
    $_SESSION['type'] = $_POST['device'];
    $_SESSION['thickness'] = $_POST['specthickness'];
    $_SESSION['width'] = $_POST['specwidth'];
    $_SESSION['numofspec'] = $_POST['numofspec'];
    $_SESSION['top'] = $_POST['toplvdt'];
    $LIMS = rand(0, 999999999);
    $_SESSION['LIMS'] = $LIMS;



    // * Save local copy of file
    save_file_data($LIMS);

    // run the whole show
    doMainWork($toReturn, $LIMS);
}

function save_file_data($LIMS)
{

    $_SESSION['rawfile'] = array();
    $_SESSION['logfile'] = array();
    $toReturn['repetitions'] = $_POST['numofspec'];
    $response = false;

    $i = 0;

    $tmp_name = $_FILES["rawData"]["tmp_name"];
    $directory = 'rawfiles';
    $name = $_FILES['rawData']['name'];
 
    $_SESSION['rawfile'] = "$directory/$name";
    
 
    move_uploaded_file($tmp_name, "$directory/$name");
    
    if ($_POST['device'] != 'AMPT' and $_SESSION['top'] === "Yes") {
        // Log File
        $tmp_name = $_FILES["logfile"]["tmp_name"][$i];
        $directory = 'rawfiles';
        $name = $_FILES['logfile']['name'][$i];
        $_SESSION['logfile'][$i] = "$directory/$name";
        move_uploaded_file($tmp_name, "$directory/$name");
    }
}

function doShedworks($filepath, $maxLoadVals, $disptime)
{
    $k = 0;
    $maxLoadIndex = -1;
    $csv = array_map('str_getcsv', file($filepath));
    $offset = 21;   //number of rows
    $initial_displace = convert($csv[$offset][0]);
    $length = sizeof($csv);
    $cycle = 0;

    //get the first value of displacement
    if ($_SESSION['top'] === "Yes") {
        $logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
    }

    for ($i = $offset; $i < $length; $i++) {
        $cycle = floor(($i - $offset) / 250); //number of rows per cycle in shedworks
        if (!isset($maxLoads[$cycle])) {
            $maxLoads[] = 0;
        }
        if (convert($csv[$i][1]) > $maxLoads[$cycle]) { //the max load for each cycle
            $maxLoads[$cycle] = $csv[$i][1];
            //code to get the index of max load of first cycle
            if ($cycle == 0) {
                $maxLoadIndex = $i;
            }
        }
        //this part is for the first loop graph
        if ($i < 250 + $offset + 1) {
            $firstCycle[$k][] = array(convert($csv[$i][0]) - $initial_displace, convert($csv[$i][1]));
        }
        //this part is for the second loop graph
        elseif ($i < 500 + $offset + 1) {
            $secondCycle[$k][] = array(convert($csv[$i][0]) - $initial_displace, convert($csv[$i][1]));
        }
    }

    $maxLoadVals[] = $csv[$maxLoadIndex][1];
    if ($_SESSION['top'] === "Yes") {
        $disptime[$k] = array();
        $temp_arr = explode('	', $logfile[15][0]);
        $temp_time = stamp2sec($temp_arr[1]);
        $temp_disp = $temp_arr[2];

        for ($i = 15; $i < sizeof($logfile) - 1; $i++) {

            $temp_arr = explode('	', $logfile[$i][0]);
            $disptime[$k][$i - 15][0] = (stamp2sec($temp_arr[1]) - $temp_time); //time
            $disptime[$k][$i - 15][1] = ($temp_arr[2] - $temp_disp) / 10 * 0.0393701; //disp
        }
    }
    $toReturn['maxIndex'] = $maxLoadIndex;
}

function doAMPT($filepath, $firstCycle, $secondCycle, $disptime, $maxLoadVals, $maxLoads)
{
    $csv = array_map('str_getcsv', file($filepath));
    $i = 1; //offset
    $k = 0;
    $length = sizeof($csv);
    $initial_displace = convert($csv[$i][4], "mm", "in");
    $initial_displace_top = convert($csv[$i][5], "mm", "in");
    while ($i < $length) {
        $cycle = $csv[$i][0];
        if (!isset($maxLoads[$cycle - 1])) {
            $maxLoads[] = 0;
        }
        if ((convert($csv[$i][2])) > $maxLoads[$cycle - 1]) { //the max load for each cycle
            $maxLoads[$cycle - 1] = $csv[$i][2];
            //code to get the index of max load of first cycle
            if ($cycle == 1) {
                $maxLoadIndex = $i;
            }
        }
        if ($cycle == 1) {
            $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in") - $initial_displace, (-1) * convert($csv[$i][2], "kN", "lbf"));
        } else if ($cycle == 2) {
            $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in") - $initial_displace, (-1) * convert($csv[$i][2], "kN", "lbf"));
        }
        $disptime[$k][] = array(convert($csv[$i][1]), convert($csv[$i][5], "mm", "in") - $initial_displace_top);
        $i++;
    }
    $maxLoadVals[] = convert($csv[$maxLoadIndex][2], "kN", "lbf");
    $toReturn['maxIndex'] = $maxLoadIndex;
}

function doCooper($fenergy, $filepath, $maxLoads, $disptime, $normLoads, $coeff, $maxLoadVals, $area, $r2, $model)
{
    //Time(s)    Cycle Number    LVDT1(mm)    LVDT2(mm)    Displacement(mm)    Load(kN)    Cycle Peak Load (kN)    Chamber Temperature (�C)    Dummy Temperature (�C)    CFE (J/m�)    CPR    CRI
    // 0            1               2               3           4                   5 ...
    $_TIME        = 0;
    $_CycleNum    = 1;
    $_LVDT1       = 2;
    $_LVDT2       = 3;
    $_Displacement = 4;
    $_Load        = 5;
    $_CyPeakLoad  = 6;
    $_ChamTemp    = 7;
    $_DummTemp    = 8;
    $_CFE         = 9;
    $_CPR         = 10;
    $_CRI         = 11;

    $csv = Array();
    $csv = getCSV($_SESSION['rawfile']);
    // return;
    $k = 0;
    $disptime[$k] = array();
    
    $i = 1;//Skip header row
    $initial_displace = convert($csv[$i][$_Displacement], "mm", "in");
    $length = sizeof($csv);
    $maxLoadIndex = 0;
    while ($i < $length -1) { //($i < $length -1)
        $cycle = $csv[$i][$_CycleNum];
        if (!isset($maxLoads[$cycle - 1])) {
            $maxLoads[] = 0;
        }
        if (convert($csv[$i][$_Load]) > $maxLoads[$cycle - 1]) { //the max load for each cycle
            $maxLoads[$cycle - 1] = $csv[$i][$_Load];
            //code to get the index of max load of first cycle
            if ($cycle == 1) {
                $maxLoadIndex = $i;
            }
        }
        if ($cycle == 1) {
            $firstCycle[$k][] = array(convert($csv[$i][$_Displacement], "mm", "in") - $initial_displace, convert($csv[$i][$_Load], "kN", "lbf"));
         }
         // else if ($cycle == 2) {
        //     $secondCycle[$k][] = array(convert($csv[$i][$_Displacement], "mm", "in") - $initial_displace, convert($csv[$i][$_Load], "kN", "lbf"));
        // }
        $i++;
    }
    
    //$maxLoadVals[] = $csv[$maxLoadIndex][5];
    $maxLoadVals[] = convert($csv[$maxLoadIndex][$_Load], "kN", "lbf");


    // $temp_disp = convert($temp_arr[2]);
    // for ($i = 15; $i < sizeof($logfile) - 1; $i++) {
    //     $temp_arr = explode('	', $logfile[$i][0]);
    //     $disptime[$k][$i - 15] = array(stamp2sec($temp_arr[1]) - $temp_time, convert(preg_replace('/\s+/', '', $temp_arr[2])));
    // }

    //formula for normalized (currLoad / firstLoad)
    for ($i = 0; $i < sizeof($maxLoads); $i++) {
        $normLoads[$k][] = $maxLoads[$i] / $maxLoads[0];
    }

    //code to calculate the power regression coeficient
    $valtop = 0;
    $valbot = 0;
    for ($j = 0; $j < count($normLoads[$k]); $j++) {
        if ($normLoads[$k][$j] > 0) {
            $valtop += log($j + 1) * log(abs($normLoads[$k][$j]));
            $valbot += log($j + 1) * log($j + 1);
        }
    }

    $coeff[] = abs($valtop / $valbot);
    //code for r squared
    $valtop = 0;
    $valbot = 0;
    for ($j = 0; $j < count($normLoads[$k]); $j++) {
        $model[$k][] = array($j + 1, pow($j + 1, -$coeff[$k]));
        $valtop += ($normLoads[$k][$j] - pow($j + 1, -$coeff[$k])) * ($normLoads[$k][$j] - pow($j + 1, -$coeff[$k])); //(yi - yreal)^2
        $valbot += ($normLoads[$k][$j]) * ($normLoads[$k][$j]);
    }


    //$r2[] = 0.99;
    $r2[] = 1 - $valtop / $valbot;
    //code to calculate the area
    $area[] = 0;
    //TODO make the "-21" or "40" not static should be offest deppends on each type of machine
    for ($i = 1; $i <= ($maxLoadIndex); $i++) {
        $area[$k] += (($firstCycle[$k][$i - 1][1] + $firstCycle[$k][$i][1]) * abs($firstCycle[$k][$i][0] - $firstCycle[$k][$i - 1][0])) / 2;
    }
    //echo $area[$k];
    $fenergy[] = $area[$k] / ($_SESSION['thickness'] * $_SESSION['width']);

    // Store results
    $toReturn['maxIndex'] = $maxLoadIndex;
    $toReturn['r2'] = $r2;
    $toReturn['firstCycle'] = $firstCycle;
    $toReturn['secondCycle'] = [];
    $toReturn['maxLoadVals'] = $maxLoadVals;
    $toReturn['fenergy'] = $fenergy;
    $toReturn['coeff'] = $coeff;
    $toReturn['normLoads'] = $normLoads;
    $toReturn['lims'] = $_SESSION['LIMS'];
    $toReturn['top'] = $_SESSION['top'];
    $toReturn['repetitions'] = $_POST['numofspec'];
    $toReturn['filename'] = $_FILES['rawData']['name'];


    returnData($toReturn);
}

function doMainWork($toReturn, $lims)
{

    try {
        // * MAIN VARIABLES USED
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
        $disptime = array();
        $maxLoads = array();

        $k = 0;
        $filepath = $_SESSION['rawfile'][$k];
        $_SESSION['file'] = $filepath;
        $toReturn['source_file'] = $filepath;
        // Find type and assign work
        if ($_SESSION['type'] == "Shedworks") {
            doShedworks($filepath, $maxLoadVals, $disptime);
        }
        if ($_SESSION['type'] == "AMPT") {
            doAMPT($filepath, $firstCycle, $secondCycle, $disptime, $maxLoadVals, $maxLoads);
        } else {
            doCooper($fenergy, $filepath, $maxLoads, $disptime, $normLoads, $coeff, $maxLoadVals, $area, $r2, $model, $toReturn, $lims);
        }
        if ($_SESSION['top'] === "Yes") {
            $toReturn['disptime'] = $disptime;
        }
    } catch (exception $e) {
        echo $e;
       
    }

    // Return data
    //returnData($toReturn); 
}

function returnData($toReturn)
{
    header('Content-Type: application/json');
    $file = fopen("toReturn.txt",'w');
    foreach ($toReturn as $key => $value) {
        # code...
        fwrite($file,"KEY: ".$key ."\tVALUE: ". $value);
        fwrite($file,"\n");
    }
    fclose($file);

    echo json_encode($toReturn);
    return;
}


function getCSV($path)
{
    // Open the File
    $file = fopen($path, "r");

    $i = 0;
    // as long as is not end of file continue loop through
    $lineData = [[]];
    while (!feof($file)) {
        // get the file string by line
        $thisLine = fgets($file);
        // Explode the line when there is  a  ", " 
        $lineData[$i] = explode(",", $thisLine);
        $i++;
 
    }

    //Write results to file
    $file2 = fopen("seeCSV.txt", "w");
    for ($i = 0; $i < count($lineData); $i++) {
        # code...
        for ($j = 0; $j < count($lineData[$i]); $j++) {
            # code...
            fwrite($file2, ($lineData[$i][$j]) . "    ");
        }
        if($i > 15){break;}
    }

    // close the File
    fclose($file);
    fclose($file2);
    return $lineData;

}
?>