//global var for colors of lines
var colors = [
  ["#000000", "000000"],
  ["rgb(0, 148, 255)", "rgb(130, 174, 255)"],
  ["#A1D490", "#B2E6A1"],
  ["#CD88E3", "#D599E8"],
  ["#DEA96D", "#FCD2A2"],
  ["#E83186", "#F071AC"],
];


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

$(document).ready(function () {
  var results_table_counter = 1;
  $("#chart_area").hide();

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
    }).done(function (returned) {
      console.log("Data Returned:");
      console.log(returned);

      /// start foreach
      var sp = []; // ? What is SP?
      returned.forEach((data) => {
        if (data.hasOwnProperty("error")) {
          alert(data.error);
        } else {
          $("#chart").empty();
          $("#chart2").empty();
          // $('#chart3_content').empty();
          $("#chart4").empty();
          $("#chart_area").show();
          $("#start").hide();

          // Update Table with results
          for (var i = 0; i < data.length; i++) {
            $("#results")
              .children("tbody")
              .append(
                "<tr>\
                                                    <td>" +
                  results_table_counter +
                  "</td>\
                                                    <td>" +
                  data.filename +
                  "</td>\
                                                    <td>" +
                  data.lims +
                  "</td>\
                                                    <td>" +
                  ((data.maxLoadVals[i] * 100) / 100).toFixed(3) +
                  "</td>\
                                                    <td>" +
                  ((data.fenergy * 100) / 100).toFixed(3) +
                  "</td>\
                                                    <td>" +
                  ((data.coeff * 100) / 100).toFixed(3) +
                  "</td>\
                                                    <td>" +
                  data.normLoads.length - 2 +
                  "</td></tr>"
              );
            results_table_counter++;
            }
          var norm = data.normLoads;
          var fenergy = data.fenergy;
          var coeff = data.coeff;

          //format the data for plots
          var normLoad = $.map(norm, function (n, i) {
            //crack propagation
            var arr = [];
            arr.push(
              {
                data: $.map(n, function (m, j) {
                  return [[j, m]];
                }),
                label: "Raw Data #" + (i + 1),
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

          var firstAndSecond = [];
          for (var i = 0; i < data.firstCycle.length; i++) {
            firstAndSecond.push({
              data: data.firstCycle[i],
              label: "First Loop #" + (i + 1),
              color: colors[i][0],
            }); // crack initiation
          }
          for (var i = 0; i < data.secondCycle.length; i++) {
            firstAndSecond.push({
              data: data.secondCycle[i],
              label: "Second Loop #" + (i + 1),
              color: colors[i][1],
            }); // crack initiation
          }

          var num_spe;
          for (var i = 0; i < data.length; i++) {
            sp.push({
              data: [[data.coeff, data.fenergy]],
              label: "Specimen #" + (i + 1),
              color: "black",
              points: {
                show: true,
                radius: 8,
                fillColor: colors[i][0],
                symbol: "circle",
              },
            });
            num_spe = i + 1;
            if (data.hasOwnProperty("disptime")) {
              var series = [];
              for (var i = 0; i < data.repetitions; i++) {
                series.push({
                  data: data.disptime[i],
                  color: colors[i][0],
                  label: "Displacement #" + (i + 1),
                });
              }
              //console.log(series);
              $.plot($("#chart4"), series, {
                yaxis: {
                  tickDecimals: 2,
                },
                xaxes: [
                  {
                    max: 100,
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
                tooltip: true,
              });
            }

            //plot the charts

            // CRACK PROPAGATION
            $.plot("#chart2", normLoad, {
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
            });
            var xaxisLabel = $("<div class='axisLabel xaxisLabel'></div>")
              .text("Number of Cycles")
              .appendTo($("#chart2"));
            var yaxisLabel = $("<div class='axisLabel yaxisLabel'></div>")
              .text("Normalized Load")
              .appendTo($("#chart2"));

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
            });
            // CRACK INITIATION
            var xaxisLabel = $("<div class='axisLabel xaxisLabel'></div>")
              .text("Displacement, in.")
              .appendTo($("#chart")); //more space between this and graph, use css
            var yaxisLabel = $("<div class='axisLabel yaxisLabel'></div>")
              .text("Load (lbs)")
              .appendTo($("#chart"));

            // OT INTERACTION PLOT
            var plot = $.plot(
              "#chart3_content",
              [
                {
                  data: sp[0]["data"],
                  label: "Specimen #" + num_spe,
                  color: "black",
                  points: {
                    show: true,
                    radius: 8,
                    fillColor: sp[0]["points"]["fillColor"],
                    symbol: "circle",
                  },
                  xaxis: 1,
                },
              ],
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
            )
              .text("Crack Progression Rate")
              .appendTo($("#chart3_content"));
            var yaxisLabel = $(
              "<div class='axisLabel yaxisLabel' style='font-weight:bold;'></div>"
            )
              .html("Critical Fracture Energy, lbs*in/in  <sup>2</sup> ")
              .appendTo($("#chart3_content"));
          }
        } //specimen fill color is hardcoded to 'black'
      }); // end foreach
      console.log(sp);
    }).error(error => {
      console.log(error)
    })

  });

  //! ////////////////////////////////////////////////////////////////

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
});
