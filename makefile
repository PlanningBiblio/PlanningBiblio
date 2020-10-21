OWN=chown
NEW=touch
LOCAL=.env.local
VAR=/var/www/var/
WWW=www-data
EXEC=install

all: $(EXEC)

install: initialisation
	$(NEW) $(LOCAL)
	$(OWN) $(WWW):$(WWW) $(LOCAL) 
	$(OWN) -R $(WWW):$(WWW) $(VAR)log $(VAR)cache

initialisation: 
	./install.sh