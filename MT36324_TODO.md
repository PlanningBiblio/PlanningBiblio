# General

* update pl_poste_tab.updated_at when a framework is modified (check diff before-after to decide if it's modified or not)
* CSRF protection on public/planning/poste/ajax.updateCell.php
* atomic update which create an affect copies for all plannings created before this patch

# Models

* It ssems to work correctly for models which are created with an original framework.
* 1./ But when we load a model created from a framework's copy, the framework is not loaded.
* 2./ We want the modifications made on frameworks affect the models, but the modification are made on original frameworks, never on copies which are not editable. 
In this case, we can't make it work

--> Try this : 
* the latest copy take the place of the original framework. The origin will be not modified, the latest copy can be modify. The models are reaffected to the latest copy.
In this case, copies are not generated when we start to complete a planning, but when we save modifications on frameworks.
* When we will load models, we could detect new version of framework and propose to adapt, or not, the model to the new framework : if yes : planning is created from adaptation and the new model is saved.
