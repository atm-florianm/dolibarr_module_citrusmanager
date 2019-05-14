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

// Load translation files required by the page
$langs->loadLangs(array('citrusmanager', 'admin'));

$action = GETPOST('action', 'alpha');
$limit = GETPOST('limit', 'int') ?: $conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha') ?: 'citrus.tms';
$sortorder = GETPOST("sortorder",'alpha') ?: 'DSC';
$page = max(0, GETPOST("page",'int') ?: 0);
$offset = $limit * $page;
$pageprev = max(0, $page - 1);
$pagenext = $page + 1;
$id = GETPOST("id",'int');


/*
 * Actions
 */

if ($action == 'delete') {
}


/*
 * View
 */

$userstatic=new User($db);

$new_card_btn = '';
if (true /*$user->rights->citrusmanager->create*/) {
    $new_card_btn  = '<a class="butActionNew" href="card.php?action=create">';
    $new_card_btn .= '<span class="valignmiddle">'.$langs->trans('NewCitrus').'</span>';
    $new_card_btn .= '</a>';
}

print_barre_liste(
    $langs->trans("ListOfCitrus"),
    $page,
    $_SERVER['PHP_SELF'],
    $param,
    $sortfield,
    $sortorder,
    '',
    -1,
    '',
    'title_generic.png',
    0,
    $new_card_btn
);

llxHeader('', $langs->trans('CitrusList'));

$listSQL = <<<SQL
    SELECT 
           citrus.rowid,
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

$listSQL = str_replace('__CONF_ENTITY__', $conf->entity, $listSQL);
$listSQL .= $db->order($sortfield, $sortorder);
$listSQL .= $db->plimit($limit, $offset);

//if (! $user->admin) $listSQL .= " AND (b.fk_user = ".$user->id." OR b.fk_user is NULL OR b.fk_user = 0)";

$responseSQL = $db->query($listSQL);

$surround = function($tag, $contents, $params = []) {
    $param_list = '';
    foreach ($params as $param => $value) {
        $param_list .= ' ' . $param . '=' . $value;
    }
    $opening_tag = '<' . $tag . $param_list . '>';
    $closing_tag = '</' . $tag . '>';

    return $opening_tag . $contents . $closing_tag;
};


if ($responseSQL) {
	$param = "";
	echo '<div class="div-table-responsive">', "\n";
	echo '<table class="tagtable liste">', "\n";
	echo '<tr class="liste_titre">', "\n";

	print_liste_field_titre(
	    "Ref",
        $_SERVER["PHP_SELF"],
        "citrus.rowid",
        "",
        $param,
        'align="left"',
        $sortfield,
        $sortorder);
	print_liste_field_titre(
	    "Label",
        $_SERVER["PHP_SELF"],
        "citrus.label",
        "",
         $param,
        'align="left"',
        $sortfield,
        $sortorder);
	echo '</tr>', "\n";

    $row_count = $db->num_rows($responseSQL);
    $i = 0;
	while ($i < $row_count)
	{
		$obj = $db->fetch_object($responseSQL);
		echo '<tr class="oddeven">';
		// Id
		echo '<td align="left">';
		$url_of_card = 'card.php?id=' . $obj->rowid;
		echo $surround(
		    'a',
            img_object(
                $langs->trans("ShowCitrus"),
                "citrus"
            ) . ' ' . $obj->rowid,
            ['href' => $url_of_card]
        );
		echo '</td>';
		echo "</tr>\n";
		$i++;
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
