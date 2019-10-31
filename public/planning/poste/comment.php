<?php
    // Notes : Affichage
    $p=new planning();
    $p->date=$date;
    $p->site=$site;
    $p->getNotes();
    $notes=$p->notes;
    $notesTextarea=$p->notesTextarea;
    $notesValidation=$p->validation;
    $notesDisplay=trim($notes)?null:"style='display:none;'";
    $notesSuppression=($notesValidation and !trim($notes)) ? "Suppression du commentaire : ":null;

    echo <<<EOD
  <div id='pl-notes-div1' $notesDisplay >
  $notes
  </div>
EOD;

    // Notes : Modifications
    if ($autorisationNotes) {
        echo <<<EOD
    <div id='pl-notes-div2' class='noprint'>
    <input type='button' class='ui-button noprint' id='pl-notes-button' value='Ajouter un commentaire' />
    </div>

    <div id="pl-notes-form" title="Commentaire" class='noprint' style='display:none;'>
      <p class="validateTips" id='pl-notes-tips'>Vous pouvez écrire ici un commentaire qui sera affich&eacute; en bas du planning.</p>
      <form>
      <textarea id='pl-notes-text'>$notesTextarea</textarea>
      </form>
    </div>
EOD;
    }

    echo <<<EOD
  <div id='pl-notes-div1-validation'>
  $notesSuppression$notesValidation
  </div>
EOD;


