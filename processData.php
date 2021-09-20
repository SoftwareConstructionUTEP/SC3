<?php
/*session_start();
if(!isset($_SESSION['in']) OR !$_SESSION['in']){
  header('Location: login.php');
  exit();
}*/
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>OT-Reduction Worksheet</title>

  <!-- Bootstrap Core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="css/logo-nav.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="css/custom.css" rel="stylesheet">

  <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">


  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <style>
    label {
      margin-bottom: 0em;
      margin-top: 1em;
    }
  </style>


</head>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">

        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">
          <!-- <img src="http://placehold.it/150x50&text=Logo" alt=""> -->
        </a>
      </div>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <a target="_blank" href="http://ctis.utep.edu">
          <img src="img/ctis_transparent_white_2017.png" style=" max-width: 75px; max-height: 75px; margin-top: 3px;" align="right"> </img>
        </a>
        <a target="_blank" href="http://txdot.gov">
          <img src="img/txdotnewlogo.png" style="max-width: 75px; max-height: 75px; margin-top: 3px;" align="right"> </img>
        </a>
        <ul class="nav navbar-nav">
          <li>
            <a href="processData.php" style=" font-size: 16px; font-weight: bold;">Overlay Tester Analysis Tool v1.3 (09/11/17)</a> <!-- <a style="padding-top: 0px; padding-bottom: 0px;"> V1.0 </a> -->
            <!-- <a> hey </a> -->
            <!-- note: version 1.2 as of Wednesday, June 7th  -->
          </li>


          <!--
          <li>
          <a href="#">Services</a>
        </li>xaxes: [{
        font:{
        size:22,
        weight:"bold",
        color: 'black'
      }
    }],
    <li>
    <a href="#">Contact</a>
  </li> -->
        </ul>
      </div>
      <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
  </nav>
  <!-- Page Content -->
  <div class="container-fluid" style="margin-left: 2em;">
    <div class="row">
      <div class="col-lg-3 col-md-4 col-sm-12">
        <div class="panel panel-primary">
          <div class="panel-heading">Input</div>
          <div class="panel-body">

            <form autocomplete="off" id="otForm" enctype="multipart/form-data" action="ot_data_handler.php" method="POST">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="txdotuse" id="txdotuse" value="txdotuse" checked>
                  <input type="text" name="txdotuse2" id="txdotuse2" value="checked" hidden>
                  For TXDOT use only
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" value="false" name="cache" id="LIMScheck">
                  Retrieve existing data from LIMS
                </label>
              </div>
              <label>OT Device</label>
              <select class="form-control" type="text" id="device" name="device" required autofocus>
                <option>AMPT</option>
                <option>Cooper</option>
                <option>Shedworks</option>
              </select>

              <label>Number of Replicates</label>
              <select class="form-control" type="text" id="numofspec" name="numofspec" required readonly>
                <option>1</option>
                <!-- <option>2</option> -->
              </select>

              <label>TOP LVDT (Log File)</label>
              <select class="form-control" type="text" id="toplvdt" name="toplvdt" required>
                <option>Yes</option>
                <option selected>No</option>
              </select>

              <label>Specimen Thickness</label>
              <input class="form-control" type="number" step="0.01" id="specthickness" name="specthickness" placeholder="1.5" value="1.5" required readonly>

              <label>Specimen Width</label>
              <input class="form-control" type="number" step="0.01" id="specwidth" name="specwidth" placeholder="3" value="3" required readonly>

              <label>Mix Type</label>
              <input class="form-control" type="text" id="mixType" name="mixType">

              <label>Site Manager ID</label>
              <input class="form-control" type="text" id="smgr_id" name="smgr_id">

              <label>LIMS</label>
              <input class="form-control" type="text" id="LIMS" name="LIMS" value="12345588">

              <label>Aggregate Description</label>
              <input class="form-control" type="text" id="description" name="description">

              <label>Asphalt Grade</label>
              <input class="form-control" type="text" id="asphalt_grade" name="asphalt_grade">

              <label>Asphalt Source</label>
              <input class="form-control" type="text" id="asphalt_source" name="asphalt_source">

              <label>Asphalt Content</label>
              <input class="form-control" type="number" step="0.01" id="asphaltcontent" name="asphaltcontent" placeholder="Leave empty if unknown or not applicable">

              <label>% RAP</label>
              <input class="form-control" type="number" step="0.01" id="rappercent" name="rappercent" placeholder="Leave empty if unknown or not applicable">

              <label>% RAS</label>
              <input class="form-control" type="number" step="0.01" id="raspercent" name="raspercent" placeholder="Leave empty if unknown or not applicable">

              <label>Operator</label>
              <input class="form-control" type="text" id="operator" name="operator">

              <label>Data Reduction Date</label>
              <input class="form-control" type="text" id="date" name="date" readonly>


              <hr style="border-color:#337ab7; border-width:1px; border-radius:25px;">

              <label>Source data</label>
              <input class="form-control" type="file" id="rawData" name="rawData[]" required multiple="multiple"> <br>
              <hr>

              <label id="peaks_label">Peaks (CSV)</label>
              <input class="form-control" type="file" id="peaks" name="peaks" required multiple>

              <label id="logfile_label">Log File (LOG)</label>
              <input class="form-control" type="file" id="logfile" name="logfile" required>

              <br><input class="btn btn-block btn-primary" type="submit" name="submit" id="submit">

            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-9 col-md-8 col-sm-12">
        <div class="col-lg-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Overlay Test Results</h3>
            </div>
            <table id="results" class="table table-striped table-bordered">
              <thead>
                <th>#</th>
                <th>File</th>
                <th>LIMS</th>
                <!-- <th>Case</th> -->
                <!-- <th>Specimen</th> -->
                <th>Max Load, lbs</th>
                <!-- <th>Fracture Area, lbs/in</th> -->
                <!--<th>Critical Fracture Energy</th> -->
                <th>Critical Fracture Energy, lbs in / in <sup>2</sup></th>
                <th>Crack Progression Rate</th>
                <!-- <th>R2</th> -->
                <th>Number of Cycles</th>
              </thead>
              <tbody>
                <?php
                if (false) {
                  for ($i = 1; $i <= $k; $i++) {

                    $warning = '';
                    if (round($r2[$i - 1], 2) < 0.9) {
                      $warning = '<span class="label label-warning">r<sup>2</sup> is less than 0.9</span>';
                    }

                    echo '<tr>
                    <td>' . $_SESSION['filename'] . '</td>
                    <td>' . $_SESSION['LIMS'] . '</td>
                    <td>' . round($maxLoadVals[$i - 1], 3) . '</td>
                    <td>' . round($fenergy, 3) . '</td>
                    <td>' . round($coeff, 3) . ' ' . $warning  . '</td>
                    <td>' . count($normLoads[$i - 1]) . '</td>
                    </tr>';

                    // echo '<tr>
                    //         <td>'.$i.'</td>
                    //         <td>1</td>
                    //         <td>'.round($maxLoadVals[$i-1],2).'</td>
                    //         <td>'.round($area[$i-1],2).'</td>
                    //         <td>'.round($fenergy[$i-1],2).'</td>
                    //         <td>'.round($coeff[$i-1],2).'</td>
                    //         <td>'.round($r2[$i-1], 2).'</td>
                    //         <td>'.count($normLoads[$i-1]).'</td>
                    //       </tr>';
                  }
                }
                ?>
              </tbody>
            </table>
          </div>

        </div>
        <div class="col-lg-12">
          <div class="panel panel-primary" style="border: none; -webkit-box-shadow: none; box-shadow: none;">
            <div class="panel-heading">
              <h3 class="panel-title"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span> Charts</h3>
            </div>
            <div class="panel-body">
              <?php
              if (false) {
                echo "<div>";
              } else {
                echo "<div id=\"start\" class='alert alert-info' role='alert'>
                Submit a form to see the results.
                </div>
                <div id=\"chart_area\">";
              }
              ?>
              <select autocomplete="off" class="form-control" id="table_select">
                <option value="#chart3">OT Interaction Plot</option> <!-- Cambio -->
                <option value="#chart">Crack Initiation</option>
                <option value="#chart2">Crack Propagation</option>
                <option value="#chart4">Top LVDT</option>
              </select><br><br>
              <!-- Performance -->
              <div id="chart3" style="height: 600px;">
                <!--<div id="chart3_prepend">hey</div> <br>-->
                <div class="col-md-11 col-md-offset-1">
                  <!--  <div> <strong>Disclaimer:</strong> The Critical Fracture Energy (CFE) and the Crack Progression Rate (CPR) are the performance-based parameters from the overlay test that characterize the cracking susceptibility of asphalt mixtures.  Please utilize these parameters when characterizing the performance of asphalt mixtures and carrying out statistical calculations such as standard deviation and coefficient of variation. The Crack Resistance Index (CRI) is specified only for quality control and acceptance practices.  The CRI index is translated from the Crack Progression Rate.  The CRI shall not be used for statistical calculations. </div> <br> <br>-->
                  <div id="chart3_content" style="height: 500px;"> </div> <br> <br> <br>
                </div>

                <div class="col-lg-8 col-lg-offset-2">
                  <div class="progress">
                    <div class="progress-bar progress-bar-success progress-bar-striped" style="width: 50%">
                      <span class="sr-only">35% Complete (success)</span>
                    </div>
                    <div class="progress-bar progress-bar-danger progress-bar-striped" style="width: 50%">
                      <span class="sr-only">10% Complete (danger)</span>
                    </div>
                    <!--<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 33%">
                      <span class="sr-only">20% Complete (warning)</span>
                    </div>-->

                  </div>
                </div>
                <div class="col-lg-8 col-lg-offset-3">
                  <h4 style="display:inline-block;">Crack Resistant Mix</h4> <i class="fa fa-long-arrow-right fa-2x" style="display:inline-block;" aria-hidden="true"></i>
                  <h4 style="display:inline-block;">Lower Quality Mix</h4>
                </div>

              </div>
              <!-- Crack Initiation -->
              <div id="chart" class="chart-area" style="height: 500px; width: 87%"></div>
              <!-- Crack Propagation -->
              <div id="chart2" class="chart-area" style="height: 500px; width: 87%"></div>
              <!-- Performance -->
              <div id="chart4" class="chart-area" style="height: 500px; width: 87%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</body>
<!-- /.container -->

<!-- jQuery -->
<script src="js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>
<!--flot -->
<script src="js/flot/jquery.flot.js"></script>

<!-- custom css for the form -->
<script src="ot_JS_handler.js">
 
</script>


</html>