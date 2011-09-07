<?php

return function($postings) {
    $data = array();
    foreach ($postings as $posting) {
        $month = date('Y-M', strtotime($posting->date));
        if (!isset($data[$month])) {
            $data[$month] = array();
        }
        if (!isset($data[$month][$posting->account])) {
            $data[$month][$posting->account] = 0;
        }
        $data[$month][$posting->account] += $posting->amount;
    }
    uksort($data, function($a, $b) { return strtotime($a) - strtotime($b); });

    $accounts = get_accounts($postings);
    $data_by_account = array();
    foreach ($accounts as $account) {
        foreach ($data as $month => $month_data) {
            if (!isset($data[$month][$account])) {
                $data[$month][$account] = 0;
            }
            if (!isset($data_by_account[$account])) {
                $data_by_account[$account] = 0;
            }
            $data_by_account[$account] += $data[$month][$account];
        }
    }

    $total = array_sum($data_by_account);
    $data_by_account = array_filter($data_by_account, function($amount) use ($total) {
        return ($amount / $total) >= .01;
    });
    $accounts = array_keys($data_by_account);
    foreach ($data as $month => $month_data) {
        $remove = array();
        foreach ($month_data as $account => $amount) {
            if (!in_array($account, $accounts)) {
                $remove[] = $account;
            }
        }
        foreach ($remove as $account) {
            unset($data[$month][$account]);
        }
    }

?>
<div id="month_total_by_account"></div>
<script type="text/javascript">
(function($) {
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: "month_total_by_account",
            defaultSeriesType: "bar"
        },
        title: {
            text: "Monthly Total by Account"
        },
        xAxis: {
            categories: <?php echo json_encode(array_keys($data)); ?>
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            }
        },
        tooltip: {
            formatter: function() {
                return this.series.name + ": " + this.y;
            }
        },
        plotOptions: {
            series: {
                stacking: "normal"
            },
        },
        series: [
            <?php
            $account_first = true;
            foreach ($accounts as $account):
                if (!$account_first):
                    echo ",\n";
                endif;
                $account_first = false;
                $account_data = array();
                foreach ($data as $month => $month_data):
                    $account_data[] = $month_data[$account];
                endforeach;
            ?>
            {
                name: <?php echo json_encode($account); ?>,
                data: <?php echo json_encode($account_data); ?>
            }<?php endforeach; ?>
        ]
   });
})(jQuery);
</script>
<?php
};
