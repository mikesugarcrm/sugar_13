# Unit Tests

These tests are Sugar's unit tests for php.  These tests must follow best practices for unit test writing by not writing to the filesystem, not making network calls, not crossing testing boundaries, etc.  These tests must also run without an installed instance of sugar, database, etc.

## Install PHPUnit

To install PHPUnit, we will need to run the following in the sugarcrm root directory:

```bash
composer install
```

This installs phpunit to the sugarcrm/vendor directory and the phpunit executable in sugarcrm/vendor/bin/phpunit

## Run PHPUnit

1. Build Sugar as you normally would
2. cd to /path/to/sugar/tests/unit-php
3. Run the following

  ```bash
  ../../vendor/bin/phpunit --color="always"
  ```

The output should display `OK`. If not, you need to figure out what's going on and if the tests are broken because of your modifications.

## Other Tips
1. If you want to test a single component, type its path after the `phpunit` command, e.g.:

  ```bash
  ../../vendor/bin/phpunit include/SugarOAuth2StorageTest.php
  ```

