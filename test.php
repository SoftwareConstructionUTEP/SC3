<?php
ini_set('memory_limit', '512M'); //Change memory use
$file_path = 'test.csv'; //Make it dynamic
$csv = array_map('str_getcsv', file($file_path));

$numofcycles = $csv[sizeof($csv)-1][0];
$j = 1;
$curr_max = -10000.00;
$newarr = array();

for ($i=1; $i < sizeof($csv); $i++) {
  $csv[$i][6] = $csv[$i][2] * (-1); // Multiply Load(kN) by -1
  if($csv[$i][0] == $j){
    if($csv[$i][6] > $curr_max){
      $curr_max = $csv[$i][6];
    }
  }else{
    $newarr[$j][0] = $curr_max;
    $newarr[$j][1] = $j;
    if($curr_max == 0){ //lbf
      $lbf = "";
    }else{
      $lbf = $curr_max * 1000 * 0.224809;
      if($j == 1){
        $lbfrone = $lbf;
      }
      $newarr[$j][2] = $lbf;
    }
    if($lbf == ""){//norm
      $norm = "";
    }else{
      $norm = $lbf / $lbfrone;
      $newarr[$j][3] = $norm;
    }
    // mm to in, displ
    $mmtoin = $csv[$i][4] * 0.0393701;
    $newarr[$j][4] = $mmtoin;
    // zero disp, in
    // if($j == 1){
    //   $mmtoinrone = $mmtoin;
    // }
    // $res = $mmtoin - $mmtoinrone;
    // $newarr[$i][5] = $res;
    //
    // // Load, lbs
    // $loadlbf = $csv[$i][2] * 0.224809 * (-1);
    // $newarr[$i][6] = $loadlbf;

    $curr_max = -1000;
    $j++;
    // $i--;
  }
}

// var_dump($newarr);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html>
        <head>
        	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        	<title>AMPT - OT Data Reducer</title>
        	<link href="js/flot/examples/examples.css" rel="stylesheet" type="text/css">
          <link href="css/bootstrap.css" rel="stylesheet" type="text/css">
        	<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
        	<script language="javascript" type="text/javascript" src="js/flot/jquery.flot.js"></script>
        	<script type="text/javascript">

        	$(function() {

        		var d1 = [
            <?php
            $to = 40;
            for ($i=1; $i < $to; $i++) {
                if($i == $to-1){
                    echo '[' . $newarr[$i][1] . ',' . $newarr[$i][3] . ']';
                }else{
                  echo '[' . $newarr[$i][1] . ',' . $newarr[$i][3] . '],';
                }
            }
            ?>
            ];
            var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

        		// A null signifies separate line segments

        		var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];

        		$.plot("#placeholder", [ d1]);

        		// Add the Flot version string to the footer

        		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
        	});

        	</script>
        </head>
        <body>

        	<div id="header">
        		<h2>AMPT - OT Data Reducer</h2>
        	</div>

        	<div id="content">


            <table class="table">
              <thead>
                <th>Case</th>
                <th>Specimen</th>
                <th>Max Load, lbs</th>
                <th>Fracture Area</th>
                <th>Fracture Energy</th>
                <th>Coeff. (1st load)</th>
                <th>R2 (1st load)</th>
                <th>Number of Cycles</th>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>

        		<div class="demo-container">
        			<div id="placeholder" class="demo-placeholder"></div>
        		</div>

        	</div>


        </body>
        </html>
