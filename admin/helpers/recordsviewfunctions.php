<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

/**
 * EasyTables Records View Helper
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */

require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php'; 

class ET_RecordsHelper
{
	/**
	 * Helper function to add a filter based on supplied params.
	 * The crazy where build is because JDatabase doesn't group/mix AND's and ORs
	 *
	 * @param   JDatabaseQuery  $query   The query to add too.
	 * @param   JRegistry       $params  The params containing the filter values.
	 * @param   JDatabase       $db      The Database object
	 *
	 * @return  null
	 */
	public static function addFilter ($query, $params, $db)
	{
		// Is their a filter?
		$ff = $params->get('filter_field', '');
		$ff = substr($ff, strpos($ff, ':') + 1);
		$ff = $ff ? $ff = $db->quoteName($ff) : '';
		$ft = $params->get('filter_type', '');
		$fv = $params->get('filter_value', '');
		$fv = strpos($fv, '|') ? explode('|', $fv) : $fv;
		$whereCond = '';

		if (!is_array($fv))
		{
			$fv = (array) $fv;
		}

		$fvCount = count($fv);

		for ($i = 0; $i < $fvCount; $i++)
		{
			$filterValue = $fv[$i];

			if ($ff && $ft && $filterValue)
			{
				$whereCond .= $ft == 'LIKE' ? $ff . ' LIKE ' . $db->quote('%' . $filterValue . '%') : $ff . ' LIKE ' . $db->quote($filterValue);
				$whereCond .= $i < $fvCount - 1 ? ' OR ' : '';
			}
		}

		if ($whereCond)
		{
			$whereCond = '(' . $whereCond . ')';
			$query->where($whereCond);
		}
	}

	/**
	 * Helper function to add the User filter if set.
	 *
	 * @param   JDatabaseQuery  $query   The query to add too.
	 * @param   JRegistry       $params  The params containing the filter values.
	 * @param   JDatabase       $db      The Database object
	 *
	 * @return  null
	 */
	public static function addUserFilter ($query, $params, $db)
	{
		// Is the User filter set?
		$uf  = $params->get('enable_user_filter', 0);
		$ufb = $params->get('filter_records_by', '');
		$uff = $params->get('user_filter_field', '');

		if ($uf && $ufb && $uff)
		{
			$uff = $db->quoteName($uff);
			$user = JFactory::getUser();
			$userValue = $ufb == 'id' ? $user->id : $user->username;
			$whereCond = $uff . ' = ' . $db->quote($userValue);
			$query->where($whereCond);
		}
	}
}

