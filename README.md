ledger stats is a simple browser-based frontend for ledger, a double-entry
accounting system with a command-line reporting interface.

## Demo

A live demonstration of ledger stats is available at [http://matthewturland.com/ledger-stats](http://matthewturland.com/ledger-stats).
It uses [stan.txt](https://github.com/peterkeen/Ledger-Tools-Demo/blob/master/stan.txt "stan.txt at master from peterkeen/Ledger-Tools-Demo - GitHub") from [Ledger-Tools-Demo](https://github.com/peterkeen/Ledger-Tools-Demo "peterkeen/Ledger-Tools-Demo - GitHub") by [peterkeen](https://github.com/peterkeen "peterkeen's Profile - GitHub") as its data source at the suggestion of [@SamStuck](https://twitter.com/#!/SamStuck "Sam Stuck \(SamStuck\) on Twitter") and [@zrail](https://twitter.com/#!/zrail "zrail \(zrail\) on Twitter").

## Requirements

- A web server with [PHP](http://php.net) 5.3.0+ installed
- [ledger](https://github.com/jwiegley/ledger) 3.0.0 (may work with older versions)

## Installation

Simply place the `ledger-stats` directory in a publicly accessible location
within your web server's document root and access the `index.php` file it
contains.

## Configuration

Configuration settings are stored in `config.php` in the same directory as
`index.php`. `config.sample.php` contains a sample set of configuration
settings that can be modified to suit your preferences. Each setting is
documented to indicate the purpose of its value. In particular, the `file`
setting must be set to the paths of one or more data files in the standard
ledger or ledger XML formats that ledger stats will query.

## Usage

Optionally enter in a beginning or ending date or amount, one or more accounts
to include or exclude, or an optional depth limit. Account names must be
comma-separated. If no accounts are specified, all accounts are included by
default. To exclude an account, prefix an account name with a hyphen (`-`).
Begin typing a portion of an account name to have it autocomplete. When you've
entered all desired criteria, submit the form to see visualizations of the data
provided by plugins.

## Plugins

ledger stats provides the ability to filter transactions by account, date,
and amount. It then passes the filtered transactions to plugins, files in
the `plugins` directory that return a closure to execute. These closures are
executed in line to display whatever output they will. They have access to
the jQuery and Highcharts libraries. See the bundled files in the `plugins`
directory for examples.

## Dependencies

The following dependencies are bundled with ledger stats:

- [jQuery](http://jquery.com) 1.6.2
- [jQuery UI](http://jqueryui.com) 1.8.16
- [Highcharts](http://highcharts.com) 2.1.6

Note that Highcharts is _not_ free for commercial use. For more information,
refer to its [licensing terms](http://www.highcharts.com/license).
