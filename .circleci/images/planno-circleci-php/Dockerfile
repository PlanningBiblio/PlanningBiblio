ARG PHP_VERSION=8.4
FROM cimg/php:${PHP_VERSION}-browsers

RUN sudo apt update \
    && sudo -E install-php-extensions zip mysqli pdo_mysql \
    && sudo apt install -y bash git ssh tar gzip ca-certificates mariadb-client libu2f-udev \
    && sudo pecl install xdebug \
    && sudo sh -c 'echo "deb [arch=amd64] https://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list' \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - \
    && sudo apt update \
    && sudo apt install -y google-chrome-stable \
    && sudo apt remove -y google-chrome-stable \
    && wget -O /tmp/googlechrome.zip https://github.com/PlanningBiblio/googlechrome/archive/refs/heads/v114.zip \
    && unzip -o /tmp/googlechrome.zip \
    && sudo dpkg -i googlechrome-114/google-chrome-stable_current_amd64.deb \
    && rm -rf googlechrome-114 \
    && wget -O /tmp/chromedriver.zip http://chromedriver.storage.googleapis.com/`curl -sS chromedriver.storage.googleapis.com/LATEST_RELEASE`/chromedriver_linux64.zip \
    && sudo unzip /tmp/chromedriver.zip chromedriver -d /usr/local/bin/
