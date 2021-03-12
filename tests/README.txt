Create a file named .env.test at the root of the project containing:

```yaml
APP_ENV=dev

APP_DEBUG=0
APP_SECRET=1a2e701e2de1f2b4eb9c320e23ed5cf2

DATABASE_URL=mysql://<dbuser>:<dbpass>@<dbhost>:<dbport>/<dbname>
DATABASE_PREFIX=

MAILER_URL=null://localhost
```

!IMPORTANT <dbname> must be different from the production DB name.
<dbuser> should be able to create a database.

run tests like the following:
./vendor/bin/simple-phpunit --bootstrap tests/bootstrap.php tests/

bootstrap.php create a database (named like choosen in .env.test)
and init the ORM.
