<!-- Version: 1.2 | Last Updated: 2025-06-04 -->
# Active Context

## Current Focus
- Completing initial refactoring and improvements for `SotiNumber.php`.
- Preparing for unit testing and documentation updates.

## Recent Changes
- Removed non-standard PECL Operator magic methods (`__is_*`).
- Refactored `ln()` method:
    - Added `LN10` constant.
    - Implemented normalization strategy (`ln(x) = ln(y) + k * ln(10)`) for better performance/convergence.
    - Extracted Taylor series calculation into `calculateLnTaylor()` private method.
- Refactored `log()` method to use `LN10` constant for `log10` calculation.

## Next Steps
1. Add comprehensive unit tests for `SotiNumber` class using PHPUnit.
2. Update `README.md` to reflect PHP 8.0 compatibility, API changes (`duplicate`), and potentially revised PECL Operator information.
3. Commit the changes related to `ln()`/`log()` refactoring and `__is_*` removal.

## Active Decisions
- Removed non-standard `__is_*` comparison magic methods.
- Adopted normalization strategy using `LN10` constant to improve `ln()` calculation.
- Optimized `log()` for base 10 using `LN10`.