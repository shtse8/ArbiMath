<!-- Version: 1.1 | Last Updated: 2025-06-04 -->
# System Patterns

## Architecture
- Single-class library (`SotiNumber.php`) within the `Shtse8\SotiMath` namespace.
- Relies heavily on the PHP BCMath extension for arbitrary precision.

## Key Technical Decisions
- **Immutability:** `SotiNumber` instances are immutable. Arithmetic operations return new instances.
- **BCMath Dependency:** Core calculations are delegated to BCMath functions.
- **Error Handling:** Using exceptions (`DivisionByZeroError`, `ValueError`) for invalid operations (e.g., division by zero, invalid log inputs).
- **Type Safety:** Leveraging PHP 8.0 strict types and type hinting.

## Design Patterns
- **Immutable Object:** Ensures that the state of a `SotiNumber` object cannot be changed after creation.
- **Wrapper/Facade:** Wraps BCMath functions with a more object-oriented interface.

## Potential Issues / Areas for Review
- **`ln()` Implementation:** Current Taylor series might be suboptimal. Consider alternative algorithms (e.g., using properties of logarithms, AGM method) if high precision/performance is critical.
- **PECL Operator Integration:** The usage of `__is_*` magic methods needs clarification regarding the PECL Operator extension's actual interface.