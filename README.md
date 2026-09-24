# PHPStan memory use on constant JSON input

A standalone reproducer for the cost of inferring a type from a large constant JSON string. Both variants decode the same 2609 rows. One passes the class constant directly to `json_decode`; the other passes it through a private identity method, leaving the runtime value unchanged.

## Reproduce

Requirements: PHP 8.2 or newer and Composer.

```sh
composer install
php run.php
```

`generate_fixture.php` creates two synthetic PHP files under `generated/`. `run.php` analyses each file in a separate PHPStan process with `--debug -vvv` and prints the file's increase in the PHP allocator peak. No application code is needed.

On macOS with PHP 8.5.10 and PHPStan `2.3.x-dev@9b5c7d6`, the direct constant costs **204 MB and 2.72 s**, compared with **10 MB and 0.05 s** for the opaque argument. Memory and timing depend on the machine; the comparison is the signal. These are PHP allocator peaks from `memory_get_peak_usage(true)`, not RSS (the resident set size that tools like `top` show). RSS also counts the PHP binary, loaded extensions and memory that PHP has not given back to the OS, so it is usually higher.

In `ConstantTypeHelper::getTypeFromValue()`, PHPStan generalizes arrays with more than 256 entries but still recursively makes a constant type for every entry. The example asks whether that work can be bounded earlier without losing useful inferred types. GitHub Actions runs the comparison on every push.
