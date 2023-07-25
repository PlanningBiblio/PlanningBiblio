#! /bin/bash

#Documentation
usage() {
    cat <<EOF

Do a minor Planno release

    --from version
        Version number to upgrade from (ex: 22.11.00.007)
    --to version
        Version number to upgrade to (ex: 22.11.00.008)
    -h | --help
        Show help
EOF
}

##
# Get input args
##

# No input or help
if [[ -z "$1" || "$1" = "-h" || "$1" = "--help" ]]
then
    usage
    exit
fi

while [ $1 ]; do
    case $1 in
        --from )
            shift
            from=$1
            ;;
        --to )
            shift
            to=$1
            ;;
        * )
            echo "Unknown arg $1"
            usage
            exit
    esac
    shift
done

echo "Releasing from version $from to $to";

vi ChangeLog.txt

sed -i "s/$from/$to/g" 'init/init.php'
sed -i "s/$from/$to/g" 'public/setup/db_data.php'

git diff

sed -i "/# MARKER/i\$v=\"$to\";\n\nif (version_compare(\$config['Version'], \$v) === -1) {\n\n\    \$sql[] = \"UPDATE \`{\$dbprefix}config\` SET \`valeur\`='\$v' WHERE \`nom\`='Version';\";\n}" 'public/setup/maj.php'

vi public/setup/maj.php +/MARKER


# Check if public/setup/atomicupdate/ is empty
if [ "$(ls 'public/setup/atomicupdate/')" ]; then
    echo "Warning: public/setup/atomicupdate is not empty. Did you forgot to remove some files?";
fi

git add ChangeLog.txt init/init.php public/setup/db_data.php public/setup/maj.php

git status
