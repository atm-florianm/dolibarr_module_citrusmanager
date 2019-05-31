<?php
/* Copyright (C) 2019 Florian Mortgat / ATM Consulting
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/custom/citrusmanager/class/citrus.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/citrusmanager/lib/citrusmanager.lib.php';

// Load translation files required by the page
$langs->loadLangs(array('citrusmanager', 'admin'));

$action    = GETPOST('action', 'alpha');
$limit     = GETPOST('limit', 'int') ?: $conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha') ?: 'citrus.rowid';
$sortorder = GETPOST("sortorder",'alpha') ?: 'ASC';
$page      = max(0, GETPOST("page",'int') ?: 0);
$offset    = $limit * $page;
$pageprev  = max(0, $page - 1);
$pagenext  = $page + 1;
$id        = GETPOST("id",'int');

$userstatic=new User($db);

$new_card_url = dol_buildpath('citrusmanager/card.php?action=create', 1);
$new_card_btn  = '
<a href="'. $new_card_url . '">
    <span class="fa fa-plus-circle valignmiddle"></span>
</a>';

$filter_config = array(
    'citrus.ref'            => 'text',
    'citrus.label'          => 'text',
    'citrus.price'          => 'text',
    'citrus.date_creation'  => 'date',
    'category.ref'          => 'text',
    'product.ref'           => 'text'
);

llxHeader('', $langs->trans('CitrusList'));

function build_sql_filters() {
    global $db;
    global $filter_config;
    $ret = '';
    $filters = array();
    foreach ($filter_config as $sql_filter_field_name => $filter_type) {
        $http_parameter_name = 'search_' . str_replace('.', '_', $sql_filter_field_name);
        $val = GETPOST($http_parameter_name);
        if (!empty($val)) {
            $filters[] = '' . $sql_filter_field_name . ' LIKE ' . '"%' . $db->escape($val) . '%"';
        }
    }
    if (!empty($filters)) {
        return ' AND ' . implode(' AND ', $filters);
    }

    return '';
}

$sql_config = array(
    'CONF_ENTITY' => $conf->entity,
    'llx' => MAIN_DB_PREFIX,
    'FILTERS' => build_sql_filters()
);
$countSQL = <<<SQL
    SELECT COUNT(citrus.rowid)
    FROM __llx__citrusmanager_citrus as citrus
    LEFT JOIN __llx__user as user
    ON citrus.fk_user_creat = user.rowid
    LEFT JOIN __llx__c_citrus_category as category
    ON citrus.fk_category = category.rowid
    LEFT JOIN __llx__product as product
    ON citrus.fk_product = product.rowid
    WHERE citrus.entity = __CONF_ENTITY__
SQL
    . ' __FILTERS__';
$countSQL = template_fill($countSQL, $sql_config);

$listSQL = <<<SQL
    SELECT 
           citrus.rowid,
           citrus.ref,
           citrus.label,
           citrus.price,
           citrus.date_creation,
           category.ref as category_ref,
           product.ref as product_ref,
           product.rowid as product_id,
           citrus.tms,
           user.login,
           user.firstname,
           user.lastname
    FROM __llx__citrusmanager_citrus as citrus
    LEFT JOIN __llx__user as user
    ON citrus.fk_user_creat = user.rowid
    LEFT JOIN __llx__product as product
    ON citrus.fk_product = product.rowid
    LEFT JOIN __llx__c_citrus_category as category
    ON citrus.fk_category = category.rowid
    WHERE citrus.entity = __CONF_ENTITY__
SQL
    . ' __FILTERS__';
$listSQL = template_fill($listSQL, $sql_config);
$listSQL .= $db->order($sortfield, $sortorder);
$listSQL .= $db->plimit($limit, $offset);

$list_row_template = <<<HTML
<tr class="oddeven">
    <td align="left" class="id-and-ref-cell">
        <a href="{LINK_TO_CARD}">{O:rowid}&nbsp;{PICTO_CITRUS}&nbsp;{O:ref}</a>
    </td>
    <td align="left" class="label-cell">{O:label}</td>
    <td align="left" class="price-cell">{OBJ_PRICE}</td>
    <td align="left" class="date-cell">{O:date_creation}</td>
    <td align="left" class="category-cell">{O:category_ref}</td>
    <td align="left" class="product-cell">{PRODUCT}</td>
    <td align="left" class="action-cell">
        <a href="{URL_EDIT}">{IMG_EDIT}</a>
        <a href="{URL_DELETE}">{IMG_DELETE}</a>
    </td>
</tr>
HTML;

$responseCountSQL = $db->query($countSQL);
if (!$responseCountSQL) dol_print_error($db);
$count_field_name = 'COUNT(citrus.rowid)';
$total_row_count = $db->fetch_object($responseCountSQL)->$count_field_name;
$db->free($responseCountSQL);


$responseSQL = $db->query($listSQL);
if ($responseSQL) {
    $page = "$page";

    $filter_parameters = array();
    foreach($filter_config as $filter_name => $filter_type) {
        $filter_name = 'search_' . str_replace('.', '_', $filter_name);
        $value = GETPOST($filter_name);
        if (!empty($value)) {
            $filter_parameters[] = $filter_name . '=' . urlencode($value);
        }
    }
    $filter_parameters = '&' . implode('&', $filter_parameters);

    print_barre_liste(
        $langs->trans("CitrusList"),
        $page,
        $_SERVER['PHP_SELF'],
        $filter_parameters,
        $sortfield,
        $sortorder,
        '',
        $db->num_rows($responseSQL)+1,
        $total_row_count,
        'title_generic.png',
        0,
        $new_card_btn
    );

	echo '<div class="div-table-responsive">', "\n";
	echo '<form>';
	echo '<table class="tagtable liste">', "\n";

	echo '<tr class="liste_titre_filter">', "\n";
	$filter_input_tpl = "\t" . '<td><input type="{type}" name="{name}" value="{val}" /></td>' . "\n";
	foreach ($filter_config as $field => $type) {
	    $name = 'search_' . str_replace('.', '_', $field);
	    $td = template_fill($filter_input_tpl, array(
	        'type' => $type,
            'name' => $name,
            'val'  => GETPOST($name)
        ));
	    echo $td;
    }
    echo '<td><input type="submit" /></td>', "\n\t";
	// TODO: display filter inputs
    // TODO: enable user to choose what columns they want
    // TODO: enable mass actions rather than individual actions


	echo '</tr>', "\n";

	echo '<tr class="liste_titre">', "\n";

	print_liste_field_titre(
	    "Ref",
        $_SERVER["PHP_SELF"],
        "citrus.rowid",
        "",
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "Label",
        $_SERVER["PHP_SELF"],
        "citrus.label",
        "",
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "CitrusPrice",
        $_SERVER["PHP_SELF"],
        "citrus.price",
        "",
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "Date",
        $_SERVER['PHP_SELF'],
        'citrus.date_creation',
        '',
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        'CitrusCategory',
        $_SERVER['PHP_SELF'],
        'citrus.category',
        '',
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        'Product',
        $_SERVER['PHP_SELF'],
        'citrus.fk_product',
        '',
        $filter_parameters,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "Actions"
    );
    echo '</tr>', "\n";

    $row_count = $db->num_rows($responseSQL);
    for ($i = 0; $i < $row_count; $i++)
    {
		$obj = $db->fetch_object($responseSQL);
		//var_dump($obj); die();
        $url_of_card = dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid, 1);
        $url_of_product_card = dol_buildpath(
            'product/card.php?id=' . $obj->product_id,
            1) . '&mainmenu=products';
		echo template_fill(
		    $list_row_template,
            array(
                'LINK_TO_CARD' => $url_of_card,
                'ROWID' => $obj->rowid,
                'PICTO_CITRUS' => img_object(
                    $langs->trans("ShowCitrus"),
                    'citrus@citrusmanager',
                    'style="max-width: 1.5em"'
                ),
                'OBJ_PRICE' => $obj->price ?: $langs->trans('Unavailable'),
                'CATEGORY' => '' . $obj->fk_category,
                'PRODUCT' => $obj->product_id ?
                    '<a href="'. $url_of_product_card .'">
                        ' . $obj->product_ref . '
                     </a>'
                    :
                    '',
                'IMG_EDIT' => img_edit(),
                'IMG_DELETE' => img_delete(),
                'URL_EDIT' => dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid . '&action=edit', 1),
                'URL_DELETE' => dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid . '&action=delete', 1)
            ),
            $obj
        );
	}
	echo "</table>";
    echo '</form>';
	echo '</div>';

	$db->free($responseSQL);
}
else
{
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();

