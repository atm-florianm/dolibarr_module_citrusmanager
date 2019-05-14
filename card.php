<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/class/citrus.class.php';

$langs->loadLangs(array('citrusmanager', 'citrus'));

$action = GETPOST('action', 'alpha');

$object = new Citrus($db);

$template_fill = function ($template, $replacements) use ($langs) {
    $filled_template = $template;
    // Templating: replace some underscore-prefixed names with their dictionary value
    foreach ($replacements as $key => $val) {
        $filled_template = str_replace($key, $val, $filled_template);
    }

    // Templating: replace "{ABC}" with $langs->trans("ABC")
    $filled_template = preg_replace_callback(
        '/\{T:(\w+)}/',
        function($matches) use ($langs) {
            return $langs->trans($matches[1]);
        },
        $filled_template
    );
    return $filled_template;
};

// Template with the HTML form to be displayed for the user to create new citruses.
$template_form_create = <<<HTML
<form action="_SELF_" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="token" value="_NEW_SESSION_TOKEN_" />
    <input type="hidden" name="action" value="save" />
    _FICHE_TITRE_
    <div>
        
    </div>
    <table class="border" width="100%">
        <tr>
            <td class="fieldrequired">{T:CitrusRef}</td>
            <td><input id="citrusref" name="ref" class="flat minwidth100" value="toto"/></td>
        </tr>
        <tr>
            <td class="fieldrequired">{T:CitrusLabel}</td>
            <td><input id="citruslabel" name="label" class="flat minwidth100"/></td>
        </tr>
    </table>
    <div align="center">
        <input type="submit" class="button" value="{T:CreateCitrus}" name="create"/>
         
        <input type="submit" class="button" value="{T:Cancel}" name="cancel"/>
    </div>
</form>
HTML;

$form_create = $template_fill(
    $template_form_create,
    array(
        '_SELF_'              => $_SERVER['PHP_SELF'],
        '_NEW_SESSION_TOKEN_' => $_SESSION['newtoken'],
        '_FICHE_TITRE_'       => load_fiche_titre($langs->trans('NewCitrus'))
    )
);

$show_form_create = function () use ($db, $object, $form_create) {
    echo $form_create;
};

$show_form_edit = function () use ($db, $object) {

};

$save_new_citrus = function () use ($db, $object) {

};


llxHeader();

switch ($action) {
    case 'create':
        $show_form_create();
        break;
    case 'save':
        $save_new_citrus();
        break;

}

llxFooter();

