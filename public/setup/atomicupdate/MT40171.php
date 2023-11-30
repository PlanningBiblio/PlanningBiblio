<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'no-reply@planno.fr' WHERE `valeur` = 'no-reply@planningbiblio.fr';";

$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'Ce message a été envoyé par Planno.\nMerci de ne pas y répondre.' WHERE `valeur` = 'Ce message a été envoyé par Planning Biblio.\nMerci de ne pas y répondre.';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'URL de Planno. A renseigner seulement si la redirection ne fonctionne pas après authentification sur le serveur CAS, si vous utilisez un Reverse Proxy par exemple.' WHERE `nom` = 'CAS-ServiceURL';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Champ Planno à utiliser pour mapper les agents.' WHERE `nom` = 'Hamac-id';";
