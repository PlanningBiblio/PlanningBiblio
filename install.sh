#!/bin/bash

version='22.04.00'

# Check PHP version
phpVersion=$(php -r "echo version_compare(phpVersion(), '8.1');");
if [[ $phpVersion -lt 0 ]]; then
    echo "You need PHP version 8.1 or greater to install Planno";
    exit
fi

# Check if .env.local exists.
if [[ -f .env.local ]]; then
    echo "The file .env.local exists.";
    echo "If you want to start a new installation, remove the file .env.local then run ./install.sh";
    echo "If you want to update your current installation, open Planno in your web browser.";
    exit
fi

# Prompt user for information
echo "You are about to install Planno";
echo "You will be asking for information to create the database.";
echo "A database and a user will be created with given information. WARNING: if the database and the user already exist, they will be overwritten";
echo "We strongly recommend that you back up your databases before starting this installation";
echo "Do you want to proceed ? [yes/no]"
read proceed 

proceed=$(echo $proceed | tr '[:upper:]' '[:lower:]');

if [[ $proceed != 'yes'  && $proceed != 'y' ]]; then
    exit;
fi

# Generate default passwords
defaultDBPass=$(head /dev/urandom|tr -dc "a-zA-Z0-9"|fold -w 10|head -n 1)
defaultAdminPass=$(head /dev/urandom|tr -dc "a-zA-Z0-9"|fold -w 10|head -n 1)

echo "DB Host [localhost] :"
read dBHost

echo "DB Port [3306] :"
read dBPort

echo "DB Admin User [root] :"
read dBRoot

echo "DB Admin Pass :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    dBRootPass+="$char"
done

echo ""
echo "DB User [planno] (will be overwritten if exists) :"
read dBUser

echo "DB Pass [$defaultDBPass] (will be overwritten if exists) :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    dBPass+="$char"
done

echo ""
echo "DB Name [planno] (will be overwritten if exists) :"
read dBName

echo "Planno admin's lastname [admin] :"
read adminLastname

echo "Planno admin's firstname [admin] :"
read adminFirstname

echo "Planno admin's e-mail address [admin@example.com] :"
read adminEmail

echo "Planno admin's password [$defaultAdminPass] :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    adminPass+="$char"
done

echo ""
echo "Update composer dependencies [no] :"
read updateComposer

# Set defaults
if [[ $dBHost = '' ]]; then
    dBHost='localhost'
fi

if [[ $dBPort = '' ]]; then
    dBPort='3306'
fi

if [[ $dBRoot = '' ]]; then
    dBRoot='root'
fi

if [[ $dBUser = '' ]]; then
    dBUser='planno'
fi

if [[ $dBPass = '' ]]; then
    dBPass=$defaultDBPass
fi

if [[ $dBName = '' ]]; then
    dBName='planno'
fi

if [[ $adminLastname = '' ]]; then
    adminLastname='admin'
fi

if [[ $adminFirstname = '' ]]; then
    adminFirstname='admin'
fi

if [[ $adminEmail = '' ]]; then
    adminEmail='admin@example.com'
fi

if [[ $adminPass = '' ]]; then
    adminPass=$defaultAdminPass
fi

updateComposer=$(echo $updateComposer | tr '[:upper:]' '[:lower:]');
if [[ $updateComposer = '' ]]; then
    updateComposer='no'
fi


if [[ $dBHost = 'localhost' ]] || [[ $dBHost = '127.0.0.1' ]]; then
    dBUserhost='localhost'
else
    dBUserhost='%'
fi

# Set variables
data=data/planno_25.05.xx.sql.gz
secret=$(head /dev/urandom|tr -dc "a-f0-9"|fold -w 32|head -n 1)

# Create the database
mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "DROP USER IF EXISTS '$dBUser'@'$dBUserhost';"
mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "DROP DATABASE IF EXISTS $dBName;"

mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "CREATE DATABASE IF NOT EXISTS $dBName CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"

mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "CREATE USER '$dBUser'@'$dBUserhost' IDENTIFIED BY '$dBPass';"
mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "GRANT ALL PRIVILEGES ON $dBName.* TO '$dBUser'@'$dBUserhost' IDENTIFIED BY '$dBPass'"

mysql --defaults-file=/dev/null -h $dBHost -u $dBRoot --password=$dBRootPass -e "FLUSH PRIVILEGES"
zcat $data | mysql -h $dBHost -u $dBRoot --password=$dBRootPass $dBName
mysql --defaults-file=/dev/null -h $dBHost -u $dBUser --password=$dBPass -e "UPDATE $dBName.\`personnel\` SET \`nom\`='$adminLastname', \`prenom\`='$adminFirstname', \`mail\`='$adminEmail', \`password\`=MD5('$adminPass') WHERE \`id\` = 1;"

if [[ $? -ne 0 ]]; then
    exit;
fi

# Create the .env.local file
cp .env .env.local
sed -i "s/APP_SECRET=.*/APP_DEBUG=0\nAPP_SECRET=${secret}/" .env.local

sed -i "s/DATABASE_URL=.*/DATABASE_URL=mysql:\/\/$dBUser:$dBPass@$dBHost:$dBPort\/$dBName/" .env.local
sed -i "s/APP_ENV=dev/APP_ENV=prod/g" .env.local

# Set the default theme
mysql -h $dBHost -u $dBUser --password=$dBPass -e "UPDATE $dBName.\`config\` SET \`valeur\` = 'default' WHERE \`nom\` = 'Affichage-theme';"

# Download composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Update dependencies ?
if [[ $updateComposer = 'yes' && -f composer.lock ]]; then
    echo "Removing composer.lock"
    rm composer.lock
    if [[ -d vendor ]];then
        echo "Removing vendor folder"
        rm -r vendor
    fi
fi

# Run composer install
php composer.phar install
if [[ $? -eq 2 ]]; then
    if [[ -d vendor ]];then
        echo "Removing vendor folder"
        rm -r vendor
    fi
    if [[ -e composer.lock ]];then
        echo "Removing composer.lock"
        rm composer.lock
    fi
    php composer.phar update
fi

if [[ $? > 0 ]]; then
    exit;
fi

# Remove composer.phar
php -r "unlink('composer.phar');"

echo ""
echo -e "One more step, run : \e[1m\033[32msudo chmod -R 777 var\e[0m";
echo "Then, the installation will be completed and you will be able to use Planno in your web browser with these cretentials : admin / $adminPass";
echo ""
