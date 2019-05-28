<?php
/* Copyright (C) 2019 Florian Mortgat
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
 * @param $template string     Template string with some var names in brackets ({VAR} or {T:KEY})
 * @param $replacements array  Replacements for each VAR
 * @return string              The filled template: {VAR} inclusions are replaced with dictionary
 *                             values and {T:KEY} inclusions are replaced with their translation from
 *                             the $langs object.
 */
function template_fill ($template, $replacements) {
    global $langs;
    $filled_template = $template;
    // Templating: replace some underscore-prefixed names with their dictionary value
    foreach ($replacements as $key => $val) {
        $filled_template = str_replace('{' . $key . '}', $val, $filled_template);
        $filled_template = str_replace('__' . $key . '__', $val, $filled_template);
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
}

/**
 * @param $params array  URL parameters to be appended to the base URL
 * @return string        The URL of the current PHP page with additional parameters
 */
function current_page_with_params ($params) {
    $current_page = $_SERVER['PHP_SELF'];
    return $current_page . '?' . http_build_query($params);
};

/**
 * As name says: adds a redirection header to $url and stops the interpreter
 * @param $relative_url string  The URL part that comes after the module name
 */
function redirect_and_exit($relative_url)
{
    $url = dol_buildpath(
        'citrusmanager/' . $relative_url,
        1,
        1
    );
    header('Location: ' . $url);
    exit;
}


///**
// * \file    citrusmanager/lib/citrusmanager.lib.php
// * \ingroup citrusmanager
// * \brief   Library files with common functions for Citrusmanager
// */
//
///**
// * Prepare admin pages header
// *
// * @return array
// */
//function citrusmanagerAdminPrepareHead()
//{
//	global $langs, $conf;
//
//	$langs->load("citrusmanager@citrusmanager");
//
//	$h = 0;
//	$head = array();
//
//	$head[$h][0] = dol_buildpath("/citrusmanager/admin/setup.php", 1);
//	$head[$h][1] = $langs->trans("Settings");
//	$head[$h][2] = 'settings';
//	$h++;
//	$head[$h][0] = dol_buildpath("/citrusmanager/admin/about.php", 1);
//	$head[$h][1] = $langs->trans("About");
//	$head[$h][2] = 'about';
//	$h++;
//
//	// Show more tabs from modules
//	// Entries must be declared in modules descriptor with line
//	//$this->tabs = array(
//	//	'entity:+tabname:Title:@citrusmanager:/citrusmanager/mypage.php?id=__ID__'
//	//); // to add new tab
//	//$this->tabs = array(
//	//	'entity:-tabname:Title:@citrusmanager:/citrusmanager/mypage.php?id=__ID__'
//	//); // to remove a tab
//	complete_head_from_modules($conf, $langs, $object, $head, $h, 'citrusmanager');
//
//	return $head;
//}
