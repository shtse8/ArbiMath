<!-- Version: 1.1 | Last Updated: 2025-06-04 -->
# Active Context

## Current Focus
- Refining `SotiNumber.php` based on initial analysis.
- Ensuring full PHP 8.0 compatibility and adherence to best practices.
- Documenting findings in Memory Bank.

## Recent Changes
- Analyzed `SotiNumber.php` for PHP 8.0 compatibility and improvements.
- Applied initial fixes:
    - Added `declare(strict_types=1);` and namespace `Shtse8\SotiMath;`.
    - Renamed `clone()` to `duplicate()`.
    - Added scalar type hints and return types (including `string` for `__toString`).
    - Added basic PHPDoc blocks.
    - Implemented division-by-zero checks for `div()` and `mod()`.
    - Improved `normalizeFloat`, `floor`, `ceil`, `abs`, `truncate`, `round`, `format`, `getHumanUnitIndex`, `toString` methods.
    - Added checks for invalid inputs in `ln()` and `log()`.
    - Added type hints for magic methods where applicable.

## Next Steps
1. Review the non-standard PECL Operator magic methods (`__is_*`). Decide whether to keep, remove, or replace them.
2. Investigate the efficiency and precision of the `ln()` Taylor series implementation.
3. Add comprehensive unit tests.
4. Update `README.md` to reflect PHP 8.0 compatibility and changes.
5. Commit the changes.

## Active Decisions
- Adopted the standard Memory Bank file structure.
- Renamed `clone` method to `duplicate`.
- Implemented basic PHP 8.0 compatibility fixes and type hinting.
- Added basic input validation and error handling for division/logarithms.