<?php
/**
 * @version $Id$
 * @package    EasyTable
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('=;)');

jimport( 'joomla.application.component.model' );

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableModelEasyTables extends JModel
{
	var $_data = null;
	
	/**
	 * Gets the tables
	 * @return data
	 */
	function &getData()
	{
		if(empty($this->_data))
			{
				$query = "SELECT * FROM #__easytables WHERE published = '1' ORDER BY easytablename ASC";
				
				$this->_data = $this->_getList($query);
			}
		return $this->_data;
	}// function
}// class
