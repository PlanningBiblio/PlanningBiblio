<?php

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-LoginAttribute', 'text', 'CAS','48', 'Attribut CAS à utiliser pour mapper l\'utilisateur si et seulement si l\'UID CAS ne convient pas. Laisser ce champ vide par défaut. Exemple : \"mail\", dans ce cas, l\'adresse mail de l\'utilisateur est fournie par le serveur CAS et elle est renseignée dans le champ \"login\" des fiches agents de Planno.');";
