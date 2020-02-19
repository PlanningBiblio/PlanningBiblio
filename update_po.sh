#xgettext --default-domain=messages -p ./locale --from-code=UTF-8 -n --omit-header -L PHP /tmp/cache/*/*.php
xgettext --default-domain=messages -p ./locale --from-code=UTF-8 -n --omit-header /tmp/cache/*/*.php public/conges/js/*.js
#find . -maxdepth 3 -iname '*.php' -o -iname '*.js' | xargs xgettext --from-code=UTF-8 -o locale/messages.po

