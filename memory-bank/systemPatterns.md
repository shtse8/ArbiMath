<!-- Version: 1.2 | Last Updated: 2025-06-04 -->
# System Patterns

## Architecture
- Single-class library (`SotiNumber.php`) within the `Shtse8\SotiMath` namespace.
- Relies heavily on the PHP BCMath extension for arbitrary precision.

## Key Technical Decisions
- **Immutability:** `SotiNumber` instances are immutable.
- **BCMath Dependency:** Core calculations delegated to BCMath functions.
- **Error Handling:** Using exceptions (`DivisionByZeroError`, `ValueError`).
- **Type Safety:** Leveraging PHP 8.0 strict types and type hinting.
- **Logarithm Calculation:** Using normalization and pre-calculated `LN10` constant for `ln()` and `log()` methods.

## Design Patterns
- **Immutable Object.**
- **Wrapper/Facade** (for BCMath).
- **Constant** (for `LN10`).

## Potential Issues / Areas for Review
- **`calculateLnTaylor()` Precision/Iterations:** The number of iterations in the Taylor series might still need tuning or a dynamic convergence check based on required precision.
- **PECL Operator Integration:** Standard comparison operators (`==`, `<`, etc.) should work if the extension is loaded, but this is not explicitly tested or guaranteed by the library itself after removing `__is_*` methods.