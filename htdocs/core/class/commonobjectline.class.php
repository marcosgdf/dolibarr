<?php
/* Copyright (C) 2006-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/**
 *       \file       htdocs/core/class/commonobjectline.class.php
 *       \ingroup    core
 *       \brief      File of the superclass of classes of lines of business objects (invoice, contract, PROPAL, commands, etc. ...)
 */


/**
 *		\class 		CommonObjectLine
 *       \brief 		Superclass for class inheritance lines of business objects
 */

abstract class CommonObjectLine
{
    //! Database handler
    protected $db;
    //! Unit id in database
    public $fk_unit;
    
    /**
     *	Returns the text label from units dictionnary
     *
     * 	@param		string  label type (long or short)
     *	@return		int		<0 if ko, label if ok
     */
	public function get_unit_label($type='long')
	{
		global $langs;
		
		$langs->load('products');
		
		$this->db->begin();
		
		$label_type = 'label';
		
		if ($type == 'short')
		{
			$label_type = 'short_label';
		}
		
		$sql = 'select '.$label_type.' from '.MAIN_DB_PREFIX.'c_units where rowid='.$this->fk_unit;
		$resql = $this->db->query($sql);
		if($resql && $resql->num_rows > 0)
		{
			$res = $this->db->fetch_array($resql);
			$label = $res[$label_type];
			$this->db->free($resql);
			return $langs->trans($label);
		}
		else
		{
			$this->error=$this->db->error().' sql='.$sql;
			dol_syslog(get_class($this)."::get_unit_label Error ".$this->error, LOG_ERR);
			return -1;
		}
	}
}

?>
