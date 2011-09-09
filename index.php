<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

$config = require_once 'config.php';

$error = false;
if (!isset($config['file'])) {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
    $error = 'Please specify the path to a ledger XML file using the "file" configuration setting.';
} else {
    $files = is_array($config['file']) ? $config['file'] : array($config['file']);
    if (!array_filter($files, 'is_readable')) {
        $error = 'Files specified in the "file" configuration setting do not exist or cannot be read.';
    } else {
        $postings = array();
        foreach ($files as $file) {
            $postings = array_merge($postings, get_postings($config['ledger'], $file));
        }
        if (!$postings) {
            $error = 'No transactions were found in the file specified in the "file" configuration setting.';
        }
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ledger stats</title>
    <link rel="stylesheet" type="text/css" href="js/jquery-ui/css/ui-lightness/jquery-ui-1.8.16.custom.css">
    <link rel="stylesheet" type="text/css" href="css/ledger-stats.css">
</head>
<body>

<div id="about" style="position: absolute; top: 0; left: 10px;">
<h1>About this Demo</h1>
<p style="width: 250px; text-align: left;"><a href="https://github.com/peterkeen/Ledger-Tools-Demo/blob/master/stan.txt" title="stan.txt at master from peterkeen/Ledger-Tools-Demo - GitHub">stan.txt</a> from <a href="https://github.com/peterkeen/Ledger-Tools-Demo" title="peterkeen/Ledger-Tools-Demo - GitHub">Ledger-Tools-Demo</a> by <a href="https://github.com/peterkeen" title="peterkeen's Profile - GitHub">peterkeen</a> is used as the data source for the demo at the suggestion of <a href="https://twitter.com/#!/SamStuck" title="Sam Stuck (SamStuck) on Twitter">@SamStuck</a> and <a href="https://twitter.com/#!/zrail" title="zrail (zrail) on Twitter">@zrail</a>. I owe all of them my thanks.</p>
</div>

<a href="http://github.com/elazar/ledger-stats"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://a248.e.akamai.net/assets.github.com/img/7afbc8b248c68eb468279e8c17986ad46549fb71/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub"></a>

<div id="container">

<h1>ledger stats</h1>

<?php if ($error): ?>

<p class="error"><?php echo nl2br(htmlentities($error)); ?></p>

<?php else: ?>

<form method="get" action="<?php echo htmlentities($_SERVER['SCRIPT_NAME']); ?>">

<div id="date-filter">
Filter by date range:
<label for="date-from">from</label>
<input type="text" size="12" id="date-from" name="date-from" value="<?php echo isset($_GET['date-from']) ? htmlentities($_GET['date-from']) : ''; ?>">
<label for="date-to">to</label>
<input type="text" size="12" id="date-to" name="date-to" value="<?php echo isset($_GET['date-to']) ? htmlentities($_GET['date-to']) : ''; ?>">
</div>

<div id="amount-filter">
<label for="amount-filter">Filter by amount:</label>
<label for="amount-from">from</label>
<input type="text" id="amount-from" name="amount-from" size="7" value="<?php echo isset($_GET['amount-from']) ? htmlentities($_GET['amount-from']) : ''; ?>">
<label for="amount-to">to</label>
<input type="text" id="amount-to" name="amount-to" size="7" value="<?php echo isset($_GET['amount-to']) ? htmlentities($_GET['amount-to']) : ''; ?>">
</div>

<div id="account-filter">
<label for="accounts">Filter by account:</label>
<textarea name="accounts" id="accounts" rows="4" cols="40"><?php echo isset($_GET['accounts']) ? htmlentities($_GET['accounts']) : 'Expenses'; ?></textarea>
</div>

<div id="depth-filter">
<label for="depth">Limit depth:</label>
<select name="depth" id="depth">
    <option value="">No limit</option>
<?php foreach (range(1, 10) as $depth): ?>
    <option value="<?php echo $depth; ?>"<?php if (!empty($_GET['depth']) && $_GET['depth'] == $depth): ?> selected="selected"<?php endif; ?>><?php echo $depth; ?></option>
<?php endforeach; ?>
</select>
</div>

<input type="submit" value="Submit">

</form>

<?php endif; ?>

</div>

<?php if (!$error): ?>

<script type="text/javascript">
LedgerStats = {
    accounts: <?php

    $accounts = get_accounts($postings);
    $all = array();
    foreach ($accounts as $account) {
        $split = explode(':', $account);
        foreach (range(1, count($split)) as $index) {
            $all[implode(':', array_slice($split, 0, $index))] = true;
        }
    }
    $accounts = array_keys($all);
    sort($accounts);
    echo json_encode($accounts);

    ?>,
    accountLimit: <?php echo isset($config['accountLimit']) ? $config['accountLimit'] : 10; ?>
};
</script>

<script type="text/javascript" src="js/jquery-ui/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/highcharts/js/highcharts.js"></script>
<script type="text/javascript" src="js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/ledger-stats.js"></script>

<?php

    if ($_GET) {

?>
<div id="tabs">
    <ul>
<?php

        $postings = filter_postings($postings, $_GET);

        $plugins = array();
        if (isset($config['plugin']) && is_array($config['plugin'])) {
            $plugins = array_map(function($plugin) {
                return 'plugins/' . $plugin . '.php';
            }, $config['plugin']);
            $plugins = array_filter($plugins, 'is_readable');
        }
        if (!$plugins) {
            $plugins = glob('plugins/*.php');
        }
        $plugins = array_combine(array_map(function($plugin) {
            return preg_replace('#^plugins/(.*)\.php$#', '$1', $plugin);
        }, $plugins), $plugins);

        foreach ($plugins as $plugin_name => $plugin_file) {

?>
        <li><a href="#<?php echo htmlentities($plugin_name, ENT_QUOTES); ?>-tab"><?php echo htmlentities(ucfirst(preg_replace('/\\_([a-z])/e', '" " . strtoupper("$1")', $plugin_name)), ENT_QUOTES); ?></a></li>
<?php

        }

?>
    </ul>
<?php

        foreach ($plugins as $plugin_name => $plugin_file) {

?>
    <div id="<?php echo htmlentities($plugin_name, ENT_QUOTES); ?>-tab">
<?php

            $callback = include $plugin_file;
            $plugin_config = array();
            foreach ($config as $key => $value) {
                $prefix = $plugin_name . '.';
                if (strpos($key, $prefix) === 0) {
                    $plugin_config[str_replace($prefix, '', $key)] = $value;
                }
            }
            if (is_callable($callback)) {
                call_user_func($callback, $postings, $plugin_config);
            }

?>
    </div>
<?php

        }

?>
</div>
<script type="text/javascript">
(function($) {
    $("#tabs").tabs();
})(jQuery);
</script>
<?php

    }

?>
<?php endif; ?>

</body>
</html>
<?php

function get_postings($ledger, $file)
{
    if (strpos($file, '.xml') !== false) {
        $output = file_get_contents($file);
    } else {
        $cmd = $ledger . ' xml -f ' . $file;
        $output = shell_exec($cmd);
    }
    $xml = simplexml_load_string($output);
    $postings = array();
    foreach ($xml->transactions->transaction as $transaction) {
        $date = (string) $transaction->date;
        foreach ($transaction->posting as $posting) {
            $account = (string) $posting->account->name;
            $amount = (float) $posting->{'post-amount'}->amount->quantity;
            $postings[] = (object) array(
                'date' => $date,
                'account' => $account,
                'amount' => $amount,
            );
        }
    }
    // There appears to be a bug in ledger xml where it duplicates a
    // transaction if that transaction contains more than two postings
    // (one debit, one credit)
    $postings = array_unique($postings, SORT_REGULAR);
    return $postings;
}

function get_accounts(array $postings)
{
    $accounts = array();
    foreach ($postings as $posting) {
        $accounts[$posting->account] = true;
    }
    $accounts = array_keys($accounts);
    sort($accounts);
    return $accounts;
}

function filter_postings(array $postings, $filters)
{
    if (!empty($filters['depth']) && ctype_digit($filters['depth'])) {
        $depth = max($filters['depth'], 1);
        $postings = array_map(function($posting) use ($depth) {
            $pattern = '((?:[^:]+)';
            if ($depth > 1) {
                $pattern .= '(?::[^:]+){' . max(1, $depth - 1) . '}';
            }
            $pattern .= ').*';
            $posting->account = preg_replace('/' . $pattern . '/', '$1', $posting->account);
            return $posting;
        }, $postings);
    }

    if (!empty($filters['amount-from']) && is_numeric($filters['amount-from'])) {
        $from = $filters['amount-from'];
        $postings = array_filter($postings, function($posting) use ($from) {
            return $posting->amount >= $from;
        });
    }

    if (!empty($filters['amount-to']) && is_numeric($filters['amount-to'])) {
        $to = $filters['amount-to'];
        $postings = array_filter($postings, function($posting) use ($to) {
            return $posting->amount <= $to;
        });
    }

    if (!empty($filters['date-from']) && $from = strtotime($filters['date-from'])) {
        $postings = array_filter($postings, function($posting) use ($from) {
            return (strtotime($posting->date) >= $from);
        });
    }

    if (!empty($filters['date-to']) && $to = strtotime($filters['date-to'])) {
        $postings = array_filter($postings, function($posting) use ($to) {
            return strtotime($posting->date) <= $to;
        });
    }

    if (!empty($filters['accounts'])) {
        $accounts = array_filter(preg_split('/\s*,\s*/', trim($filters['accounts'])));
        $remove = array();
        foreach ($accounts as $key => $account) {
            if (strpos($account, '-') === 0) {
                $remove[] = $key;
                $account = ltrim($account, '-');
                $postings = array_filter($postings, function($posting) use ($account) {
                    return stripos($posting->account, $account) === false;
                });
            }
        }
        foreach ($remove as $key) {
            unset($accounts[$key]);
        }
        $postings = array_filter($postings, function($posting) use ($accounts) {
            return (bool) preg_match('/' . implode('|', $accounts) . '/i', $posting->account);
        });
    }

    return $postings;
}

?>
