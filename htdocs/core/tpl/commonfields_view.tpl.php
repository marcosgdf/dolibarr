<?php
/* Copyright (C) 2017  Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $action
 * $conf
 * $langs
 *
 * $keyforbreak may be defined to key to switch on second column
 */
?>
<!-- BEGIN PHP TEMPLATE commonfields_view.tpl.php -->
<?php

foreach($object->fields as $key => $val)
{
	// Discard if extrafield is a hidden field on form
	if (abs($val['visible']) != 1) continue;

	if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field
	if ($key == 'status') continue;	// Status is already in dol_banner

	$value=$object->$key;

	print '<tr><td';
	print ' class="titlefield';
	if ($val['notnull'] > 0) print ' fieldrequired';
	if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
	print '"';
	print '>'.$langs->trans($val['label']).'</td>';
	print '<td>';
	print $object->showOutputField($val, $key, $value, '', '', '', 0);
	//print dol_escape_htmltag($object->$key, 1, 1);
	print '</td>';
	print '</tr>';

	if (! empty($keyforbreak) && $key == $keyforbreak) break;						// key used for break on second column
}

print '</table>';
print '</div>';
print '<div class="fichehalfright">';
print '<div class="ficheaddleft">';
print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent">';

$alreadyoutput = 1;
foreach($object->fields as $key => $val)
{
	if ($alreadyoutput)
	{
		if (! empty($keyforbreak) && $key == $keyforbreak) $alreadyoutput = 0;		// key used for break on second column
		continue;
	}

	if (abs($val['visible']) != 1) continue;	// Discard such field from form
	if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field
	if ($key == 'status') continue;	// Status is alreadt in dol_banner

	$value=$object->$key;

	print '<tr><td';
	print ' class="titlefield';
	if ($val['notnull'] > 0) print ' fieldrequired';
	if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
	print '"';
	print '>'.$langs->trans($val['label']).'</td>';
	print '<td>';
	print $object->showOutputField($val, $key, $value, '', '', '', 0);
	//print dol_escape_htmltag($object->$key, 1, 1);
	print '</td>';
	print '</tr>';
}

?>
<!-- END PHP TEMPLATE commonfields_view.tpl.php -->