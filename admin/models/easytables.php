<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.model' );

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableModelEasyTables extends JModel
{

	/**
	 * EasyTables data array
	 *
	 * @var array
	 */
	var $data;


	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
			. ' FROM #__easytables '
		;

		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->data ))
		{
			$query = $this->_buildQuery();
			$this->data = $this->_getList( $query );
		}

		return $this->data;
	}

}
