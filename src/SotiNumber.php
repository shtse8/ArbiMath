<?php

declare(strict_types=1);

namespace Shtse8\SotiMath;

use DivisionByZeroError;
use ValueError;

/**
 * A class for arbitrary precision mathematics using BCMath.
 * Provides immutable objects for calculations.
 */
class SotiNumber
{
    private const INTERNAL_SCALE = 20;
    private const ITERATIONS = 100;
    private const HUMAN_UNITS = [
        3 => 'K', 6 => 'M', 9 => 'G', 12 => 'T', 15 => 'P', 18 => 'E'
    ];
    // ln(10) to 40 decimal places + a few guard digits
    private const LN10 = '2.302585092994045684017991454684364207601101488';

    /** @var string The internal value as a string. */
    private string $value;

    /**
     * Constructor.
     *
     * @param string $value The initial numeric value as a string (e.g., "123.45", "1.23E+10").
     */
    public function __construct(string $value = "0")
    {
        // Set BCMath scale for all operations within this scope
        bcscale(self::INTERNAL_SCALE);
        $this->value = $this->normalizeFloat($value);
    }

    /**
     * Normalizes a string potentially in scientific notation to a standard decimal string.
     *
     * @param string $value The input string.
     * @return string The normalized numeric string.
     */
    private function normalizeFloat(string $value): string
    {
        // Trim whitespace
        $value = trim($value);
        // Handle scientific notation (e.g., 1.23E+10)
        if (str_contains($value, 'E')) {
            $parts = explode("E", $value, 2);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                 // Ensure the exponent is an integer for bcpow
                $exponent = (string)(int)$parts[1];
                return bcmul($parts[0], bcpow('10', $exponent));
            }
             // Handle potential case like "E5" or "5E" - treat as invalid/zero? Or throw error?
             // For now, let's return '0' for invalid scientific notation.
             // A stricter approach might throw an InvalidArgumentException.
             // Also handle lowercase 'e'
             if (str_contains($value, 'e')) {
                 $parts = explode("e", $value, 2);
                 if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                     $exponent = (string)(int)$parts[1];
                     return bcmul($parts[0], bcpow('10', $exponent));
                 }
             }
             // If format is invalid, return '0' or consider throwing an exception
             return '0';
        }
        // Basic validation for non-scientific notation
        if (!is_numeric($value)) {
             // Return '0' or throw InvalidArgumentException
             return '0';
        }
        return $value;
    }


    /**
     * Creates a new instance with the same value.
     * Renamed from clone() due to PHP keyword conflict.
     *
     * @return self A new SotiNumber instance.
     */
    public function duplicate(): self
    {
        return new self($this->value);
    }


    /**
     * Adds a number to this number.
     *
     * @param string|SotiNumber $delta The number to add.
     * @return self A new SotiNumber instance representing the sum.
     */
    public function add(string|SotiNumber $delta): self
    {
        $normalizedDelta = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);
        return new self(bcadd($this->value, $normalizedDelta));
    }

    /**
     * Subtracts a number from this number.
     *
     * @param string|SotiNumber $delta The number to subtract.
     * @return self A new SotiNumber instance representing the difference.
     */
    public function sub(string|SotiNumber $delta): self
    {
        $normalizedDelta = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);
        return new self(bcsub($this->value, $normalizedDelta));
    }

    /**
     * Multiplies this number by another number.
     *
     * @param string|SotiNumber $delta The number to multiply by.
     * @return self A new SotiNumber instance representing the product.
     */
    public function mul(string|SotiNumber $delta): self
    {
        $normalizedDelta = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);
        return new self(bcmul($this->value, $normalizedDelta));
    }

    /**
     * Divides this number by another number.
     *
     * @param string|SotiNumber $delta The divisor.
     * @return self A new SotiNumber instance representing the quotient.
     * @throws DivisionByZeroError If the divisor is zero.
     */
    public function div(string|SotiNumber $delta): self
    {
        $normalizedDelta = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);
        if (bccomp($normalizedDelta, '0') === 0) {
            throw new DivisionByZeroError("Division by zero");
        }
        $result = bcdiv($this->value, $normalizedDelta);
        // bcdiv returns null on failure (like division by zero, handled above)
        // but let's be safe
        if ($result === null) {
             // This case should ideally not be reached due to the check above
             throw new ValueError("Division failed for an unknown reason.");
        }
        return new self($result);
    }

    /**
     * Calculates the modulus of this number by another number.
     *
     * @param string|SotiNumber $delta The divisor.
     * @return self A new SotiNumber instance representing the modulus.
     * @throws DivisionByZeroError If the divisor is zero (ValueError in PHP 8+ for bcmod).
     */
    public function mod(string|SotiNumber $delta): self
    {
        $normalizedDelta = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);
        // bcmod throws ValueError on division by zero in PHP 8.0+
        try {
            $result = bcmod($this->value, $normalizedDelta);
             // bcmod returns null if modulus is non-integer in PHP < 8.0
             // In PHP 8.0+, it should throw ValueError for non-int modulus too.
             // Let's handle null just in case, though the ValueError catch is primary for PHP 8+
             if ($result === null) {
                 throw new ValueError("Modulus operation resulted in null, potentially invalid non-integer modulus.");
             }
            return new self($result);
        } catch (ValueError $e) {
            // Check if the error message indicates division by zero
            if (str_contains($e->getMessage(), 'Argument #2 ($divisor) is zero')) {
                 throw new DivisionByZeroError("Modulo by zero", 0, $e);
            }
            // Re-throw other ValueErrors
            throw $e;
        }
    }

    /**
     * Raises this number to the power of another number.
     *
     * @param string|SotiNumber $delta The exponent.
     * @return self A new SotiNumber instance representing the result.
     */
    public function pow(string|SotiNumber $delta): self
    {
        $exponent = $delta instanceof SotiNumber ? $delta->value : $this->normalizeFloat($delta);

        // Check if the exponent has a fractional part
        if (str_contains($exponent, '.')) {
            // bcmod with 1 returns the fractional part, or 0 if integer
            if (bccomp(bcmod($exponent, '1'), '0') !== 0) {
                 throw new ValueError("Exponent must be an integer for pow() method. Fractional exponents are not currently supported.");
            }
             // If fractional part is zero (e.g., "3.0"), truncate it
             $exponent = bcadd($exponent, '0', 0);
        }

        // bcpow requires an integer exponent.
        return new self(bcpow($this->value, $exponent));
    }

    /**
     * Calculates the natural logarithm (base e).
     * Uses normalization to bring the value closer to 1 for faster Taylor series convergence.
     * ln(x) = ln(y * 10^k) = ln(y) + k * ln(10)
     *
     * @return self A new SotiNumber instance representing the natural logarithm.
     * @throws ValueError If the number is not positive.
     */
    public function ln(): self
    {
        // Check for non-positive input
        if ($this->isSmallerOrEqual('0')) {
            throw new ValueError("Natural logarithm is only defined for positive numbers.");
        }
        // ln(1) = 0
        if ($this->isEqual('1')) {
            return new self('0');
        }

        // Normalize x to y * 10^k where y is in [1, 10)
        $x_str = $this->value;
        $k = 0; // Exponent for 10^k

        // Find exponent k
        if (bccomp($this->abs()->value, '1', 0) === -1) { // 0 < x < 1
            // Find position of first non-zero digit after decimal point
            if (str_contains($x_str, '.')) {
                $decimalPart = explode('.', $x_str, 2)[1];
                $k = -(strspn($decimalPart, '0') + 1);
            } else {
                 // This case should not happen for 0 < x < 1 if normalized correctly, but handle defensively
                 $k = 0; // Or throw error?
            }
        } else { // x >= 1
            $integerPart = explode('.', $x_str, 2)[0];
            // Handle negative sign if present, although ln is for positive numbers
            $integerPart = ltrim($integerPart, '-');
            $k = strlen($integerPart) - 1;
        }

        // Calculate y = x / 10^k
        $powerOf10 = bcpow('10', (string)$k);
        $y = bcdiv($x_str, $powerOf10); // y is now in [0.1, 1) or [1, 10)

        // Calculate ln(y) using Taylor series (now y is closer to 1)
        $ln_y = $this->calculateLnTaylor(new self($y));

        // Calculate k * ln(10)
        $k_ln10 = bcmul((string)$k, self::LN10);

        // Result: ln(y) + k * ln(10)
        return $ln_y->add($k_ln10);
    }

    /**
     * Calculates the natural logarithm using Taylor series.
     * Assumes the input $y is relatively close to 1 for better convergence.
     * Formula: ln(y) = 2 * sum[ (1/(2n+1)) * ((y-1)/(y+1))^(2n+1) ] for n=0 to inf
     *
     * @param self $y The number (expected to be close to 1).
     * @return self The natural logarithm of $y$.
     */
    private function calculateLnTaylor(self $y): self
    {
        // Use a slightly higher scale for intermediate calculations
        $internalScale = self::INTERNAL_SCALE + 5;
        bcscale($internalScale); // Set scale for this method

        $limit = bcpow('10', (string)(-self::INTERNAL_SCALE)); // Convergence limit
        $result = '0';
        $termBase = bcdiv(bcsub($y->value, '1'), bcadd($y->value, '1')); // (y-1)/(y+1)
        $powerOfTermBase = $termBase; // First power is ^1
        $iteration = 0;

        do {
            $divisor = (string)(2 * $iteration + 1);
            $term = bcdiv($powerOfTermBase, $divisor);

            $previousResult = $result;
            $result = bcadd($result, $term);

            // Prepare for next iteration: power^n * base^2
            $powerOfTermBase = bcmul($powerOfTermBase, bcmul($termBase, $termBase));
            $iteration++;

            // Check for convergence or max iterations
            $delta = bcsub($result, $previousResult);
            if (bccomp(ltrim($delta, '-'), $limit) === -1 || $iteration > self::ITERATIONS * 2) { // Increased iterations limit
                 break;
            }

        } while (true);

        bcscale(self::INTERNAL_SCALE); // Restore original scale
        return new self(bcmul('2', $result)); // Multiply by 2 at the end
    }

/**
 * Calculates the logarithm to a specified base.
 *
 * @param string|SotiNumber $base The base of the logarithm.
 * @return self A new SotiNumber instance representing the logarithm.
 * @throws ValueError If base is non-positive or equals 1.
 */
    public function log(string|SotiNumber $base): self
    {
        $baseNum = $base instanceof SotiNumber ? $base : new self($base);

        if ($baseNum->isSmallerOrEqual('0') || $baseNum->isEqual('1')) {
            throw new ValueError("Logarithm base must be positive and not equal to 1.");
        }

        // Optimization for log10
        if ($baseNum->isEqual('10')) {
            // log10(x) = ln(x) / ln(10)
            $ln_x = $this->ln();
            $ln10Num = new self(self::LN10); // Use the constant
            // Division by zero check for LN10 is not needed as it's a known constant > 0
            return $ln_x->div($ln10Num);
        }

        // General case: log_base(x) = ln(x) / ln(base)
        $ln_x = $this->ln();
        $ln_base = $baseNum->ln();
        // ln(base) cannot be zero because base != 1 (checked above)
        return $ln_x->div($ln_base);
    }

    /**
     * Rounds the number down to the nearest integer (floor value).
     *
     * @return self A new SotiNumber instance representing the floor value.
     */
    public function floor(): self
    {
        // bcadd with scale 0 truncates towards zero for positive, away for negative.
        // We need floor (always round down).
        if ($this->isNegative() && bccomp($this->value, bcadd($this->value, '0', 0)) !== 0) {
            // If negative and has fractional part, subtract 1 after truncating towards zero
            return new self(bcadd($this->value, '-1', 0));
        } else {
            // If positive or integer, truncating towards zero is the floor
            return new self(bcadd($this->value, '0', 0));
        }
    }

    /**
     * Rounds the number up to the nearest integer (ceiling value).
     *
     * @return self A new SotiNumber instance representing the ceiling value.
     */
    public function ceil(): self
    {
        // bcadd with scale 0 truncates towards zero. We need ceil (always round up).
        if ($this->isPositive() && bccomp($this->value, bcadd($this->value, '0', 0)) !== 0) {
            // If positive and has fractional part, add 1 after truncating towards zero
            return new self(bcadd($this->value, '1', 0));
        } else {
             // If negative or integer, truncating towards zero is the ceil
            return new self(bcadd($this->value, '0', 0));
        }
    }

    /**
     * Returns the absolute value of the number.
     *
     * @return self A new SotiNumber instance representing the absolute value.
     */
    public function abs(): self
    {
        // Using bccomp is safer than string manipulation for "-0" cases etc.
        return $this->isNegative() ? $this->mul('-1') : $this->duplicate();
    }

    /**
     * Truncates the number to a specified number of decimal places.
     *
     * @param int $precision The number of decimal places (non-negative).
     * @return self A new SotiNumber instance representing the truncated value.
     * @throws ValueError If precision is negative.
     */
    public function truncate(int $precision = 0): self
    {
        if ($precision < 0) {
            throw new ValueError("Precision must be a non-negative integer.");
        }
        // bcadd with the number itself and the desired scale effectively truncates.
        return new self(bcadd($this->value, '0', $precision));
    }


    /**
     * Rounds the number to a specified number of decimal places (half up).
     *
     * @param int $precision The number of decimal places (non-negative).
     * @return self A new SotiNumber instance representing the rounded value.
     * @throws ValueError If precision is negative.
     */
    public function round(int $precision = 0): self
    {
        if ($precision < 0) {
            throw new ValueError("Precision must be a non-negative integer.");
        }
        // Standard rounding (half up) using bcadd/bcsub trick
        $offset = bcpow('10', (string)(-($precision + 1)), self::INTERNAL_SCALE); // 10^-(p+1)
        $offset = bcmul($offset, '5'); // 0.5 * 10^-p

        if ($this->isNegative()) {
            $roundedValue = bcsub($this->value, $offset, $precision);
        } else {
            $roundedValue = bcadd($this->value, $offset, $precision);
        }
        return new self($roundedValue);
    }

    /**
     * Checks if this number is equal to another number.
     *
     * @param string|SotiNumber $arg The number to compare against.
     * @return bool True if equal, false otherwise.
     */
    public function isEqual(string|SotiNumber $arg): bool
    {
        $normalizedArg = $arg instanceof SotiNumber ? $arg->value : $this->normalizeFloat($arg);
        return bccomp($this->value, $normalizedArg) === 0;
    }

    /**
     * Checks if this number is smaller than another number.
     *
     * @param string|SotiNumber $arg The number to compare against.
     * @return bool True if this number is smaller, false otherwise.
     */
    public function isSmaller(string|SotiNumber $arg): bool
    {
        $normalizedArg = $arg instanceof SotiNumber ? $arg->value : $this->normalizeFloat($arg);
        return bccomp($this->value, $normalizedArg) === -1;
    }

    /**
     * Checks if this number is smaller than or equal to another number.
     *
     * @param string|SotiNumber $arg The number to compare against.
     * @return bool True if this number is smaller or equal, false otherwise.
     */
    public function isSmallerOrEqual(string|SotiNumber $arg): bool
    {
        $normalizedArg = $arg instanceof SotiNumber ? $arg->value : $this->normalizeFloat($arg);
        return bccomp($this->value, $normalizedArg) !== 1; // Not greater
    }

    /**
     * Checks if this number is greater than another number.
     *
     * @param string|SotiNumber $arg The number to compare against.
     * @return bool True if this number is greater, false otherwise.
     */
    public function isGreater(string|SotiNumber $arg): bool
    {
        $normalizedArg = $arg instanceof SotiNumber ? $arg->value : $this->normalizeFloat($arg);
        return bccomp($this->value, $normalizedArg) === 1;
    }

    /**
     * Checks if this number is greater than or equal to another number.
     *
     * @param string|SotiNumber $arg The number to compare against.
     * @return bool True if this number is greater or equal, false otherwise.
     */
    public function isGreaterOrEqual(string|SotiNumber $arg): bool
    {
        $normalizedArg = $arg instanceof SotiNumber ? $arg->value : $this->normalizeFloat($arg);
        return bccomp($this->value, $normalizedArg) !== -1; // Not smaller
    }

    /**
     * Checks if this number is negative.
     *
     * @return bool True if negative, false otherwise.
     */
    public function isNegative(): bool
    {
        return bccomp($this->value, '0') === -1;
    }

    /**
     * Checks if this number is positive.
     *
     * @return bool True if positive, false otherwise.
     */
    public function isPositive(): bool
    {
        return bccomp($this->value, '0') === 1;
    }

    /**
     * Increments the number by 1.
     *
     * @return self A new SotiNumber instance representing the incremented value.
     */
    public function inc(): self
    {
        return $this->add('1');
    }

    /**
     * Decrements the number by 1.
     *
     * @return self A new SotiNumber instance representing the decremented value.
     */
    public function dec(): self
    {
        return $this->sub('1');
    }

    /**
     * Formats the number as a string with thousands separators and specified decimal places.
     * Rounds the number before formatting.
     *
     * @param int $decimals The number of decimal places (non-negative).
     * @return string The formatted number string.
     * @throws ValueError If decimals is negative.
     */
    public function format(int $decimals = 0): string
    {
        if ($decimals < 0) {
            throw new ValueError("Number of decimals cannot be negative.");
        }

        $roundedValue = $this->round($decimals)->value;
        $sign = '';
        if (str_starts_with($roundedValue, '-')) {
            $sign = '-';
            $roundedValue = substr($roundedValue, 1);
        }

        $parts = explode('.', $roundedValue, 2);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        // Add thousands separators
        $formattedInteger = preg_replace('/\\B(?=(\\d{3})+(?!\\d))/', ',', $integerPart);

        // Format decimal part
        $formattedDecimal = str_pad($decimalPart, $decimals, '0', STR_PAD_RIGHT);
        // Ensure the decimal part is exactly $decimals length if it was longer initially
        if (strlen($formattedDecimal) > $decimals) {
             $formattedDecimal = substr($formattedDecimal, 0, $decimals);
        }


        $result = $sign . $formattedInteger;
        if ($decimals > 0) {
            $result .= '.' . $formattedDecimal;
        }

        return $result;
    }


    /**
     * Gets the numeric value scaled to its human-readable unit (K, M, G, etc.).
     *
     * @return self A new SotiNumber instance representing the scaled value.
     */
    public function getHumanValue(): self
    {
        $index = $this->getHumanUnitIndex();
        if ($index === 0) { // No unit needed if less than 1000
            return $this->duplicate();
        }
        $base = (new self('10'))->pow((string)$index);
        return $this->div($base);
    }

    /**
     * Gets the human-readable unit (K, M, G, etc.) for the number's magnitude.
     * Returns an empty string if the number is less than 1000.
     *
     * @return string The unit ('K', 'M', 'G', 'T', 'P', 'E') or empty string.
     */
    public function getHumanUnit(): string
    {
        $index = $this->getHumanUnitIndex();
        return self::HUMAN_UNITS[$index] ?? '';
    }

    /**
     * Determines the appropriate index for human-readable units based on magnitude.
     * Returns 0 if no unit prefix is needed (value < 1000).
     *
     * @return int The exponent index (3 for K, 6 for M, etc.) or 0.
     */
    private function getHumanUnitIndex(): int
    {
        // Use the integer part of the absolute value to determine magnitude
        $integerPart = $this->abs()->truncate(0)->value; // Get integer part as string
        $integerLength = strlen($integerPart);

        // Find the largest unit index where the length is greater than the index
        $applicableIndex = 0;
        foreach (array_keys(self::HUMAN_UNITS) as $index) {
            if ($integerLength > $index) {
                $applicableIndex = $index;
            } else {
                break; // Stop once length is no longer greater
            }
        }
        return $applicableIndex;
    }


    /**
     * Returns the string representation of the number, removing trailing decimal zeros.
     *
     * @return string The number as a string.
     */
    public function toString(): string
    {
        // Ensure the value is normalized (e.g., handle cases like "-0")
        if (bccomp($this->value, '0') === 0) {
            return '0';
        }

        // Remove trailing zeros from decimal part, and the decimal point if nothing remains
        if (str_contains($this->value, '.')) {
            return rtrim(rtrim($this->value, '0'), '.');
        }

        return $this->value;
    }


    /**
     * Magic method for string conversion.
     *
     * @return string The number as a string.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    // --- Magic methods for operator overloading (require PECL operator extension) ---

    /** @param string|SotiNumber $delta */
    public function __add($delta): self { return $this->add($delta); }
    /** @param string|SotiNumber $delta */
    public function __sub($delta): self { return $this->sub($delta); }
    /** @param string|SotiNumber $delta */
    public function __mul($delta): self { return $this->mul($delta); }
    /** @param string|SotiNumber $delta */
    public function __div($delta): self { return $this->div($delta); }
    /** @param string|SotiNumber $delta */
    public function __mod($delta): self { return $this->mod($delta); }
    /** @param string|SotiNumber $delta */
    public function __pow($delta): self { return $this->pow($delta); }

    // Note: Standard PECL Operator comparison uses overloaded operators (==, !=, <, >, <=, >=).
    // The non-standard __is_* magic methods previously here have been removed.
    // Use standard comparison methods (isEqual, isSmaller, etc.) or rely on
    // PECL Operator overloading if the extension is installed.


    public function __pre_inc(): self { return $this->inc(); }
    public function __pre_dec(): self { return $this->dec(); }
    public function __post_inc(): self { $original = $this->duplicate(); $this->__assign_add('1'); return $original; }
    public function __post_dec(): self { $original = $this->duplicate(); $this->__assign_sub('1'); return $original; }

    /** @param string|SotiNumber $delta */
    public function __assign_add($delta): void { $this->value = $this->add($delta)->value; }
    /** @param string|SotiNumber $delta */
    public function __assign_sub($delta): void { $this->value = $this->sub($delta)->value; }
    /** @param string|SotiNumber $delta */
    public function __assign_mul($delta): void { $this->value = $this->mul($delta)->value; }
    /** @param string|SotiNumber $delta */
    public function __assign_div($delta): void { $this->value = $this->div($delta)->value; }
    /** @param string|SotiNumber $delta */
    public function __assign_mod($delta): void { $this->value = $this->mod($delta)->value; }
    /** @param string|SotiNumber $delta */
    public function __assign_pow($delta): void { $this->value = $this->pow($delta)->value; }
}
