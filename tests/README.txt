rename tests/config.ini.changeme to tests/config.ini and
fill the variable (dbname should different from the one used
in production.

run tests like the following:
./vendor/bin/simple-phpunit --bootstrap tests/bootstrap.php tests/

bootstrap.php create a database (named like choosen in config.ini)
a init the ORM.
