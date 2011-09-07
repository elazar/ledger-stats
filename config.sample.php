<?php

$config = array();

/**
 * String containing the path to the ledger executable. Only needed if standard
 * ledger data files will be used, as opposed to ledger XML data files.
 *
 * Example: $config['ledger'] = '/usr/bin/ledger';
 */
$config['ledger'] = 'ledger';

/**
 * String containing one file path or array containing multiple file paths to
 * files in either the standard ledger or ledger XML formats. Note that the
 * "ledger" configuration setting will need to be set if files in the standard
 * ledger format are used and that files in the ledger XML format must use a
 * .xml file extension to be recognized as such.
 *
 * Examples:
 *
 * $config['file'] = '/home/user/ledger.dat';
 *
 * $config['file'] = array(
 *     '/home/user/ledger-2010.xml',
 *     '/home/user/ledger-2011.xml',
 * );
 */
$config['file'] = '/path/to/file';

/**
 * Integer containing the maximum number of account autocompletion suggestions
 * to display.
 *
 * Example: $config['accountLimit'] = 15;
 */
$config['accountLimit'] = 10;

/**
 * Array containing one or more short names for plugins to include in ledger
 * stats output. If none are specified, all plugins in the plugins directory
 * are used.
 *
 * Example:
 *
 * $config['plugin'] = array(
 *     'account_total_by_date',
 *     'month_total_by_account',
 *     'total_by_account',
 * );
 */
$config['plugin'] = array(
    'account_total_by_date',
    'month_total_by_account',
    'total_by_account',
    'total_by_date',
);

return $config;
