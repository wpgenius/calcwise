=== CalcWise – Financial Calculators ===
Contributors: mkrndmane
Tags: calculator, sip calculator, fd calculator, interest calculator, financial calculator
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Financial calculators for SIP, FD interest and more, with animated charts.

== Description ==

CalcWise adds financial calculators to your site. Visitors fill in a short form, such as the monthly amount, interest rate and duration, and the result appears as an animated chart.

Planned calculators:

* **SIP calculator**: what a monthly investment grows to.
* **FD calculator**: the maturity amount and interest earned on a fixed deposit.
* More calculators to follow.

CalcWise is in early development. This version has the settings page; the calculators arrive in the next release.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the zip from Plugins → Add New → Upload.
2. Activate the plugin on the Plugins screen.
3. Go to Settings → CalcWise.

== Frequently Asked Questions ==

= Is the result financial advice? =

No. The calculators give estimates based on the values entered. They are not financial advice.

= Which libraries does CalcWise include? =

All bundled code is GPL-compatible, loaded from your own site (no CDN), with its readable source in the plugin:

* [financial-calculators](https://github.com/wpgenius/financial-calculators-js) 1.0.0 (GPL-3.0-or-later): formulas, number formatting and amounts in words. Source in `src/vendor/financial-calculators/`.
* [Chart.js](https://www.chartjs.org/) 4.6 (MIT): the charts.

= Is my data deleted when I delete the plugin? =

Only if "Delete data on uninstall" is ticked in the settings.

== Changelog ==

= 1.0.0 =

* Initial release.

