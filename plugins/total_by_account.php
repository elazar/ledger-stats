<?php

return function($postings, $config) {
    $data = array();
    foreach ($postings as $posting) {
        if (!isset($data[$posting->account])) {
            $data[$posting->account] = 0;
        }
        $data[$posting->account] += $posting->amount;
    }

    $total = array_sum($data);
    $data = array_filter($data, function($amount) use ($total) {
        return ($amount / $total) >= .01;
    });
    if ($diff = $total - array_sum($data)) {
        $data['Other'] = $diff;
    }
    asort($data);

?>
<div id="total_by_account"></div>
<script type="text/javascript">
(function($) {
    var formatter = function() {
        return "<b>" + this.point.name + "</b>: " + this.y + " (" + Number(this.percentage).toFixed(1) + "%)";
    };
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: "total_by_account",
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: null
        },
        tooltip: {
            formatter: formatter
        },
        plotOptions: {
            pie: {
                cursor: "pointer",
                dataLabels: {
                    enabled: true,
                    color: Highcharts.theme.textColor || "#000000",
                    connectorColor: Highcharts.theme.textColor || "#000000",
                    formatter: formatter
                }
            }
        },
        series: [{
            type: "pie",
            data: [
                <?php
                $first = true;
                foreach ($data as $account => $amount):
                    if (!$first) {
                        echo ",\n";
                    }
                    $first = false;
                ?>
                [ <?php echo json_encode($account); ?>, <?php echo $amount; ?> ]<?php
                endforeach;
                ?>
            ]
        }]
    });
})(jQuery);
</script>
<?php

};
