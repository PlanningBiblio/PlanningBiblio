#!/bin/bash

# Check if .env.local exists.
if [[ -f .env.local ]]; then
    echo "The file .env.local exists.";
    echo "If you want to start a new installation, remove the file .env.local then run ./install.sh";
    echo "If you want to update your current installation, open Planning Biblio in your web browser.";
    exit
fi

# Prompt user for information
echo "You are about to install Planning Biblio";
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
planningbdbpass_default=$(head /dev/urandom|tr -dc "a-zA-Z0-9"|fold -w 10|head -n 1)
planningbadminpass_default=$(head /dev/urandom|tr -dc "a-zA-Z0-9"|fold -w 10|head -n 1)

echo "DB Host [localhost] :"
read planningdbhost

echo "DB Port [3306] :"
read planningdbport

echo "DB Admin User [root] :"
read dbroot

echo "DB Admin Pass :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    dbpass+="$char"
done

echo ""
echo "DB User [planningbiblio] (will be overwritten if exists) :"
read planningbdbuser

echo "DB Pass [$planningbdbpass_default] (will be overwritten if exists) :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    planningbdbpass+="$char"
done

echo ""
echo "DB Name [planningbiblio] (will be overwritten if exists) :"
read planningbdbname

# DB Prefix : does not work by loading the sql file
# echo "DB Prefix [] (optional) :"
# read planningbdbprefix

echo "Planning Biblio admin's lastname [admin] :"
read planningbadminlastname

echo "Planning Biblio admin's firstname [admin] :"
read planningbadminfirstname

echo "Planning Biblio admin's e-mail address [admin@example.com] :"
read planningbadminemail

echo "Planning Biblio admin's password [$planningbadminpass_default] :"
prompt=''
while IFS= read -p "$prompt" -r -s -n 1 char
do
    if [[ $char == $'\0' ]]
    then
        break
    fi
    prompt='*'
    planningbadminpass+="$char"
done

echo ""
echo "Update composer dependencies [no] :"
read updatecomposer

# Set defaults
if [[ $planningdbhost = '' ]]; then
    planningdbhost='localhost'
fi

if [[ $planningdbport = '' ]]; then
    planningdbport='3306'
fi

if [[ $dbroot = '' ]]; then
    dbroot='root'
fi

if [[ $planningbdbuser = '' ]]; then
    planningbdbuser='planningbiblio'
fi

if [[ $planningbdbpass = '' ]]; then
    planningbdbpass=$planningbdbpass_default
fi

if [[ $planningbdbname = '' ]]; then
    planningbdbname='planningbiblio'
fi

if [[ $planningbadminlastname = '' ]]; then
    planningbadminlastname='admin'
fi

if [[ $planningbadminfirstname = '' ]]; then
    planningbadminfirstname='admin'
fi

if [[ $planningbadminemail = '' ]]; then
    planningbadminemail='admin@example.com'
fi

if [[ $planningbadminpass = '' ]]; then
    planningbadminpass=$planningbadminpass_default
fi

updatecomposer=$(echo $updatecomposer | tr '[:upper:]' '[:lower:]');
if [[ $updatecomposer = '' ]]; then
    updatecomposer='no'
fi



# Set variables
planningbdatas=data/planningb_1911_utf8.sql.gz
planningbsecret=$(hexdump -n 16 -e '4/4 "%08X" 1 "\n"' /dev/random)

# Download composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Create the .env.local file
cp .env .env.local
sed -i "s/APP_SECRET=.*/APP_SECRET=${planningbsecret}/" .env.local

sed -i "s/DATABASE_URL=.*/DATABASE_URL=mysql:\/\/$planningbdbuser:$planningbdbpass@$planningdbhost:$planningdbport\/$planningbdbname/" .env.local
sed -i "s/DATABASE_PREFIX=.*/DATABASE_PREFIX=$planningbdbprefix/" .env.local

# Create the database
mysql -u $dbroot --password=$dbpass -e "DROP USER IF EXISTS '$planningbdbuser'@'$planningdbhost';"
mysql -u $dbroot --password=$dbpass -e "DROP DATABASE IF EXISTS $planningbdbname;"

mysql -u $dbroot --password=$dbpass -e "CREATE DATABASE IF NOT EXISTS $planningbdbname CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
mysql -u $dbroot --password=$dbpass -e "CREATE USER '$planningbdbuser'@'$planningdbhost' IDENTIFIED BY '$planningbdbpass';"
mysql -u $dbroot --password=$dbpass -e "GRANT ALL PRIVILEGES ON $planningbdbname.* TO $planningbdbuser@$planningdbhost IDENTIFIED BY '$planningbdbpass'"

mysql -u $dbroot --password=$dbpass -e "FLUSH PRIVILEGES"
zcat $planningbdatas | mysql -u $dbroot --password=$dbpass $planningbdbname
mysql -u $planningbdbuser --password=$planningbdbpass -e "UPDATE $planningbdbname.\`personnel\` SET \`nom\`='$planningbadminlastname', \`prenom\`='$planningbadminfirstname', \`mail\`='$planningbadminemail', \`password\`=MD5('$planningbadminpass') WHERE \`id\` = 1;"

# Set the light_blue theme
mysql -u $planningbdbuser --password=$planningbdbpass -e "UPDATE $planningbdbname.\`config\` SET \`valeur\` = 'light_blue' WHERE \`nom\` = 'Affichage-theme';"

if [[ ! -d public/themes/light_blue ]]; then
    git clone https://github.com/planningbiblio/theme_light_blue public/themes/light_blue
fi

# Update dependencies ?
if [[ $updatecomposer = 'yes' && -f composer.lock ]]; then
    echo "Removing composer.lock"
    rm composer.lock
fi

# Run composer install
php composer.phar install

# Remove composer.phar
php -r "unlink('composer.phar');"

# Run database update
php -f public/index.php

echo ""
echo "Installation is completed. You can open Planning Biblio in your web browser with these cretentials : admin / $planningbadminpass";
echo ""