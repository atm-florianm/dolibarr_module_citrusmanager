<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    citrusmanager/admin/setup.php
 * \ingroup citrusmanager
 * \brief   Citrusmanager setup page.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/citrusmanager.lib.php';

// Translations
$langs->loadLangs(array("admin", "citrusmanager@citrusmanager"));

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * Actions
 */
if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}


/*
 * View
 */

$page_name = "CitrusmanagerSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'
    .(
        $backtopage ?
            $backtopage
            :
            DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1'
    ).'">'
    .$langs->trans("BackToModuleList")
    .'</a>';

print load_fiche_titre(
    $langs->trans($page_name),
    $linkback,
    'object_citrusmanager@citrusmanager'
);

// Setup page goes here
echo '<div style="margin: 2em;">', $langs->trans("CitrusmanagerSetupPage"), '</div>';

$configForm = <<<HTML
<form action="{PHP_SELF}?action=save" method="post">
    <input
        type="hidden"
        name="token"
        value="{newtoken}"
        />
    <table style="width: 100%" class="noborder">
        <colgroup>
            <col width="50%"/><col width="50%"/>
        </colgroup>
        <tr class="liste_titre"><td class="validtitre">{T:Parameter}</td><td>{T:Value}</td></tr>
        <tr>
            <td>
                <span>{T:DefaultPrice}</span>
                <div class="classfortooltip inline-block"
                     title="{T:CitrusDefaultPriceTooltip}">
                    <img
                       src="/theme/eldy/img/info.png"
                       alt="Question mark as a context help symbol"
                       style="vertical-align: middle; cursor: help"
                       />
                </div>
            </td><td>
                <input
                    type="text"
                    step="0.01"
                    name="conf_default_price"
                    placeholder="{T:DefaultPrice}"
                    value="{CURRENT_DEFAULT_PRICE}"/>
            </td>
        </tr>
    </table>
    <div style="text-align: right">
        <input type="submit" class="butAction" value="{T:Save}" />
    </div>
</form>
HTML;

if ($action == 'save') {
    $default_price = GETPOST('conf_default_price', 'int');
    if ($default_price) {
        dolibarr_set_const($db, 'CITRUSMANAGER_DEFAULT_PRICE', $default_price, 'chaine', 0);
    }
}

$default_price = $conf->global->CITRUSMANAGER_DEFAULT_PRICE ?: 0;

echo template_fill(
    $configForm,
    array(
        'PHP_SELF' => $_SERVER['PHP_SELF'],
        'newtoken' => $_SESSION['newtoken'],
        'CURRENT_DEFAULT_PRICE' => $default_price
    )
);

// Page end
dol_fiche_end();

llxFooter();
$db->close();

