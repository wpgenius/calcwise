=== CalcWise – Financial Calculators ===
Contributors: mkrndmane
Tags: calculator, sip calculator, emi calculator, financial calculator, investment calculator
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

SIP, lump sum, SWP, goal and EMI calculators with live results and charts. Add them with a block or a shortcode.

== Description ==

CalcWise adds financial calculators to your site. Visitors move a slider or type a value, and the results, amounts in words and chart update at once.

= Calculators =

* **SIP**: what a monthly investment grows to.
* **Lump sum**: what a one-time investment grows to.
* **SWP**: what is left after fixed monthly withdrawals, and when the money runs out.
* **Goal**: the monthly investment needed to reach a goal, after inflation.
* **EMI**: the monthly instalment of a loan, with a yearly repayment schedule.

= Add a calculator =

* **Block**: add the "CalcWise Calculator" block, or one of its variations (SIP calculator, EMI calculator, …), and pick the calculator. Its sidebar sets each field's starting value and limits for that block.
* **Shortcode**: `[calcwise type="sip"]`. Types: `sip`, `lumpsum`, `swp`, `goal`, `emi`.

Set a field's starting value or limits in the shortcode with the field name, plus `_min`, `_max` or `_step` for its limits:

`[calcwise type="emi" principal="2500000" years="15" years_max="25"]`

Several calculators can be placed on one page.

= Settings =

Settings → CalcWise:

* Currency symbol and its position, digit grouping (Indian: 12,34,567 or international: 1,234,567) and amounts in words.
* Main and second colour.
* The disclaimer under every calculator.
* Starting values and limits of each calculator for the whole site. A block or shortcode can still change them.

= Built for every visitor =

* Works with a keyboard and screen readers: every value has one labelled input, results are announced when they change, and each chart comes with a table of its values.
* Default colours meet WCAG AA contrast. Animations are turned off for visitors who prefer reduced motion.
* Translation-ready, including amounts in words.
* Loads only on pages that show a calculator, with no jQuery.

= Privacy =

CalcWise doesn't collect, store or send any visitor data. Calculations run in the visitor's browser, it sets no cookies and it loads nothing from other sites.

== Installation ==

1. In Plugins → Add New, search for "CalcWise", or upload the zip.
2. Activate the plugin.
3. Add the "CalcWise Calculator" block to a page, or paste a shortcode such as `[calcwise type="sip"]`.
4. Optionally, set the currency, colours and limits in Settings → CalcWise.

== Frequently Asked Questions ==

= Is the result financial advice? =

No. The calculators give estimates based on the values entered. They are not financial advice. You can change the disclaimer shown under every calculator in the settings.

= Can I use another currency? =

Yes. Set the currency symbol, its position and the digit grouping in Settings → CalcWise.

= Can I change the starting values and limits? =

Yes: for the whole site in Settings → CalcWise, and per calculator in the block sidebar or the shortcode.

= How do I translate the calculators? =

All text, including amounts in words and the short units on the chart (K, L, Cr, M), is translatable on translate.wordpress.org or with a plugin such as Loco Translate.

= Which libraries does CalcWise include? =

All bundled code is GPL-compatible, loaded from your own site (no CDN), with its readable source in the plugin:

* [financial-calculators](https://github.com/wpgenius/financial-calculators-js) 1.1.1 (GPL-3.0-or-later): formulas, number formatting and amounts in words. Source in `src/vendor/financial-calculators/`.
* [Chart.js](https://www.chartjs.org/) 4.6 (MIT): the charts.

Their licence notices are in `THIRD-PARTY-NOTICES.txt`.

The plugin's own source, including the block, is on [GitHub](https://github.com/wpgenius/calcwise).

= Is my data deleted when I delete the plugin? =

Only if "Delete data on uninstall" is ticked in the settings. On a multisite network, each site's own setting decides for that site.

== Screenshots ==

1. The SIP calculator: sliders, results in figures and words, and the growth chart.
2. The EMI calculator with the principal and interest split and a yearly repayment schedule.
3. The SWP calculator: balance and withdrawals year by year.
4. The CalcWise Calculator block and its sidebar in the editor.
5. Settings: currency, number format, colours, disclaimer and each calculator's limits.

== Changelog ==

= 1.0.0 =

* Initial release.

