# financial-calculators (bundled)

Formulas, number formatting and amounts in words for CalcWise.

Source: https://github.com/wpgenius/financial-calculators-js
Version: 1.1.1
License: GPL-3.0-or-later

`financial-calculators.esm.js` is the unmodified ES module build from that
release. It is bundled into `build/runtime/index.js` and exposed to the
calculators as `window.calcwise.calc`.

To update: `bin/update-financial-calculators.sh vX.Y.Z`, then `npm run build`.
Don't edit the file here; change the library and release it instead.
