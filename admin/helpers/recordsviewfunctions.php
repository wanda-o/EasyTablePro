<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
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
		// Is there a filter?
		$ff = $params->get('filter_field', '');
		$ff = strpos($ff, ':') ? substr($ff, strpos($ff, ':') + 1) : $ff;
		$ff = $ff ? $ff = $db->quoteName($ff) : '';
		$ft = strtoupper(trim($params->get('filter_type', '')));
		$fv = $params->get('filter_value', '');

		if ($ff && $ft && $fv)
		{
			// Note this means a '|' in the first char of $fv is a valid character no an OR indicator
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

				$newWhereCond = self::createWhereForType($ft, $filterValue, $ff, $db);

				if (!empty($newWhereCond))
				{
					$whereCond .= $newWhereCond;
					$whereCond .= $i < $fvCount - 1 ? ' OR ' : '';
				}
			}

			if ($whereCond)
			{
				$whereCond = '(' . $whereCond . ')';
				$query->where($whereCond);
			}
		}
	}


	/**
	 * Where our WHERE condition is built.
	 *
	 * @param   string     $filterType   Our operator LIKE, IS, !=, <, >,
	 * @param   string     $filterValue  An unprocessed filter value
	 * @param   string     $filterField  The field the where is being applied to
	 * @param   JDatabase  $db           The Database object
	 *
	 * @return string
	 */
	private static function createWhereForType($filterType, $filterValue, $filterField, $db)
	{
		// Initialise our where condition
		$whereCond = '';

		// First we process our $filterValue to get the final value we're matching against
		$matchValue = self::createMatchValue($filterType, $filterValue, $db);

		if ($matchValue)
		{
			// Then we construct our where statement.
			switch ($filterType)
			{
				case 'LIKE':
				case 'IS':
				{
					$whereCond = $filterField . ' LIKE ' . $matchValue;
					break;
				}
				case '=':
				case '!=':
				case '<':
				case '>':
				{
					$whereCond = $filterField . ' ' . $filterType . ' ' . $matchValue;
					break;
				}
				default:
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_OPERATOR_ERROR_X', $filterType));
				}
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_MATCH_ERROR_X', $filterValue));
		}

		return $whereCond;
	}

	/**
	 * Prepares our value to match against taking into account the Operator.
	 *
	 * @param   string          $filterType   Our operator LIKE, IS, !=, <, >,
	 * @param   string          $filterValue  An unprocessed filter value
	 * @param   JDatabaseQuery  $db           The Database object.
	 *
	 * @return string
	 */
	private static function createMatchValue($filterType, $filterValue, $db)
	{
		// Standardise $filterValue
		$startWithPHPHashes = strpos($filterValue, '##') == 0;
		$endWithPHPHashes   = strrpos($filterValue, '##') == strlen($filterValue) - 2;
		$fvLength = strlen($filterValue);
		$filterValueType = ($startWithPHPHashes && $endWithPHPHashes && $fvLength > 10) ? 'PHP' : trim($filterValue);

		// Check for our predefined
		switch ($filterValueType)
		{
			case 'CURDATE()':
			case 'CURTIME()':
			case 'NOW()':
			case 'CONCAT(\'\', CURDATE())':
			case 'CONCAT(\'\', CURTIME())':
			case 'CONCAT(\'\', NOW())':
			{
				return $filterValue;
			}
			case 'PHP':
			{
				// Eval the php if it validates
				$phpFilter = self::stripHashes($filterValue);

				if (!self::validatePHPFilter($phpFilter))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_PHP_MATCH_ERROR_X', $filterValue));

					return false;
				}

				$filterValue = eval($phpFilter);

				if ((!is_string($filterValue) && !is_numeric($filterValue)) || is_object($filterValue))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_PHP_MATCH_WRONG_TYPE_X', print_r($filterValue, true)));

					return false;
				}
				break;
			}
			default:
				{
					// Everything else we escape
					$filterValue = $db->escape($filterValue);
				}
		}

		if (strtoupper($filterType) == 'LIKE')
		{
			$filterValue = '%' . $filterValue . '%';
		}

		return $db->quote($filterValue);
	}

	/**
	 * Make sure the PHP filter is likely to return a value.
	 *
	 * @param   string  $filterValue  The potential PHP statement(s).
	 *
	 * @return bool
	 */
	private static function validatePHPFilter($filterValue)
	{
		$filterValue = trim($filterValue);
		$singleStatementPHP = (strpos($filterValue, 'return ') == 0) && (substr($filterValue, -1) == ';');

		if (substr_count($filterValue, ';') > 1 && !$singleStatementPHP)
		{
			$lastStatment = trim(end(explode(';', $filterValue)));
			$multiStatementPHP = (strpos($lastStatment, 'return ') == 0);
		}
		else
		{
			$multiStatementPHP = false;
		}

		return $singleStatementPHP || $multiStatementPHP;
	}

	/**
	 * Strips preceeding and trailing ##
	 *
	 * @param   string  $phpFilter  A string starting and ending with ##
	 *
	 * @return  string
	 */
	private static function stripHashes($phpFilter)
	{
		$phpFilter = substr($phpFilter, 2, strlen($phpFilter) - 4);

		return $phpFilter;
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
            $uffArray = explode(':', $uff);
            $uff = $uffArray[1];
			$uff = $db->quoteName($uff);
			$user = JFactory::getUser();
			$userValue = $ufb == 'id' ? $user->id : $user->username;
			$whereCond = $uff . ' = ' . $db->quote($userValue);
			$query->where($whereCond);
		}
	}

	/**
	 * Helper function to add the Advanced filter if set. Each line is added to the query
	 * individually because of the JDatabase limitation on grouping and ORs.
	 *
	 * @param   EasyTableProModelTable  $et      The table we're creating the Advanced Filter for.
	 * @param   JDatabaseQuery          $query   The query to add too.
	 * @param   JRegistry               $params  The params containing the filter values.
	 * @param   JDatabase               $db      The Database object.
	 *
	 * @return  null
	 */
	public static function addAdvancedFilters($et, $query, $params, $db)
	{
		// Setup basics vars
		$jAp = JFactory::getApplication();
		$etp_sep = '||';

		// Extract the Advanced Filter value
		$advFilters = $params->get('advanced_filter_value');
		$filters = explode("\r\n", $advFilters);

		// Convert valid lines into conditions
		foreach ($filters as $filter)
		{
			// Copy our filter line
			$processedFilter = $filter;

			// Check for the conditions of a valid filter line
			$firstPipe = strpos($processedFilter, $etp_sep);
			$lastPipe  = strrpos($processedFilter, $etp_sep);
			$pipeCount = substr_count($processedFilter, $etp_sep);

			if (($pipeCount == 2) && ($lastPipe > $firstPipe + 2))
			{
				// Pre-process each filter
				list($field, $operator, $matchValue) = explode($etp_sep, $processedFilter);

				// Check the field exists in this table (who knows what users will put in here!)
				if (!array_key_exists($field, $et->table_meta))
				{
					$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_FIELD_ERROR_X', $field));
					continue;
				}

				// See if any curdate(), curtime() or now() match values are being compared to a string
				if (($matchValue == 'CURDATE()' || $matchValue == 'CURTIME()' || $matchValue == 'NOW()') && $et->table_meta[$field]['type'] == '0')
				{
					$matchValue = "CONCAT('', " . $matchValue . ")";
				}

				// Build our faux params
				$condition = array('filter_field' => $field, 'filter_type' => $operator, 'filter_value' => $matchValue);
				$fauxParams = new JRegistry;
				$fauxParams->loadArray($condition);

				// Add the filter
				self::addFilter($query, $fauxParams, $db);
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_ADV_FUNCTION_ERROR_X', $filter));
			}
		}
	}

	/**
	 * Get's our checkbox
	 *
	 * @param   int  $cid    Our cid.
	 *
	 * @param   int  $rowId  Our row Id.
	 *
	 * @return  mixed
	 *
	 * @since   1.1
	 */
	public static function getRecordCheckBox ($cid, $rowId)
	{
		$cb = JHtml::_('grid.id', $cid, $rowId);

		return($cb);
	}

	/**
	 * Create the delete record link.
	 *
	 * @param   int     $cid        Our cid.
	 *
	 * @param   int     $rowId      Our row id.
	 *
	 * @param   string  $tableName  Our table name.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function getDeleteRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_RECORDS_DELETE_LINK') . ' ' . $rowId . ' of table \'' . $tableName . '\' ';

		$jvTag = ET_General_Helper::getJoomlaVersionTag();

		if ($jvTag == 'j2')
		{
			$theDeleteLink = '<span class="hasTip" title="' . $link_text
				. '" style="margin-left:10px;" ><a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
				. '\',\'records.delete\');" title="' . $link_text . '" ><img src="' . JURI::root()
				. 'media/com_easytablepro/images/publish_x.png" alt="' . $link_text . '"/></a></span>';
		}
		else
		{
			$theDeleteLink = '<span class="hasTooltip btn btn-small" title="' . $link_text
				. '"><a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
				. '\',\'records.delete\');" title="' . $link_text . '" ><i class="icon-remove" style="color:red;"></i></a></span>';
		}

		return($theDeleteLink);
	}

	/**
	 * Create the edit record link.
	 *
	 * @param   int     $cid        Our cid.
	 * @param   int     $rowId      Our row id.
	 * @param   string  $tableName  Our table name.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function getEditRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_RECORDS_EDIT_LINK') . ' ' . $rowId . ' of table \'' . $tableName . '\' ';

		$jvTag = ET_General_Helper::getJoomlaVersionTag();

		if ($jvTag == 'j2')
		{
			$theEditLink = '<span class="hasTip" title="' . $link_text . '" style="margin-left:3px;" >'
				. '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
				. '\',\'record.edit\');" title="' . $link_text . '" ><img src="' . JURI::root()
				. 'media/com_easytablepro/images/edit.png" alt="' . $link_text . '" /></a></span>';
		}
		else
		{
			$theEditLink = '<span class="hasTip btn btn-small" title="' . $link_text . '">'
				. '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
				. '\',\'record.edit\');" title="' . $link_text . '" ><i class="icon-pencil" style="color:green;"></i></a></span>';
		}

		return($theEditLink);
	}
}

