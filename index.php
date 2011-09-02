<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

$config = require_once 'config.php';

$error = false;
if (!isset($config['file'])) {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
    $error = 'Please specify the path to a ledger XML file using the "file" configuration setting.';
} elseif (!is_readable($config['file'])) {
    $error = 'The file specified in the "file" configuration setting does not exist or cannot be read.';
} elseif (!get_postings($config['ledger'], $config['file'])) {
    $error = 'No transactions were found in the file specified in the "file" configuration setting.';
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
<textarea name="accounts" id="accounts" rows="4" cols="40"><?php echo isset($_GET['accounts']) ? htmlentities($_GET['accounts']) : ''; ?></textarea>
</div>

<input type="submit" value="Submit">

</form>

<?php endif; ?>

</div>

<?php if (!$error): ?>

<script type="text/javascript">
LedgerStats = {
    accounts: <?php echo json_encode(get_accounts($config['ledger'], $config['file'])); ?>,
    accountLimit: <?php echo isset($config['accountLimit']) ? $config['accountLimit'] : 10; ?>
};
</script>

<script type="text/javascript" src="js/jquery-ui/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/highcharts/js/highcharts.js"></script>
<script type="text/javascript" src="js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/ledger-stats.js"></script>

<?php

    if ($_GET) {
        $postings = search_postings($config['ledger'], $config['file'], $_GET);
        foreach (glob('plugins/*.php') as $plugin) {
            $callback = include $plugin;
            if (is_callable($callback)) {
                call_user_func($callback, $postings);
            }
        }
    }

?>
<?php endif; ?>

</body>
</html>
<?php

function get_postings($ledger, $file)
{
    static $postings = array();

    if (!isset($postings[$file])) {
        $cmd = $ledger . ' xml expenses liabilities -f ' . $file;
        $output = shell_exec($cmd);
        $xml = simplexml_load_string($output);
        $postings[$file] = array();
        foreach ($xml->transactions->transaction as $transaction) {
            $date = (string) $transaction->date;
            foreach ($transaction->posting as $posting) {
                $account = (string) $posting->account->name;
                $amount = (float) $posting->{'post-amount'}->amount->quantity;
                $postings[$file][] = (object) array(
                    'date' => $date,
                    'account' => $account,
                    'amount' => $amount,
                );
            }
        }
    }

    return $postings[$file];
}

function get_accounts($ledger, $file)
{
    static $accounts = null;

    if (!$accounts) {
        foreach (get_postings($ledger, $file) as $posting) {
            $accounts[$posting->account] = true;
        }
        $accounts = array_keys($accounts);
        sort($accounts);
    }

    return $accounts;
}

function search_postings($ledger, $file, $filters)
{
    $postings = get_postings($ledger, $file);

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
