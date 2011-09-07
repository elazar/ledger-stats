<?php

return function($postings, $config) {
    $data = array();
    $min = 0;
    foreach ($postings as $posting) {
        if (!isset($data[$posting->account])) {
            $data[$posting->account] = array();
        }
        if (!isset($data[$posting->account][$posting->date])) {
            $data[$posting->account][$posting->date] = 0;
        }
        if (!$min || $min > $posting->amount) {
            $min = $posting->amount;
        }
        $data[$posting->account][$posting->date] += $posting->amount;
    }

    foreach ($data as $account => $account_data) {
        $total = 0;
        uksort($account_data, function($a, $b) { return strtotime($a) - strtotime($b); });
        foreach ($account_data as $date => $amount) {
            $account_data[$date] = $total + $amount;
            $total += $amount;
        }
        $data[$account] = $account_data;
    }

?>
<div id="account_total_by_date"></div>
<script type="text/javascript">
(function($) {
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: "account_total_by_date",
            zoomType: "xy",
            spacingRight: 20
        },
        title: {
            text: "Account Total by Date"
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
            min: <?php echo $min; ?>,
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
        series: [
            <?php
            $account_first = true;
            foreach ($data as $account => $account_data):
                if (!$account_first):
                    echo ",\n";
                endif;
                $account_first = false;
            ?>
            {
                type: "area",
                name: <?php echo json_encode($account); ?>,
                pointInterval: 24 * 3600 * 1000, // 1 day
                data: [
                    <?php
                    $date_first = true;
                    foreach ($account_data as $date => $amount):
                        if (!$date_first):
                            echo ",\n";
                        endif;
                        $date_first = false;
                        $date = strtotime($date);
                    ?>
                    [ Date.UTC(<?php echo date('Y, ', $date), date('n', $date) - 1, date(', j', $date); ?>), <?php echo $amount; ?> ]<?php
                    endforeach;
                    ?>

                ]
            }
            <?php endforeach; ?>
        ]
   });
})(jQuery);
</script>
<?php
};
