<?php

return function($postings) {
    $data = array();
    foreach ($postings as $posting) {
        if (!isset($data[$posting->date])) {
            $data[$posting->date] = 0;
        }
        $data[$posting->date] += $posting->amount;
    }

    uksort($data, function($a, $b) { return strtotime($a) - strtotime($b); });

?>
<div id="total_by_date"></div>
<script type="text/javascript">
(function($) {
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: "total_by_date",
            zoomType: "x",
            spacingRight: 20
        },
        title: {
            text: "Total by Date"
        },
        xAxis: {
            type: "datetime",
            maxZoom: 14 * 24 * 3600 * 1000, // 14 days
            title: {
                text: null
            }
        },
        yAxis: {
            title: {
                text: null
            },
            min: <?php echo min($data); ?>,
            startOnTick: false,
            showFirstLabel: false
        },
        tooltip: {
            shared: true
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            area: {
                lineWidth: 1,
                marker: {
                    enabled: false,
                    states: {
                        hover: {
                            enabled: true,
                            radius: 5
                        }
                    }
                },
                shadow: false,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                }
            }
        },
        series: [{
            type: "area",
            name: "Amount",
            pointInterval: 24 * 3600 * 1000, // 1 day
            data: [
                <?php
                $first = true;
                foreach ($data as $date => $amount):
                    if (!$first):
                        echo ",\n";
                    endif;
                    $first = false;
                    $strtotime = strtotime($date);
                ?>
                [ Date.UTC(<?php echo date('Y, ', $strtotime), date('n', $strtotime)-1, date(', j', $strtotime); ?>), <?php echo $amount; ?> ]<?php
                endforeach;
                ?>

            ]
        }]
   });
})(jQuery);
</script>
<?php
};
