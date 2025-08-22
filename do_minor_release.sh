#! /bin/bash

#Documentation
usage() {
    cat <<EOF

Do a minor Planno release

    -h | --help
        Show help
EOF
}

##
# Get input args
##

# Help
if [[ "$1" = "-h" || "$1" = "--help" ]]
then
    usage
    exit
fi

while [ $1 ]; do
    case $1 in
        * )
            echo "Unknown arg $1"
            usage
            exit
    esac
    shift
done

# Get current release
from=$(grep 'version=' init/init.php | sed 's/\$version="\([0-9]*\.[0-9]*\.[0-9]*\).*/\1/g')

# Create new release number
major=$(echo $from | sed 's/\([0-9]*\.[0-9]*\)\.\([0-9]*\)/\1/g')

minor=$(echo $from | sed 's/\([0-9]*\.[0-9]*\)\.\([0-9]*\)/\2/g')
minor=$(echo $minor | sed 's/^0*//')
minor="$(($minor + 1))"
minor=$(printf "%02d\n" $minor)

digit2=$(echo $from | sed 's/[0-9]*\.\([0-9]*\)\.[0-9]*/\1/g')

to=$major.$minor


echo -e "\e[0;33mReleasing from version $from to $to\e[0m\n"
echo -e "\e[0;33mDo you want to proceed ? [yes/no]\e[0m\n"
read proceed 

proceed=$(echo $proceed | tr '[:upper:]' '[:lower:]');

if [[ $proceed != 'yes'  && $proceed != 'y' ]]; then
    echo "Nothing changed. Bye"
    exit;
fi

# Update Changelog
date=$(date +%Y-%m-%d)

echo "# Changelog Planno" > Changelog.tmp
echo "" >> Changelog.tmp
echo "## Version $to ($date)" >> Changelog.tmp
echo "" >> Changelog.tmp
echo "### Enhancement" >> Changelog.tmp
echo "" >> Changelog.tmp
echo "### Fixes" >> Changelog.tmp
echo "" >> Changelog.tmp
echo "### Security" >> Changelog.tmp
echo "" >> Changelog.tmp
echo "### Dependencies" >> Changelog.tmp
echo "" >> Changelog.tmp
echo "### Plumbing" >> Changelog.tmp

tail -n +2 Changelog.md >> Changelog.tmp
mv Changelog.tmp Changelog.md

vi Changelog.md

sed -i "s/$from/$to/g" 'init/init.php'
sed -i "s/$from/$to/g" 'public/setup/db_data.php'

git diff

sed -i "/# MARKER/i\ \n\$v=\"$to\";\n\nif (version_compare(\$config['Version'], \$v) === -1) {\n\n\    \$sql[] = \"UPDATE \`{\$dbprefix}config\` SET \`valeur\`='\$v' WHERE \`nom\`='Version';\";\n}" 'public/setup/maj.php'

vi public/setup/maj.php +/MARKER


git add Changelog.md init/init.php public/setup/db_data.php public/setup/maj.php

git status

# Check if public/setup/atomicupdate/ is empty
if [ "$(ls 'public/setup/atomicupdate/')" ]; then
    echo -e "\e[0;33mWarning: public/setup/atomicupdate is not empty. Did you forgot to remove some files?\e[0m\n"
fi


echo -e "\e[0;33mApprove changes ? [yes/no]\e[0m\n"
read proceed 

proceed=$(echo $proceed | tr '[:upper:]' '[:lower:]');

# Cancel
if [[ $proceed != 'yes' && $proceed != 'y' ]]; then
    git reset --hard
    echo "Changes cancelled. Bye."
    exit;
fi

# Commit
git commit -m "Release v$to"

echo -e "\e[0;33mCommit \"Release v$to\" done\e[0m\n"

# Create tag
tag=0
if [[ $digit2 == '04' || $digit2 == '10' ]]; then
    git tag -f "v$to"
    echo -e "\e[0;33mTag \"v$to\" done\e[0m\n"
    tag=1
fi

if [[ $tag == 1 ]]; then
    echo -e "\e[0;33mDo you want to push the release commit and the tag to origin ? [yes/no]\e[0m\n"
else
    echo -e "\e[0;33mDo you want to push the release commit to origin ? [yes/no]\e[0m\n"
fi

# Push commit and tag
read proceed 

proceed=$(echo $proceed | tr '[:upper:]' '[:lower:]');

if [[ $proceed == 'yes' || $proceed == 'y' ]]; then
    git push origin

    if [[ $tag == 1 ]]; then
        git push origin "v$to"
    fi
fi

echo -e "\e[0;33mDone\e[0m\n"
