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

$action = GETPOST('action', 'alpha');
$limit = GETPOST('limit', 'int') ?: $conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha') ?: 'citrus.rowid';
$sortorder = GETPOST("sortorder",'alpha') ?: 'ASC';
$page = max(0, GETPOST("page",'int') ?: 0);
$offset = $limit * $page;
$pageprev = max(0, $page - 1);
$pagenext = $page + 1;
$id = GETPOST("id",'int');


/*
 * Actions
 */

/*
 * View
 */

$userstatic=new User($db);

$new_card_url = dol_buildpath('citrusmanager/card.php?action=create', 1);
$new_card_btn  = '
<a href="'. $new_card_url . '">
    <span class="fa fa-plus-circle valignmiddle"></span>
</a>';

llxHeader('', $langs->trans('CitrusList'));



$countSQL = <<<SQL
    SELECT COUNT(citrus.rowid)
    FROM llx_citrusmanager_citrus as citrus
    LEFT JOIN llx_user as user
    ON citrus.fk_user_creat = user.rowid
    AND citrus.entity = __CONF_ENTITY__
SQL;
$countSQL = template_fill($countSQL, array('CONF_ENTITY' => $conf->entity));

$listSQL = <<<SQL
    SELECT 
           citrus.rowid,
           citrus.ref,
           citrus.label,
           citrus.price,
           citrus.date_creation,
           citrus.tms,
           user.login,
           user.firstname,
           user.lastname
    FROM llx_citrusmanager_citrus as citrus
    LEFT JOIN llx_user as user
    ON citrus.fk_user_creat = user.rowid
    AND citrus.entity = __CONF_ENTITY__
SQL;

$list_row_template = <<<HTML
<tr class="oddeven">
    <td align="left" class="id-and-ref-cell">
        <a href="{LINK_TO_CARD}">{O:rowid}&nbsp;{PICTO_CITRUS}&nbsp;{O:ref}</a>
    </td>    
    <td align="left" class="label-cell">{O:label}</td>    
    <td align="left" class="price-cell">{OBJ_PRICE}</td>    
    <td align="left" class="date-cell">{O:date_creation}</td>    
    <td align="left" class="category-cell">{CATEGORY}</td>    
    <td align="left" class="product-cell">{PARENT_PRODUCT}</td>
    <td align="left" class="action-cell">
        <a href="{URL_EDIT}">{IMG_EDIT}</a>
        <a href="{URL_DELETE}">{IMG_DELETE}</a>
    </td>    
</tr>
HTML;


$listSQL = template_fill($listSQL, array('CONF_ENTITY' => $conf->entity));
$listSQL .= $db->order($sortfield, $sortorder);
$listSQL .= $db->plimit($limit, $offset);

//if (! $user->admin) $listSQL .= " AND (b.fk_user = ".$user->id." OR b.fk_user is NULL OR b.fk_user = 0)";

$responseCountSQL = $db->query($countSQL);
$count_field_name = 'COUNT(citrus.rowid)';
$total_row_count = $db->fetch_object($responseCountSQL)->$count_field_name;

$responseSQL = $db->query($listSQL);
$db->free($responseCountSQL);

if ($responseSQL) {
    $page = "$page";
    $varexp = array(
        'page' => $page,
        'param' => $param,
        'sortfield' => $sortfield,
        'sortorder' => $sortorder,
        'num' => $db->num_rows($responseSQL),
        'totalnboflines' => $total_row_count
    );
    print_barre_liste(
        $langs->trans("CitrusList"),
        $page,
        $_SERVER['PHP_SELF'],
        $param,
        $sortfield,
        $sortorder,
        '',
        $db->num_rows($responseSQL)+1,
        $total_row_count,
        'title_generic.png',
        0,
        $new_card_btn
    );

	$param = "";
	echo '<div class="div-table-responsive">', "\n";
	echo '<table class="tagtable liste">', "\n";

	echo '<tr class="liste_titre_filter">', "\n";

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
        $param,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "Label",
        $_SERVER["PHP_SELF"],
        "citrus.label",
        "",
        $param,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "CitrusPrice",
        $_SERVER["PHP_SELF"],
        "citrus.price",
        "",
        $param,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        "Date",
        $_SERVER['PHP_SELF'],
        'citrus.date_creation',
        '',
        $param,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        'CitrusCategory',
        $_SERVER['PHP_SELF'],
        'citrus.category',
        '',
        $param,
        'align="left"',
        $sortfield,
        $sortorder
    );
    print_liste_field_titre(
        'Product',
        $_SERVER['PHP_SELF'],
        'citrus.fk_product',
        '',
        $param,
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
        $url_of_card = dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid, 1);
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
                'CATEGORY' => '',
                'PARENT_PRODUCT' => '',
                'IMG_EDIT' => img_edit(),
                'IMG_DELETE' => img_delete(),
                'URL_EDIT' => dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid . '&action=edit', 1),
                'URL_DELETE' => dol_buildpath('citrusmanager/card.php?id=' . $obj->rowid . '&action=delete', 1)
            ),
            $obj
        );
        /*echo '<tr class="oddeven">';
		// Id
		echo '<td align="left">';
		echo $surround(
		    'a',
            $obj->rowid .
            img_object(
                $langs->trans('ShowCitrus'),
                'citrus@citrusmanager',
                'style="max-width: 1em"'
            ) . '&nbsp' . $obj->ref,
            array('href' => $url_of_card)
        );
		echo '</td>';

		echo $surround('td', $obj->label, array('align' => 'left'));
        echo $surround('td', $obj->price ?: $langs->trans('Unavailable'), array('align' => 'left'));
        echo $surround('td', $obj->date_creation, array('align' => 'left'));
        echo $surround(
            'td',
            $surround(
                'a',
                img_edit(),
                array('href' => 'card.php?id=' . $obj->rowid . '&action=edit')
            ) . $surround(
                'a',
                img_delete(),
                array('href' => 'card.php?id=' . $obj->rowid . '&action=delete')
            ),
            array()
        );
		echo "</tr>\n";*/
	}
	echo "</table>";
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

