FROM cimg/php:8.2-browsers

RUN sudo apt update
RUN sudo -E install-php-extensions zip mysqli pdo_mysql
RUN sudo apt install -y bash git ssh tar gzip ca-certificates mariadb-client libu2f-udev
RUN sudo pecl install xdebug

# For google chrome
RUN sudo sh -c 'echo "deb [arch=amd64] https://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list'
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add -

RUN sudo apt update
RUN sudo apt install -y google-chrome-stable

# MT41778: Install and remove to get dependencies
RUN sudo apt remove -y google-chrome-stable

# MT41778 : Download Google Chrome from an archive to be sure to have version v114
RUN wget -O /tmp/googlechrome.zip https://github.com/PlanningBiblio/googlechrome/archive/refs/heads/v114.zip
RUN unzip -o /tmp/googlechrome.zip
RUN sudo dpkg -i googlechrome-114/google-chrome-stable_current_amd64.deb
RUN rm -rf googlechrome-114

# Chrome driver
RUN wget -O /tmp/chromedriver.zip http://chromedriver.storage.googleapis.com/`curl -sS chromedriver.storage.googleapis.com/LATEST_RELEASE`/chromedriver_linux64.zip
RUN sudo unzip /tmp/chromedriver.zip chromedriver -d /usr/local/bin/
