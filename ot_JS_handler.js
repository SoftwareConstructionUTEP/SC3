// plotting library is called FLOT https://www.flotcharts.org/

$("#LIMScheck").on("change", function () {
  if ($(this).is(":checked")) {
    $(this).val(true);
    $("input").attr("disabled", "");
    $("#LIMS").attr("disabled", null);
    $("#LIMScheck").attr("disabled", null);
    $("#submit").attr("disabled", null);
    $("#specthickness").attr("disabled", null);
    $("#specwidth").attr("disabled", null);
  } else {
    $(this).val(false);
    $("input").attr("disabled", null);
  }
});
//global var for colors of lines
var colors =  [
  "#000000",
  "rgb(0, 148, 255)", 
  "rgb(130, 174, 255)",
  "#A1D490", // dark green
  "#B2E6A1", // green
  "#CD88E3", // dark purple
  "#D599E8", // purple
  "#DEA96D", // dark beige
  "#FCD2A2", // beige
  "#E83186", // dark pink
  "#F071AC", // pink
  "#0000EE", // blue
  "#008080", // teal
  "#00C5CD" // turqoise
]

$(document).ready(function () {
  var results_table_counter = 1;
  $("#chart_area").hide();

  // input - form handling
  $("#logfile").attr("required", false);
  $("#logfile_label").hide();
  $("#logfile").hide();
  $("#peaks").attr("required", false);
  $("#peaks_label").hide();
  $("#peaks").hide();
  $("#date").val(Date());

  $("#device").change(function () {
    temp = $("#device").val();
    if (temp == "AMPT") {
      $("#logfile").attr("required", false);
      $("#logfile_label").hide();
      $("#logfile").hide();

      $("#peaks").attr("required", false);
      $("#peaks_label").hide();
      $("#peaks").hide();
    } else if (temp == "Cooper") {
      // $('#toplvdt').val('Yes');

      // $('#logfile').attr('required', true);
      // $('#logfile_label').show();
      // $('#logfile').show();

      $("#peaks").attr("required", false);
      $("#peaks_label").hide();
      $("#peaks").hide();
    } else {
      //Shedworks
      // Overlay Top LVDT (LOG)
      if ($("#toplvdt").val() == "Yes") {
        $("#logfile").attr("required", true);
        $("#logfile_label").show();
        $("#logfile").show();
      }
      // Overlay Data Peaks File (CSV)
      $("#peaks").attr("required", true);
      $("#peaks_label").show();
      $("#peaks").show();
    }
  });

  $("#toplvdt").change(function () {
    temp = $("#toplvdt").val();
    if (temp == "No") {
      $("#logfile").attr("required", false);
      $("#logfile_label").hide();
      $("#logfile").hide();
    } else if ($("#device").val() != "AMPT") {
      //Shedworks
      // Overlay Top LVDT (LOG)
      $("#logfile").attr("required", true);
      $("#logfile_label").show();
      $("#logfile").show();
    }
  });
  //var myCanvas = plot.getCanvas();
  //var image = myCanvas.toDataURL();
  //image = image.replace("image/png","image/octet-stream");
  //document.location.href=image;

  $("#chart").hide();
  $("#chart2").hide();
  $("#chart4").hide();
  $("#table_select").on("change", function () {
    $("#chart").hide();
    $("#chart2").hide();
    $("#chart3").hide();
    $("#chart4").hide();

    $($("#table_select").val()).fadeIn();
  });

  $("#txdotuse").change(function (data) {
    if ($("#txdotuse").is(":checked") == false) {
      $("#txdotuse2").val("notchecked");
      $("#smgr_id").attr("disabled", true);
      $("#smgr_id").attr("required", false);

      $("#LIMS").attr("disabled", true);
      $("#LIMS").attr("required", false);

      $("#LIMScheck").attr("disabled", true);
    } else {
      $("#txdotuse2").val("checked");
      $("#smgr_id").attr("disabled", false);
      $("#smgr_id").attr("required", true);

      $("#LIMS").attr("disabled", false);
      $("#LIMS").attr("required", true);

      $("#LIMScheck").attr("disabled", false);
    }
  });

  $("#otForm").on("submit", function (e) {

    var data = new FormData($("form")[0]);
    data.append("submit", true);
    e.preventDefault();

    $.ajax({
      url: $(this).attr("action"),
      type: "POST",
      data: data,
      cache: false,
      contentType: false,
      processData: false,
    }).done(function (data_arr) {

      console.log("Data Returned:");
      console.log(data_arr);


      data_arr.forEach(data => {
        if (data.hasOwnProperty("error")) {
          alert(data.error);
          return
        }
      });
    

        $("#chart").empty();
        $("#chart2").empty();
        $("#chart3_content").empty();
        $("#chart4").empty();
        $("#chart_area").show();
        $("#start").hide();

        // optimized for multiple files
        // populates top table with data - make dynamic - more dynamic
        for (var i = 0; i < data_arr.length; i++) {
          $("#results")
            .children("tbody")
            .append(
              "<tr>\
          <td>" +
                results_table_counter +
                "</td>\
                  <td>" +
                  data_arr[i].filename +
                "</td>\
                  <td>" +
                  data_arr[i].lims +
                "</td>\
                  <td>" +
                ((data_arr[i].maxLoadVals * 100) / 100).toFixed(3) +
                "</td>\
                  <td>" +
                ((data_arr[i].fenergy * 100) / 100).toFixed(3) +
                "</td>\
                  <td>" +
                ((data_arr[i].coeff * 100) / 100).toFixed(3) +
                "</td>\
                  <td>" +
                (data_arr[i].normLoads[0].length -2 ) +
                "</td></tr>"
            );
          results_table_counter++;
        }

      
        var normLoads = [];
        var firstAndSecond = [];
        
        var curr_index = 0;
        data_arr.forEach(element => {

          var norm = element.normLoads;
          var fenergy = element.fenergy;
          var coeff = element.coeff;

          //format the data for plots
          var normLoad = $.map(norm, function (n, i) {
            //crack propagation
            var arr = [];
            arr.push(
              {
                data: $.map(n, function (m, j) {
                  return [[j, m]];
                }),
                label: "Raw Data #" + (curr_index+1),
              },
              {
                data: $.map(n, function (m, j) {
                  return [[j, Math.pow(j, -coeff)]];
                }),
                label: "Calculated Load",
              }
            );
            return arr;
          });

          normLoad.forEach(dict => {
            normLoads.push(dict)
          });
          curr_index++
        });



        // first and second cycle
        let color_index = 0;
        data_arr.forEach(data => {
    
          // first cycle
            firstAndSecond.push({
              data: data.firstCycle.pop(),
              label: "First Loop #"  + (color_index + 1) ,
              color: colors[color_index],
            }); // crack propagation

          color_index = color_index + 1;
          
  
          // // second cycle
          // for (var i = 0; i < data.secondCycle.length; i++) {
          //   firstAndSecond.push({
          //     data: data.secondCycle[i],
          //     label: "Second Loop #" + (i + 1),
          //     color: colors[i],
          //   }); // crack initiation
          // }
  
          
        });

        var specimen = [];
        color_index = 0;

        data_arr.forEach(data => {

          let sp_data =  {
            data: [[data.coeff, data.fenergy]],
            label: "Specimen #" + (color_index+1),
            color: colors[color_index],
            points: {
              show: true,
              radius: 8,
              fillColor: colors[color_index],
              symbol: "circle",
            },
            // xaxis: 1,
          }
          color_index++;
          specimen.push(sp_data)
        });

 

        
        // CRACK PROPAGATION CHART
        $.plot("#chart2", normLoads, {
          yaxis: {
            min: 0,
            max: 1.0,
          },
          yaxes: [
            {
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          xaxes: [
            {
              max: 300,
              min: 0,
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          grid: {
            hoverable: true, //IMPORTANT! this is needed for tooltip to work
            },
          tooltip: true
        });
        var xaxisLabel = $("<div class='axisLabel xaxisLabel'></div>")
          .text("Number of Cycles")
          .appendTo($("#chart2"));
        var yaxisLabel = $("<div class='axisLabel yaxisLabel'></div>")
          .text("Normalized Load")
          .appendTo($("#chart2"));

          // CRACK INITIATION CHART
        $.plot("#chart", firstAndSecond, {
          xaxis: {
            min: 0,
            max: 0.03,
          },
          xaxes: [
            {
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          yaxes: [
            {
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          legend: {
            position: "se",
          },
          grid: {
            hoverable: true, //IMPORTANT! this is needed for tooltip to work
            },
          tooltip: true
        });

        xaxisLabel = $("<div class='axisLabel xaxisLabel'></div>")
          .text("Displacement, in.")
          .appendTo($("#chart")); //more space between this and graph, use css

        yaxisLabel = $("<div class='axisLabel yaxisLabel'></div>")
          .text("Load (lbs)")
          .appendTo($("#chart"));



        // // OT INTERACTION PLOT CHART
        $.plot("#chart3_content",
        specimen,
        {
          xaxes: [
            /*{
                  position: "top",
                  max: 100,
                  min: 0,
                  font:{ size:22, weight:"bold", color: 'black'}
                },*/
            {
              /*inverseTransform: function (v) { return -v; },*/
              position: "bottom",
              tickColor: "#000000",
              tickLength: 12,
              max: 1,
              min: 0,
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          yaxes: [
            {
              min: 0,
              max: 6,
              tickDecimals: 0,
              font: {
                size: 22,
                weight: "bold",
                color: "black",
              },
            },
          ],
          grid: {
            markingsStyle: "dashed",
            markings: [
              {
                xaxis: {
                  from: 0,
                  to: 0.5,
                },
                color: "rgb(104, 185, 67)",
                lineWidth: 2,
              }, //green
              //{xaxis: {from:  30, to: 70}, color: "rgb(233, 222, 66)", lineWidth: 2},//yellow
              {
                xaxis: {
                  from: 0.5,
                  to: 1,
                },
                color: "rgb(199, 96, 86)",
                lineWidth: 2,
              }, //red
              {
                xaxis: {
                  from: 0.5,
                  to: 0.5,
                },
                color: "red",
                lineWidth: 5,
              }, //vertical line
              {
                xaxis: {
                  from: 0,
                  to: 100,
                },
                yaxis: {
                  from: 1,
                  to: 1,
                },
                color: "black",
                lineWidth: 2,
              },
              {
                xaxis: {
                  from: 0,
                  to: 100,
                },
                yaxis: {
                  from: 3,
                  to: 3,
                },
                color: "black",
                lineWidth: 2,
              },
            ],
            /*markingsStyle: 'solid',
                markings: [
                {xaxis: {from: 0, to: 100}, yaxis: {from: 1, to: 1}, color: "black", lineWidth: 5}
                ]*/
          },
        }
        );

        var xaxisBottom = $(
          "<div class='axisLabel xaxisBottom' style='font-weight:bold;'></div>"
        ).text("Crack Progression Rate").appendTo($("#chart3_content"));
        var yaxisLabel = $(
          "<div class='axisLabel yaxisLabel' style='font-weight:bold;'></div>"
        ).html("Critical Fracture Energy, lbs*in/in  <sup>2</sup> ").appendTo($("#chart3_content"));
      
    });
  });
});
