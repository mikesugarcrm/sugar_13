# Old Tests

These tests are the old "unit" tests.  All of these tests should be reviewed when a corresponding code change is made to see where this test truly belongs (unit, integration, or rest api).  It should then be moved or removed from here and re-implemented appropriately.

## Install PHPUnit

To install PHPUnit, we will need to run the following in the sugarcrm root directory:

```bash
composer install
```

This installs phpunit to the sugarcrm/vendor directory and the phpunit executable in sugarcrm/vendor/bin/phpunit

## Run PHPUnit

1. Install Sugar
2. cd to /path/to/sugar/tests/{old}
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
