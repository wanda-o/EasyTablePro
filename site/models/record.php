<?php /**
 * @package     EasyTable_Pro
 * @subpackage  Models
 * @author      Craig Phillips <craig@craigphillips.biz>
 * @copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @link        http://www.seepeoplesoftware.com
 */


//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.modelitem');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * EasyTableProRecord Model
 *
 */
class EasyTableProModelRecord extends JModelItem
{
	/**
	 * @var string
	 */
	protected $context = 'com_easytablepro.record';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		/** @var $jAp JSite */
		$jAp = JFactory::getApplication();


		// Load state from the request.
		$pk = $jAp->input->get('id');
		$this->setState('table.id', $pk);

		$pk = $jAp->input->get('rid');
		$this->setState('record.id', $pk);

		// Load the parameters.
		$params = $jAp->getParams('com_easytablepro');
		$this->setState('params', $params);

		// Get the current menu item's table id if it exists
		$menuItem = $jAp->input->get('Itemid');
		$menu = $jAp->getMenu();
		$currentMenuItem = $menu->getItem($menuItem);

		if (isset($currentMenuItem) && ($currentMenuItem->query['option'] == 'com_easytablepro') && isset($currentMenuItem->query['id']))
		{
			$etIdFromMenu = $currentMenuItem->query['id'];
		}
		else
		{
			$etIdFromMenu = '';
		}

		$this->setState('etIdFromMenu', $etIdFromMenu);
	}

	/**
	 * Method to get a single row from the table
	 *
	 * @param   null  $pk  Optional id of EasyTable
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$etID = (!empty($pk)) ? $pk : (int) $this->getState('table.id');
		$pk = (!empty($pk)) ? $pk : $this->getState('record.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$etID . '.' . $pk]))
		{
			try
			{
				// Get our DB connection
				$db = $this->getDbo();

				// Setup a new query
				$query = $db->getQuery(true);

				// Get our table meta data
				$et = ET_General_Helper::getEasytable($etID);

				if (!$et)
				{
					return JError::raiseError(404, JText::_('COM_EASYTABLEPRO_RECORD_ERROR_TABLE_NOT_FOUND'));
				}

				// @todo move to general helper functions
				// First up lets convert these params to a JRegister
				$rawParams = $et->params;
				$paramsObj = new JRegistry;
				$paramsObj->loadString($rawParams);
				$et->params = $paramsObj;

				// Get our record from the right table
				$query->select('*');
				$query->from($db->quoteName($et->ettd_tname));
				$query->where($db->quoteName($et->key_name) . ' = ' . $db->quote($pk));
				$db->setQuery($query);
				$record = $db->loadObject();

				// Get our elements for next & prev records, making sure we use the
				// params relevant to the current table.
				if ($et->id == $this->getState('etIdFromMenu'))
				{
					$paramsToUse = $this->getState('params', null);
				}
				else
				{
					$paramsToUse = $et->params;
				}

				$orderFieldId = $paramsToUse->get('sort_field', 0);

				if ($orderFieldId != 0)
				{
					$orderField = substr($orderFieldId, strpos($orderFieldId, ':') + 1);
					$ordDir = $paramsToUse->get('sort_order', 'ASC');
				}
				else
				{
					$orderField = $et->key_name;
					$ordDir = 'ASC';
				}

				$title_leaf = $et->params->get('title_field');

				if ($i = strpos($title_leaf, ':'))
				{
					$title_leaf = substr($title_leaf, $i + 1);
				}

				// @todo add title_field id to prev/next request to retrieve leaf
				$prevId = $this->getAdjacentId($et->ettd_tname, $orderField, $ordDir, $record->$orderField, $title_leaf, false, $et->key_name);
				$nextId = $this->getAdjacentId($et->ettd_tname, $orderField, $ordDir, $record->$orderField, $title_leaf, true, $et->key_name);

				// Do we need linked records?
				$show_linked_table = $et->params->get('show_linked_table', 0);
				$linked_data = $let = null;

				if ($show_linked_table)
				{
					$linked_table = $et->params->get('id', 0);
					$key_field_raw = $et->params->get('key_field', 0);
					$key_field_parts = explode(':', $key_field_raw);
					$key_field = $key_field_parts[1];
					$linked_key_field = $et->params->get('linked_key_field', 0);

					// We need all 3 id's to proceed
					if ($linked_table && $key_field && $linked_key_field)
					{
						// Retreive the linked table
						$let = ET_General_Helper::getEasytableMetaItem($linked_table);
						$letP = new JRegistry;
						$letP->loadArray($let->params);
						$let->params = $letP;
						$key_field = $et->table_meta[$key_field]['fieldalias'];
						$key_field_value = $record->$key_field;
						$linked_key_field_meta = $let->table_meta[$linked_key_field];
						$linked_key_field = $linked_key_field_meta['fieldalias'];
						$linked_data = $this->getLinked($let, $key_field_value, $linked_key_field);

						// If no matching records are found we act as if not linked (makes everything cleaner).
						if (!count($linked_data))
						{
							$et->params->set('show_linked_table', false);
							$linked_data = $let = null;
						}
					}
					else
					{
						$et->params->set('show_linked_table', false);
					}
				}

				if ($error = $db->getErrorMsg())
				{
					throw new Exception($error);
				}

				if (empty($record))
				{
					return JError::raiseError(404, JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_ERROR_RECORD_NOT_FOUND', $pk));
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$et->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					$et->params->set('access-view', in_array($et->access, $groups));
				}

				$item = (object) array( 'easytable'    => $et,
										'record'       => $record,
										'prevRecordId' => $prevId,
										'nextRecordId' => $nextId,
										'linked_table' => $let,
										'linked_records' => $linked_data);

				$this->_item[$etID . '.' . $pk] = $item;
			}
			catch (JException $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$etID . '.' . $pk] = false;
				}
			}
		}

		return $this->_item[$etID . '.' . $pk];
	}

	/**
	 * getLinked()
	 *
	 * @param   EasyTableProModelTable  $linked_table      The current EasyTable object.
	 *
	 * @param   string                  $key_field_value   The current EasyTable object.
	 *
	 * @param   string                  $linked_key_field  The current EasyTable object.
	 *
	 * @return  array                   $linked_data
	 *
	 * @since   1.1
	 */
	protected function getLinked ($linked_table = null, $key_field_value = '', $linked_key_field = '')
	{
		if (($linked_table == null) || ($key_field_value == '') || ($linked_key_field == ''))
		{
			return false;
		}
		else
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Get all fields of our matching records
			$query->select('*');
			$query->from($db->quoteName($linked_table->ettd_tname));
			$query->where($db->quoteName($linked_key_field) . ' = ' . $db->quote($key_field_value));

			// Set our query and retreive our records
			$db->setQuery($query);
			$linked_data = $db->loadAssocList();

			return $linked_data;
		}
	}

	/**
	 * getAdjacentId()
	 *
	 * @param   string  $tableName   The tablename
	 *
	 * @param   string  $orderField  The field to order by
	 *
	 * @param   string  $ordDir      The order direction
	 *
	 * @param   string  $curOrdFldV  The value of order field in the current row
	 *
	 * @param   string  $leafField   The leaf field.
	 *
	 * @param   bool    $next        Next/Prev flag.
	 *
	 * @param   string  $pk          The primary key.
	 *
	 * @return array|mixed
	 *
	 * @since   1.1
	 */
	protected function getAdjacentId ($tableName, $orderField, $ordDir, $curOrdFldV, $leafField, $next=false, $pk = 'id')
	{
		// Do we need to flip for reverse sort order
		if ($ordDir == 'DESC')
		{
			$next = !$next;
		}

		// Next record?
		if ($next)
		{
			$eqSym = '>';
			$sortOrder = 'ASC';
		}
		else
		{
		// So prev. record.
			$eqSym = '<';
			$sortOrder = 'DESC';
		}

		// Make sure we have a field value to check against...
		$adjacentRow = array();

		if ($curOrdFldV)
		{
			// Get the current database object
			$db = JFactory::getDBO();

			// New query
			$query = $db->getQuery(true);

			$query->from($db->quoteName($tableName));
			$query->select($db->quoteName($pk));

			if ($leafField)
			{
				$query->select($db->quoteName($leafField));
			}

			$query->where($db->quoteName($orderField) . ' ' . $eqSym . ' ' . $db->quote($curOrdFldV));
			$query->order($db->quoteName($orderField) . ' ' . $sortOrder);
			$db->setQuery($query, 0, 1);

			$adjacentRow = $db->loadRow();

			// Convert leaf to URL safe
			$adjacentRow[1] = $leafField ? JFilterOutput::stringURLSafe(substr($adjacentRow[1], 0, 100)) : '';
		}

		return $adjacentRow;
	}
}
