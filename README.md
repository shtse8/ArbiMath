# ArbiNumber

**ArbiNumber** is a powerful PHP library for arbitrary precision mathematics. It provides a simple, optimal, and type-safe way to perform high-precision calculations, leveraging the BCMath extension and optionally the PECL Operator extension for enhanced performance.

## Features

- **Arbitrary Precision Arithmetic**: Perform calculations with any desired level of precision.
- **Support for Scientific Notation**: Seamlessly handle numbers in scientific format.
- **Integration with PHP's Native Functions**: Easily use ArbiNumber with PHP’s built-in mathematical functions.
- **Optional Operator Overloading**: For intuitive and natural syntax using the PECL Operator extension.
- **Comprehensive Mathematical Operations**: Includes addition, subtraction, multiplication, division, power, modulus, and more.
- **Human-Readable Formatting Options**: Format numbers with thousands separators and custom decimal places.

## Requirements

- PHP 8.0 or higher
- BCMath extension enabled

### Optional (for operator overloading)
- [PECL Operator extension](https://github.com/php/pecl-php-operator)

## Installation

### Via Composer

```bash
composer require shtse8/arbimath
```

### Installing BCMath

On Debian/Ubuntu systems:

```bash
# Example for PHP 8.0 on Debian/Ubuntu:
sudo apt install php8.0-bcmath
```

For other systems, please refer to the [PHP documentation](https://www.php.net/manual/en/book.bc.php) for installation instructions.

### Installing PECL Operator (Optional)

If you want to use operator overloading, you'll need to install the PECL Operator extension:

```bash
# Note: Installation might vary depending on your OS and PHP setup.
# PECL Operator might not be actively maintained or compatible with recent PHP versions.
# Verify compatibility before installing.
# Example build steps (may need adjustments):
git clone https://github.com/php/pecl-php-operator
cd pecl-php-operator
phpize
./configure
make && sudo make install
```

Then, enable the extension (adjust paths for your PHP version, e.g., 8.0):

```bash
# Example for PHP 8.0 FPM/CLI on Debian/Ubuntu:
echo "extension=operator.so" | sudo tee /etc/php/8.0/mods-available/operator.ini
sudo ln -s /etc/php/8.0/mods-available/operator.ini /etc/php/8.0/cli/conf.d/20-operator.ini
sudo ln -s /etc/php/8.0/mods-available/operator.ini /etc/php/8.0/fpm/conf.d/20-operator.ini
# Reload PHP-FPM if applicable
sudo service php8.0-fpm reload
```

Note: Adjust the PHP version in the paths if you're using a different version.

## Usage

### Basic Usage

```php
use Shtse8\ArbiMath PHP\ArbiNumber;

$num1 = new ArbiNumber("1.8573958822565E+17");
$num2 = new ArbiNumber("111");

$result = $num1->add($num2)->pow($num2);
echo $result->toString();
```

### With Operator Overloading (requires PECL Operator)

```php
use Shtse8\ArbiMath PHP\ArbiNumber;

$num1 = new ArbiNumber("1.8573958822565E+17");
$num2 = new ArbiNumber("111");

$result = $num1 + $num2;
$result **= $num2;
echo $result;
```

## Available Methods

Methods accept `string` or `ArbiNumber` instances as arguments where applicable.

- `add(string|ArbiNumber $number)`: Addition
- `sub(string|ArbiNumber $number)`: Subtraction
- `mul(string|ArbiNumber $number)`: Multiplication
- `div(string|ArbiNumber $number)`: Division
- `mod(string|ArbiNumber $number)`: Modulus
- `pow(string|ArbiNumber $number)`: Power
- `abs()`: Absolute value
- `floor()`: Round down to the nearest integer
- `ceil()`: Round up to the nearest integer
- `round(int $precision = 0)`: Round to specified precision (half up)
- `truncate(int $precision = 0)`: Truncate to specified precision
- `ln()`: Natural logarithm (base e)
- `log(string|ArbiNumber $base)`: Logarithm to a specified base
- `isEqual(string|ArbiNumber $number)`: Equality comparison (`==`)
- `isSmaller(string|ArbiNumber $number)`: Less than comparison (`<`)
- `isSmallerOrEqual(string|ArbiNumber $number)`: Less than or equal comparison (`<=`)
- `isGreater(string|ArbiNumber $number)`: Greater than comparison (`>`)
- `isGreaterOrEqual(string|ArbiNumber $number)`: Greater than or equal comparison (`>=`)
- `isNegative()`: Check if the number is negative
- `isPositive()`: Check if the number is positive
- `inc()`: Increment by 1
- `dec()`: Decrement by 1
- `format(int $decimals = 0)`: Format number with thousands separators and specified decimal places
- `getHumanValue()`: Get the numeric value scaled to its human-readable unit (K, M, G, etc.)
- `getHumanUnit()`: Get the human-readable unit (K, M, G, etc.) or empty string
- `duplicate()`: Create a new instance with the same value
- `toString()` / `__toString()`: Get the string representation

For a complete list of methods and their usage, please refer to the [API documentation](link-to-api-docs).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Thanks to the PHP team for the BCMath extension
- Thanks to the PECL Operator team for enabling operator overloading in PHP

---

This library aims to provide a simple, optimal, and type-safe interface for arbitrary precision math in PHP.
