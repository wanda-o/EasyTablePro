<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');


/**
 * EasyTable Table class
 *
 * 
 */
class EasyTableProTableTable extends JTable
{
	/**
		Check function
	 */
	 function check()
	{
		/* Make sure we have an alias for the table - nicer for linking, css etc */
	    jimport( 'joomla.filter.output' );
	    if(empty($this->easytablealias)) {
	            $this->easytablealias = $this->easytablename;
	    }
	    $this->easytablealias = JFilterOutput::stringURLSafe($this->easytablealias);
	 
	    /* Any other checks ?
           Not yet Bob, but ya never know! */
	    return true;
	}

	/**
		Bind function - to support table specific params
	 */
	function bind($array, $ignore = '')
	{
		$user = JFactory::getUser();
		$uid = $user->get('id',0);
		// Update record modified and if necessary created datetime stamps
		if(key_exists( 'id', $array ) && !$array['id'])
		{
			$array['created_'] = date( 'Y-m-d H:i:s' );
			$array['created_by'] = $uid;
		}
		// Check for missing creator
		if($array['created_by'] == 0) $array['created_by'] = $uid;

		$array['modified_'] = date( 'Y-m-d H:i:s' );
		$array['modifiedby_'] = $uid;

		// Change the params back to a string for storage
		if (key_exists( 'params', $array ) && is_array( $array['params'] ))
        {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db) {
		parent::__construct('#__easytables', 'id', $db);
	}

	/**
	 * Redefined asset name, as we support action control
	 */

	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_easytablepro.table.'.(int) $this->$k;
	}

	/**
	 * We provide our global ACL as parent
	 * @see JTable::_getAssetParentId()
	 */

	protected function _getAssetParentId($table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_easytablepro');
		return $asset->id;
	}
}
?>
