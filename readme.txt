=== CalcWise – Financial Calculators ===
Contributors: mkrndmane
Tags: calculator, sip calculator, fd calculator, interest calculator, financial calculator
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Financial calculators for SIP, FD interest and more, with animated charts.

== Description ==

CalcWise adds financial calculators to your site. Visitors fill in a short form, such as the monthly amount, interest rate and duration, and the result appears as an animated chart.

Calculators:

* **SIP**: what a monthly investment grows to. `[calcwise type="sip"]`
* **Lump sum**: what a one-time investment grows to. `[calcwise type="lumpsum"]`
* **SWP**: what is left after fixed monthly withdrawals, and when the money runs out. `[calcwise type="swp"]`
* **Goal**: the monthly investment needed to reach a goal, after inflation. `[calcwise type="goal"]`
* **EMI**: the monthly instalment of a loan, with a yearly repayment schedule. `[calcwise type="emi"]`

Set any starting value or limit in the shortcode, e.g. `[calcwise type="emi" principal="2500000" years="15" years_max="25"]`: use the field name for its starting value and add `_min`, `_max` or `_step` for its limits.

Several calculators can be placed on one page.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the zip from Plugins → Add New → Upload.
2. Activate the plugin on the Plugins screen.
3. Go to Settings → CalcWise.

== Frequently Asked Questions ==

= Is the result financial advice? =

No. The calculators give estimates based on the values entered. They are not financial advice.

= Which libraries does CalcWise include? =

All bundled code is GPL-compatible, loaded from your own site (no CDN), with its readable source in the plugin:

* [financial-calculators](https://github.com/wpgenius/financial-calculators-js) 1.1.1 (GPL-3.0-or-later): formulas, number formatting and amounts in words. Source in `src/vendor/financial-calculators/`.
* [Chart.js](https://www.chartjs.org/) 4.6 (MIT): the charts.

= Is my data deleted when I delete the plugin? =

Only if "Delete data on uninstall" is ticked in the settings.

== Changelog ==

= 1.0.0 =

* Initial release.

