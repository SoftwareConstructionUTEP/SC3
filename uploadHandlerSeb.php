<?php
// SET ENVIRONMENT VARIABLES
ini_set('memory_limit', '1024M');
error_reporting(E_ALL);
session_start();
// value	constant
// 1	E_ERROR
// 2	E_WARNING
// 4	E_PARSE
// 8	E_NOTICE
// 16	E_CORE_ERROR
// 32	E_CORE_WARNING
// 64	E_COMPILE_ERROR
// 128	E_COMPILE_WARNING
// 256	E_USER_ERROR
// 512	E_USER_WARNING
// 1024	E_USER_NOTICE
// 32767	E_ALL
// 2048	E_STRICT
// 4096	E_RECOVERABLE_ERROR
// 8192	E_DEPRECATED
// 16384	E_USER_DEPRECATED

ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

// RETURN VARIABLE
$toReturn = array();

// WHEN SUBMIT IS ACTIVATED
if (isset($_POST['submit'])) {
    echo "All good here 1";
    include('utils.php');

    $fromCache = false;

    $_SESSION['top'] = $_POST['toplvdt'];
    $_SESSION['type'] = $_POST['device'];
    $_SESSION['thickness'] = $_POST['specthickness'];
    $_SESSION['width'] = $_POST['specwidth'];
    $_SESSION['numofspec'] = $_POST['numofspec'];

    $toReturn['top'] = $_POST['toplvdt'];

    // !  disabling LIMS check by generating random number instead - SG
    $lims = rand(0, 999999999);
    $toReturn['lims'] = $lims;
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

    for ($k = 0; $k < $_SESSION['numofspec']; $k++) {
        $filepath = $_SESSION['rawfile'][$k];

        $firstCycle[] = array();
        $secondCycle[] = array();
        $maxLoads = array();
        $normLoads[] = array();
        $maxLoadIndex = -1;

        // * SHEDWORKS
        if ($_SESSION['type'] == "Shedworks") {
            $csv = array_map('str_getcsv', file($filepath));
            //get the first value of displacement
            if ($_SESSION['top'] === "Yes") {
                $logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
            }
            //number of rows
            $offset = 21;
            $initial_displace = convert($csv[$offset][0]);
            $length = sizeof($csv);
            $cycle = 0;
            for ($i = $offset; $i < $length; $i++) {
                $cycle = floor(($i - $offset) / 250); //number of rows ber cylcle in shedworks
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
        }


        // * AMPT
        else if ($_SESSION['type'] == "AMPT") {
            $csv = array_map('str_getcsv', file($filepath));
            $i = 1; //offset
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
        }


        // * COOPER
        else {
            echo "All good here Cooper";
            // get the files
            $logfile = array_map('str_getcsv', file($_SESSION['logfile'][$k]));
            $tmp = array_map('str_getcsv', file($filepath));

            //put all lines into an array
            $tmp = explode("\r", $tmp[0][0]);
            $csv = array();

            // save the data into CSV array
            for ($i = 0; $i < sizeof($tmp) - 1; $i++) {
                $csv[] = explode("\t", $tmp[$i]);
            }

            $i = 40; //offset -> header of data in source file (txt file)
            $toReturn["init_displace_original"] = $csv[$i][4];
            $initial_displace = abs(convert($csv[$i][4], "mm", "in"));
            $length = sizeof($csv);

            // while ($i < $length - 1) {
            while ($i < $length) {
                $cycle = $csv[$i][1];
                if (!isset($maxLoads[$cycle - 1])) {
                    $maxLoads[] = 0;
                }
                if (convert($csv[$i][5]) > $maxLoads[$cycle - 1]) { //the max load for each cycle
                    $maxLoads[$cycle - 1] = $csv[$i][5];
                    //code to get the index of max load of first cycle
                    if ($cycle == 1) {
                        $maxLoadIndex = $i;
                    }
                }

                if ($cycle == 1) {
                    $firstCycle[$k][] = array(convert($csv[$i][4], "mm", "in") - $initial_displace, convert($csv[$i][5], "kN", "lbf"));
                } else if ($cycle == 2) {
                    $secondCycle[$k][] = array(convert($csv[$i][4], "mm", "in") - $initial_displace, convert($csv[$i][5], "kN", "lbf"));
                }
                $i++;
            }

            //$maxLoadVals[] = $csv[$maxLoadIndex][5];
            $maxLoadVals[] = convert($csv[$maxLoadIndex][5], "kN", "lbf");

            $disptime[$k] = array();
            $temp_arr = explode('	', $logfile[15][0]);
            $temp_time = stamp2sec($temp_arr[1]);
            $temp_disp = convert($temp_arr[2]);
            for ($i = 15; $i < sizeof($logfile) - 1; $i++) {
                $temp_arr = explode('	', $logfile[$i][0]);
                $disptime[$k][$i - 15] = array(stamp2sec($temp_arr[1]) - $temp_time, convert(preg_replace('/\s+/', '', $temp_arr[2])));
            }
        }


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
        //Maxload = 82
        //TODO make the "-21" not static should be offset deppends on each type of machine
        for ($i = 1; $i <= ($maxLoadIndex - 40); $i++) {
            $area[$k] += (($firstCycle[$k][$i - 1][1] + $firstCycle[$k][$i][1]) * abs($firstCycle[$k][$i][0] - $firstCycle[$k][$i - 1][0])) / 2;
            //break;
        }
        $toReturn["AREA"] = $area;

        $fenergy[] = $area[$k] / ($thickness * $width);
    }
    // For loop ends
    echo "All good here For loop ends";

    $toReturn['initial_displace'] = $initial_displace;
    $toReturn['r2'] = $r2;
    $toReturn['firstCycle'] = $firstCycle;
    $toReturn['secondCycle'] = $secondCycle;
    $toReturn['maxLoadVals'] = $maxLoadVals;
    $toReturn['fenergy'] = $fenergy;
    $toReturn['coeff'] = $coeff;
    $toReturn['normLoads'] = $normLoads;
    $toReturn['maxLoadVals'] = $maxLoadVals;
    $toReturn['maxIndex'] = $maxLoadIndex;

    if ($_SESSION['top'] === "Yes") {
        $toReturn['disptime'] = $disptime;
    }
} else {
    $toReturn['error'] = "There was a problem with response from Database, please try again.";
}
echo "All good here return";
// * Return values
header('Content-Type: application/json');
echo json_encode($toReturn);
