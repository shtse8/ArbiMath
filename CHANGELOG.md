# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project structure.
- `ArbiNumber` class for arbitrary precision math using BCMath.
- Basic arithmetic operations (`add`, `sub`, `mul`, `div`, `pow`, `mod`).
- Comparison methods (`isEqual`, `isSmaller`, etc.).
- Utility methods (`abs`, `round`, `floor`, `ceil`, `truncate`, `format`, etc.).
- `LICENSE` file (MIT).
- `CHANGELOG.md` file.
- Basic GitHub Actions CI workflow for running tests.

### Changed
- Renamed project from "SotiMath" to "ArbiMath PHP".
- Renamed class `SotiNumber` to `ArbiNumber`.
- Renamed namespace `Shtse8\SotiMath` to `Shtse8\ArbiMath`.
- Refactored `ln()` and `log()` methods for improved performance/accuracy using normalization and `LN10` constant.
- Updated `README.md` with new name, features, and contribution info.
- Updated `composer.json` with new project name and namespace.
- Updated Memory Bank files and `.clinerules` with new project name.

### Removed
- Non-standard PECL Operator magic methods (`__is_equal`, `__is_smaller`, etc.). Rely on standard comparison operators if PECL Operator extension is installed, or use explicit methods (`isEqual`, `isSmaller`).

### Fixed
- N/A

## [0.1.0] - 2025-04-06

### Added
- PHPUnit testing framework and initial test cases.
- PSR-4 autoloading configuration via Composer.
- Memory Bank system for project knowledge.

### Changed
- Major refactoring for PHP 8.0 compatibility (strict types, type hints).
- Renamed `clone()` method to `duplicate()`.
- Improved `ln()` and `log()` methods using normalization and `LN10`.
- Updated README with installation, usage, and method list (prior to major revamp).

### Removed
- Non-standard `__is_*` comparison magic methods.

### Fixed
- Various minor bugs and inconsistencies from older versions (pre-2025).