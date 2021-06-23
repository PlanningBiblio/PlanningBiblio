local Pipeline(phpVersion, dbImage) = {
    kind: 'pipeline',
    type: 'docker',
    name: 'php:' + phpVersion + ' ' + dbImage,
    steps: [
        {
            name: 'test',
            image: 'php:' + phpVersion,
            commands: [
                'apt update',
                'apt install -y libzip-dev',
                'docker-php-ext-install zip',
                'docker-php-ext-install mysqli',
                'docker-php-ext-install pdo_mysql',
                'cp .drone/.env.test.local .env.test.local',
                'cp .drone/.env.local .env.local',
                '.drone/install-composer.sh',
                'php composer.phar install -n --prefer-dist',
                'cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && echo "xdebug.mode=coverage" | tee -a $PHP_INI_DIR/php.ini',
                './vendor/bin/simple-phpunit --coverage-text --coverage-clover coverage.xml --bootstrap tests/bootstrap.php tests/'
            ],
        },
    ],
    services: [
        {
            name: 'db',
            image: dbImage,
            environment: {
                MYSQL_ROOT_PASSWORD: 'rootplb',
                MYSQL_DATABASE: 'planningb',
                MYSQL_USER: 'plb',
                MYSQL_PASSWORD: 'plb',
            },
        },
    ],
};

[
    Pipeline('7.4', 'mariadb:10.5'),
]
