<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
// No Direct Access
defined('_JEXEC') or die('Restricted Access');

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
/**
 * EasyTablePro Table Model
 *
 * @package     EasyTablePro
 *
 * @subpackage  Models
 *
 * @since       1.0
 */
class EasyTableProModelRecord extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 *
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 *
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable	A database object
	 *
	 * @since   1.1
	 */
	public function getTable($type = 'Record', $prefix = 'EasyTableProTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method definition required to avoid strict warning about partially declared class.
	 *
	 * @param   mixed  $data      Form data.
	 *
	 * @param   bool   $loadData  Force data to be loaded for form?
	 *
	 * @see JModelForm::getForm()
	 *
	 * @return  bool     Always false for us.
	 */
	public function getForm($data = '', $loadData = true)
	{
		// Required to avoid strict errors but not used by EasyTable Pro.
		return false;
	}

	/**
	 * Retrieve selected record from current table.
	 *
	 * @param   int  $pk  Ignored by us as we need two parts.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	public function getItem($pk = null)
	{
		$trid = ET_General_Helper::getTableRecordID();

		$record = parent::getItem($trid[1]);
		$easytable = ET_General_Helper::getEasytableMetaItem($trid[0]);
		$item = array('trid' => $trid, 'record' => $record, 'easytable' => $easytable);

		return $item;
	}

	/**
	 * We override so that we can add our title and alias.
	 *
	 * @param   array  $data  Our data.
	 *
	 * @return  array
	 */
	public function save($data)
	{
		// Alter the title for save as copy
		if (JFactory::getApplication()->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle('', $data['alias'], $data['title']);
			$data['title']	= $title;
			$data['alias']	= $alias;
		}

		return parent::save($data);
	}

	/**
	 * We override this method as an EasyTable doesn't currently have a category...
	 *
	 * @param   int     $cat_id  Joomla Category Id.
	 *
	 * @param   string  $alias   Alias of category.
	 *
	 * @param   string  $title   Category label.
	 *
	 * @return  array
	 *
	 * @see JModelAdmin::generateNewTitle()
	 *
	 * @since   1.1
	 */
	protected function generateNewTitle($cat_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Deletes record from specified EasyTable data table.
	 *
	 * @param   string|array  &$pks  Our dot notation table.record value
	 *
	 * @return  bool
	 *
	 * @since   1.1
	 */
	public function delete(&$pks)
	{
		// Check for dot.notation cid's
		$pks = (array) $pks;
		$standardPks = array();

		foreach ($pks as $pk)
		{
			if (strpos($pk, '.'))
			{
				$pkarray = explode('.', $pk);
				$standardPks[] = $pkarray[1];
			}
			else
			{
				$standardPks = $pk;
			}
		}

		return parent::delete($standardPks);
	}

	/**
	 * Populate initial state.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	public function populateState()
	{
		// Initialise variables.
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$trid = ET_General_Helper::getTableRecordID();
		$tid = $trid[0];
		$this->setState('table' . '.id', $tid);
		$pk = $trid[1];

		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}

	/**
	 * Method to set the EasyTable identifier
	 *
	 * @param   int  $id  EasyTable identifier
	 *
	 * @return	void
	 *
	 * @since   1.1
	 */
	public function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}
}
