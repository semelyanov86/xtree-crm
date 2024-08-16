jQuery(document).ready(function() {
    var plot1 = jQuery.jqplot ('durationBlock', [durations], {
          // Give the plot a title.
          title: 'Runtime of this Block',
        highlighter: {
                show: true,
                sizeAdjust: 7.5,
                tooltipAxes:"y",
                formatString:'%.0f ms'
              },
        cursor: {
                show: false
              },
          // You can specify options for all axes on the plot at once with
          // the axesDefaults object.  Here, we're using a canvas renderer
          // to draw the axis label which allows rotated text.
          axesDefaults: {
                labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer
          },
          // An axes object holds options for all axes.
          // Allowable axes are xaxis, x2axis, yaxis, y2axis, y3axis, ...
          // Up to 9 y axes are supported.
          axes: {
            // options for each axis are specified in seperate option objects.
            xaxis: {
                pad: 0,
                tickInterval:"1"
            },
            yaxis: {

                min:0,
                max:maxValue,
                autoscale:true,
                tickOptions:{
                    formatString:'%.0fms',

                  fontFamily: 'Arial',
                  fontSize: '10px',
                  angle: -30
                },
              label: "Runtime"
            }
          }
        });
});