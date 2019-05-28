<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/class/citrus.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/class/citrus_categories.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/lib/citrusmanager.lib.php';

$langs->loadLangs(array('citrusmanager', 'citrus'));
$action = GETPOST('action', 'alpha');
$list_view_url = 'list.php';

// Template with the HTML form to be displayed for the user to create new citruses.
$template_new_citrus_form = <<<HTML
<form action="{SAVE_URL}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="token" value="{NEW_SESSION_TOKEN}" />
    {FICHE_TITRE}
    <div>
        
    </div>
    <table class="border" width="100%">
    <colgroup><col width="20%"/><col width="40%"/><col width="40%"/></colgroup>
        <tr>
            <td class="fieldrequired">{T:CitrusRef}</td>
            <td><input id="citrusref"
                   name="ref"
                   required
                   class="flat minwidth100"
                   style="width: 80%"
                   value="{CITRUS_REF}"
                   placeholder="{T:CitrusRefShortHint}"/></td>
            <td>{T:CitrusRefHint}</td>
        </tr>
        <tr>
            <td class="fieldrequired">{T:CitrusLabel}</td>
            <td><input id="citruslabel"
                   name="label"
                   required
                   class="flat minwidth100"
                   style="width: 80%"
                   value="{CITRUS_LABEL}"
                   placeholder="{T:CitrusLabelShortHint}"/></td>
            <td>{T:CitrusLabelHint}</td>
        </tr>
        <tr>
            <td class="">{T:CitrusPrice}</td>
            <td><input id="citrusprice"
                   name="price"
                   class="flat minwidth100"
                   style="width: 80%"
                   placeholder="{T:CitrusPriceShortHint}"
                   value="{CITRUS_PRICE}"/></td>
            <td>{T:CitrusPriceHint}</td>
        </tr>
            <td class="">{T:CitrusCategory}</td>
            <td>
                {CATEGORY}
            </td>
            <td>
                {T:CitrusCategoryHint}
            </td>
        <tr>
        <tr>
            <td class="">{T:ParentProductOfCitrus}</td>
            <td class="">
                <input
                   type="hidden"
                   name="fk_product"
                   value="{PARENT_PRODUCT_ID}" />
                {PARENT_PRODUCT_REF}
            </td>
            <td class=""></td>
        </tr>
    </table>
    <div align="center">
        <input type="submit" class="button" accesskey="s" value="{T:CreateCitrus}" name="create"/>
        <input type="submit" class="button" accesskey="c" value="{T:Cancel}" name="cancel"/>
    </div>
</form>
HTML;

$form = new Form($db);
$categoriesDAO = new CitrusCategories($db);

// by default, use empty category (ID 0)
$allCategories = array(0 => '') + $categoriesDAO->fetchAll();

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
    <tr>
        <td class="fieldrequired">{T:CitrusPrice}</td>
        <td>{CITRUS_PRICE}</td>
    </tr>
    <tr>
        <td class="">{T:CitrusCategory}</td>
        <td>{CATEGORY}</td>
    </tr>
</table>
{FORM_BUTTONS?}
{FORM_END?}
{ACTION_BUTTONS?}
HTML;

// Function that displays the Citrus creation form.
function show_form_create () {
    global $template_new_citrus_form;
    global $form;
    global $langs;
    global $allCategories;
    global $db;
    require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
    $product = NULL;
    if (GETPOST('derive_from_product',  'int')) {
        $product_id = GETPOST('product_id', 'int') ?: NULL;
        if ($product_id) {
            $product = new Product($db);
            if ($product->fetch($product_id) == -1) {
                $product = NULL;
            }
        }
    }
    $new_citrus_form = template_fill(
        $template_new_citrus_form,
        array(
            'SAVE_URL'          => current_page_with_params(array('action' => 'save')),
            'NEW_SESSION_TOKEN' => $_SESSION['newtoken'],
            'FICHE_TITRE'       => load_fiche_titre($langs->trans('NewCitrus')),
            'CATEGORY'        => $form->selectarray(
                'category',
                $allCategories,
                0
            ),
            'CITRUS_REF' => $product ? $product->ref : GETPOST('ref'),
            'CITRUS_LABEL' => $product ? $product->label : GETPOST('label'),
            'CITRUS_PRICE' => $product ? $product->price : GETPOST('price'),
            'PARENT_PRODUCT_REF' => $product ? $product->ref : '',
            'PARENT_PRODUCT_ID' => $product ? $product->id : ''
        )
    );
    llxHeader();
    echo $new_citrus_form;
};

// data access object
$citrusDAO = new Citrus($db);

/**
 * @param $is_in_edit_mode boolean  Whether to show the citrus as a read-only display or as an edit form
 */
function show_citrus ($is_in_edit_mode) {
    global $db;
    global $citrusDAO;
    global $langs;
    global $template_show_citrus;
    global $form;
    global $allCategories;
    $id = GETPOST('id', 'int');
    if ($id <= 0) {
        // invalid ID passed by POST or GET
        dol_print_error();
        return;
    }
    $id = $citrusDAO->fetch($id);
    if ($id <= 0) {
        // no citrus with this ID in the database
        setEventMessages('Database error: failed to fetch citrus.', array(), 'errors');
        dol_print_error();
        return;
    }
    $edit_url = current_page_with_params(array('id' => $id, 'action' => 'edit'));
    $delete_url = current_page_with_params(array('id' => $id, 'action' => 'delete'));
    dol_fiche_head(
        array( // describes the available tab links
            array(
                current_page_with_params(array('id' => $id)), // url
                $langs->trans('Card'),                   // title
                'card_tab'                                     // key (ID)
            )
        ),
        'card_tab',
        $langs->trans('CitrusCard'),
        -1,
        'citrus@citrusmanager'
    );
    if ($is_in_edit_mode) {
        $template_values = array(
            'CITRUS_REF' => '<input name="ref" value="'. $citrusDAO->ref .'">',
            'CITRUS_LABEL' => '<textarea name="label" style="width: 85%; height: 5em;">'
                               . $citrusDAO->label . '</textarea>' . "\n",
            'CITRUS_PRICE' => '<input name="price" value="' . $citrusDAO->price . '">',
            'FORM_START?' => (
                '<form action="' . current_page_with_params(array('action' => 'save')) . '" method="POST">' . "\n"
                .'<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />' . "\n"
                .'<input type="hidden" name="id" value="'.$id.'" />' . "\n"
                .'<input type="hidden" name="fk_product" value="'.$citrusDAO->fk_product.'" />'
            ),
            'CATEGORY' => $form->selectarray(
                'category',
                $allCategories,
                $citrusDAO->categoryId
            ),
            'FORM_BUTTONS?' => '<input type="submit" class="button" accesskey="s" value="{T:Save}" name="save"/>',
            'FORM_END?' => '</form>',
            'ACTION_BUTTONS?' => ''
        );
    } else {
        if ($citrusDAO->categoryId) {
            $categoryDisplay = '
                <div class="select2-container-multi-dolibarr">
                    <ul class="select2-choices-dolibarr">
                        <li class="select2-search-choice-dolibarr noborderoncategories" 
                            style="background: #454545; padding: 0.3em;">
                            <img src="/theme/eldy/img/object_category.png" alt="" class="inline-block" />
                            <span class="categtextwhite">
                                ' .
                                dol_htmlentities($allCategories[$citrusDAO->categoryId])
                                . '
                            </span>
                        </li>
                    </ul>
                </div>
            ';
        } else {
            $categoryDisplay = 'N/A';
        }
        $template_values = array(
            'CITRUS_REF' => $citrusDAO->ref,
            'CITRUS_LABEL' => $citrusDAO->label,
            'CITRUS_PRICE' => $citrusDAO->price ?: $langs->trans('Unavailable'),
            'FORM_START?' => '',
            'CATEGORY' => $categoryDisplay,
            'FORM_BUTTONS?' => '',
            'FORM_END?' => '',
            'ACTION_BUTTONS?' => '
                <div class="tabsAction">
                    <a href="'. $edit_url . '" class="butAction">' . $langs->trans('Modify') . '</a>
                    <a href="'. $delete_url . '" class="butActionDelete">' . $langs->trans('Delete') . '</a>
                </div>'
        );
    }
    echo template_fill(
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
function save_citrus ($id = null) {
    global $db;
    global $conf;
    global $citrusDAO;
    $citrusDAO->ref = GETPOST('ref', 'alpha');
    $citrusDAO->label = GETPOST('label', 'alpha');
    $citrusDAO->price = GETPOST('price', 'int');
    $citrusDAO->categoryId = GETPOST('category', 'int');
    $citrusDAO->fk_product = GETPOST('fk_product', 'int');

    // required fields: if not filled, return error code
    if (empty($citrusDAO->ref)) {
        return -3;
    } else if (empty($citrusDAO->label)) {
        return -3;
    }

    if (!$citrusDAO->price) {
        if ($citrusDAO->categoryId) {
            $categoriesDAO = new CitrusCategories($db);
            $default_price = $categoriesDAO->fetchDefaultPrice($citrusDAO->categoryId);
            $citrusDAO->price = $default_price;
        } else {
            $citrus_default_price = $conf->global->CITRUSMANAGER_DEFAULT_PRICE;
            $citrusDAO->price = $citrus_default_price;
        }
    }
    if ($id) {
        $citrusDAO->id = $id;
        return $citrusDAO->update();
    } else {
        return $citrusDAO->create();
    }
};

if (GETPOST('cancel', 'alpha')) {
    // assume that the 'cancel' parameter is only there if the user was previously editing a card
    // or creating a new one
    if (GETPOST('id', 'int')) {
        // back to card edit form
        llxHeader();
        show_citrus(true);
    } else {
        // back to card creation form
        redirect_and_exit('list.php');
    }
} else {
    switch ($action) {
        case 'create':
            show_form_create();
            break;
        case 'save':
            $id = GETPOST('id', 'int');
            $result = save_citrus($id);
            if ($result > 0) {
                redirect_and_exit('card.php?id=' . $result);
            } else {
                if ($result === -3) {
                    setEventMessages('Error: some required fields were left empty.', array(), 'warnings');
                    show_form_create();
                } else {
                    setEventMessages('Database error: failed to save citrus.', array(), 'errors');
                    dol_print_error($db);
                    show_form_create();
                }
            }
            break;
        case 'edit':
            llxHeader();
            show_citrus(true);
            break;
        case 'delete':
            $form = new Form($db);
            $id = GETPOST('id', 'int');
            if ($id <= 0) {
                dol_print_error();
            }
            $ajax_confirm_delete = $form->formconfirm(
                current_page_with_params(array('id' => $id)),
                $langs->trans('DeleteCitrus'),
                $langs->trans('DeleteCitrusAskForConfirmation'),
                'confirm_delete',
                '',
                '',
                1
            );
            llxHeader();
            echo $ajax_confirm_delete;
            show_citrus(false);
            break;
        case 'confirm_delete':
            if ('yes' == GETPOST('confirm', 'alpha')) {
                $id = GETPOST('id', 'int');
                $id = $citrusDAO->fetch($id);
                if ($id <= 0) {
                    dol_print_error();
                    var_dump($id);
                } else {
                    $result = $citrusDAO->remove();
                    if($result > 0) {
                        redirect_and_exit('list.php');
                    } else {
                        dol_print_error();
                        var_dump($result);
                    }
                }
            } else {
                llxHeader();
                show_citrus(false);
            }
            break;
        default:
            llxHeader();
            show_citrus(false);
    }
}
// Display the bottom part of the Dolibarr standard interface (mostly closing tags, except in some specific cases)
llxFooter();

