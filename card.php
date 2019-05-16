<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/class/citrus.class.php';

$langs->loadLangs(array('citrusmanager', 'citrus'));
$action = GETPOST('action', 'alpha');

/**
 * @param $template string     Template string with some var names in brackets ({VAR} or {T:KEY})
 * @param $replacements array  Replacements for each VAR
 * @return string              The filled template: {VAR} inclusions are replaced with dictionary
 *                             values and {T:KEY} inclusions are replaced with their translation from
 *                             the $langs object.
 */
$template_fill = function ($template, $replacements) use ($langs) {
    $filled_template = $template;
    // Templating: replace some underscore-prefixed names with their dictionary value
    foreach ($replacements as $key => $val) {
        $filled_template = str_replace('{' . $key . '}', $val, $filled_template);
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

/**
 * @param $params array  URL parameters to be appended to the base URL
 * @return string        The URL of the current PHP page with additional parameters
 */
$current_page_with_params = function ($params) {
    $current_page = $_SERVER['PHP_SELF'];
    return $current_page . '?' . http_build_query($params);
};

// Template with the HTML form to be displayed for the user to create new citruses.
$template_new_citrus_form = <<<HTML
<form action="{SELF}?action=save" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="token" value="{NEW_SESSION_TOKEN}" />
    {FICHE_TITRE}
    <div>
        
    </div>
    <table class="border" width="100%">
    <colgroup><col width="20%"/><col width="40%"/><col width="40%"/></colgroup>
        <tr>
            <td class="fieldrequired">{T:CitrusRef}</td>
            <td><input id="citrusref" name="ref" class="flat minwidth100" style="width: 80%" value="" placeholder="{T:CitrusRefShortHint}"/></td>
            <td>{T:CitrusRefHint}</td>
        </tr>
        <tr>
            <td class="fieldrequired">{T:CitrusLabel}</td>
            <td><input id="citruslabel" name="label" class="flat minwidth100" style="width: 80%" placeholder="{T:CitrusLabelShortHint}"/></td>
            <td>{T:CitrusLabelHint}</td>
        </tr>
    </table>
    <div align="center">
        <input type="submit" class="button" accesskey="s" value="{T:CreateCitrus}" name="create"/>
        <input type="submit" class="button" accesskey="c" value="{T:Cancel}" name="cancel"/>
    </div>
</form>
HTML;

$new_citrus_form = $template_fill(
    $template_new_citrus_form,
    array(
        'SELF'              => $_SERVER['PHP_SELF'],
        'NEW_SESSION_TOKEN' => $_SESSION['newtoken'],
        'FICHE_TITRE'       => load_fiche_titre($langs->trans('NewCitrus'))
    )
);

$template_show_citrus = <<<HTML
{FORM_START?}
<table class="border" width="100%">
    <tr>
        <td class="titlefield fieldrequired">{T:CitrusRef}</td>
        <td>{CITRUS_REF}</td>
    </tr>
    <tr>
        <td class="fieldrequired">{T:CitrusLabel}</td>
        <td>{CITRUS_LABEL}</td>
    </tr>
</table>
{FORM_BUTTONS?}
{FORM_END?}
HTML;

$template_edit_citrus = <<<HTML

HTML;



// Function that displays the Citrus creation form.
$show_form_create = function () use ($new_citrus_form) {
    echo $new_citrus_form;
};

// data access object
$object = new Citrus($db);

/**
 * @param $editable boolean  Whether to show the citrus as a read-only display or as an edit form
 */
$show_citrus = function ($editable) use (
    $db,
    $object,
    $langs,
    $template_fill,
    $template_show_citrus,
    $template_edit_citrus
) {
    $id = GETPOST('id', 'int');
    if ($id <= 0) {
        // invalid ID passed by POST or GET
        // TODO: replace this dirty redirect with anything else (error message or default value?)
        echo '<script>window.setTimeout(() => {window.location = "list.php";}, 1000);</script>';
        return;
    }
    $id = $object->fetch($id);
    echo "<br>";
    if ($id < 0) {
        // no citrus with this ID in the database
        // TODO: replace this dirty redirect with anything else (error message or default value?)
        echo '<script>window.setTimeout(() => {window.location = "list.php";}, 1000);</script>';
        return;
    }
    $current_page = $_SERVER['PHP_SELF'];
    dol_fiche_head(
        array( // describes the available tab links
            array(
                $current_page.'?'.http_build_query(array('id' => $id)), // url
                $langs->trans('Card'),                             // title
                'card_tab'                                              // key (ID)
            ),
            array(
                $current_page.'?'.http_build_query(array('id' => $id, 'action' => 'edit')),
                $langs->trans('Modify'),
                'card_edit_tab'
            )
        ),
        $editable ? 'card_edit_tab' : 'card_tab',
        $langs->trans('CitrusCard'),
        -1,
        'citrus@citrusmanager'
    );
    if ($editable) {
        $template_values = array(
            'CITRUS_REF' => '<input name="ref" value="'. $object->ref .'">',
            'CITRUS_LABEL' => '<textarea name="label" style="width: 85%; height: 5em;">' . $object->label . '</textarea>' . "\n",
            'FORM_START?' => (
                '<form action="' . $current_page . '?action=save"' . ' method="POST">' . "\n"
                .'<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />' . "\n"
                .'<input type="hidden" name="id" value="'.$id.'" />' . "\n"
            ),
            'FORM_BUTTONS?' => '<input type="submit" class="button" accesskey="s" value="{T:Save}" name="save"/>',
            'FORM_END?' => '</form>'
        );
    } else {
        $template_values = array(
            'CITRUS_REF' => $object->ref,
            'CITRUS_LABEL' => $object->label,
            'FORM_START?' => '',
            'FORM_BUTTONS?' => '',
            'FORM_END?' => ''
        );
    }

    echo $template_fill(
        $template_show_citrus,
        $template_values
    );
    // dol_banner_tab();

    dol_fiche_end();

};

/**
 * @param $id  NULL if new record, else rowid of record to update
 * @return int ID of the created or updated record if successful
 *             -1 if SQL insert or update failed,
 *             -2 if SQL insert was executed but last_insert_id <= 0
 */
$save_citrus = function ($id = null) use ($db, $object) {
    $object->ref = GETPOST('ref', 'alpha');
    $object->label = GETPOST('label', 'alpha');
    if ($id) {
        $object->id = $id;
        return $object->update();
    } else {
        return $object->create();
    }
};

if (GETPOST('cancel', 'alpha')) {
    // assume that the 'cancel' parameter is only there if the user was previously editing a card
    // or creating a new one
    if (GETPOST('id', 'int')) {
        // back to card edit form
        $show_citrus(true);
    } else {
        // back to card creation form
        header('location: list.php');
    }
} else {
    // Display the top part of the Dolibarr standard interface (top and left menus)
    llxHeader();
    switch ($action) {
        case 'create':
            $show_form_create();
            break;
        case 'save':
            $id = GETPOST('id', 'int');
            $result = $save_citrus($id);
            if ($result <= 0) {
                assert(false);
                exit;
            }
            $show_citrus(false);
            break;
        case 'edit':
            $show_citrus(true);
            break;
        default:
            $show_citrus(false);
    }
}
// Display the bottom part of the Dolibarr standard interface (mostly closing tags, except in some specific cases)
llxFooter();

