<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * Records JSON controller for EasyTable Pro.
 *
 * JSON calls of the form ?option=com_easytablepro&view=records&task=records.fetch&id=1&format=json
 *
 * @package  EasyTablePro
 *
 * @since    1.2
 */
class EasyTableProControllerRecords extends JControllerLegacy
{
	/**
	 * Method to return requested records from a table.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function fetchRecords()
	{
		$params = JComponentHelper::getParams('com_easytablepro');
		$app = JFactory::getApplication();
		$jInput = $app->input;

		// Find our sEcho
		$sEcho = $jInput->getInt('sEcho', 0);

		if ($params->get('enable_ajax_tables', 1))
		{
			// Get the records model.
			$recordModel = $this->getModel('DtRecords', 'EasyTableProModel');

			// Get the table
			/* @var $recordModel EasyTableProModelRecords */
			$table = $recordModel->getEasyTable();

			if (!$table || empty($table))
			{
				$recordMeta = array();
			}
			else // We have a table so it's probably going to have meta, records etc
			{
				$recordMeta = $table->table_meta;
				$title_leaf = $params->get('title_field', '');
				$table->title_leaf = $title_leaf;

				// Get the raw records
				$rawRecords = $recordModel->getItems();
			}
		}
		else
		{
			return false;
		}

		/* $return should now have an array of ojbects, i.e. "rows" of data that will eventually get coverted to JSON
		 * e.g {"id":"1","presidency":"1","president":"George Washington","party":"Independent <em>(Wow! Really?)<\/em>",
		 * "thumbnail":"thmb_GeorgeWashington.jpg","home-state":"Virginia","wikipedia-entry":"http:\/\/en.wikipedia.org\/wiki\/George_Washington",
		 * "portrait-cf":"GeorgeWashington.jpg","took-office":"1789-04-30","left-office":"1797-03-04","et-rank":"1"}
		 */

		// Check the data.
		if (empty($rawRecords))
		{
			$processedRecords = array();
		}
		else
		{
			// Process each record for presentation, init variables.
			$rowId = '';
			$processedRecords = array();

			foreach ($rawRecords as $rawRecord)
			{
				// Create a new record object for the processed results
				$processedRecord = array();

				// Process each field
				foreach ($rawRecord as $k => $f)
				{
					// We skip the row id which is in position 0
					if (!($k == 'id'))
					{
						if (!($k == 'et-rank'))
						{
							// Get our field meta
							$fieldMeta = $recordMeta[$k];

							// Now we can check if this field is shown in the list view
							if (!$fieldMeta['list_view'])
							{
								continue;
							}

							// Make sure cellData is empty before we start this cell.
							$cellType		= (int) $fieldMeta['type'];
							$cellDetailLink = (int) $fieldMeta['detail_link'];
							$cellOptions	= $fieldMeta['params'];

							$cellData		= ET_VHelper::getFWO($f, $cellType, $cellOptions, $rawRecord, $table->defaultimagedir);

							// As a precaution we make sure the detail link cell is not a URL field
							if ($cellDetailLink && ($cellType != 2))
							{
								$linkToDetail = 'index.php?option=com_easytablepro&view=record&id=' . $table->id . '&rid=' . $rowId;

								// There is a defined label field a the URL leaf.
								$leaf = $table->title_leaf;
								$linkToDetail .= $leaf ? '&rllabel=' . JFilterOutput::stringURLSafe(substr($rawRecord->$leaf, 0, 100)) : '';
								$linkToDetail = JRoute::_($linkToDetail);
								$cellData = '<a href="' . $linkToDetail . '">' . $cellData . '</a>';
							}
						}
						else // The et-rank doesn't require processing...
						{
							$cellData = $f;
						}
					}
					else // We store the rowID for possible use in a detaillink (and for our processed record).
					{
						$rowId = $f;
						$cellData = $f;
					}

					// Ok, the field has been processed so we can store it in it's record array
					$processedRecord[$k] = $cellData;
				}

				// Records processed lets add it to our array
				$processedRecords[] = array_values($processedRecord);
			}
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		// Debug: echo json_encode($recordMeta);
		$dtjson = array(
			'sEcho' => $sEcho,
			'iTotalDisplayRecords' => $recordModel->getTotal(),
			'iTotalRecords' => $recordModel->getTotalRecords(),
			'aaData' => $processedRecords
		);
		echo json_encode($dtjson);
		JFactory::getApplication()->close();
	}

	/**
	 * fetchTable()
	 *
	 * @return null
	 */
	public function fetchTable()
	{
		$params = JComponentHelper::getParams('com_easytablepro');
		$table = array();

		if ($params->get('enable_ajax_tables', 1))
		{
			// Get the records model.
			$recordModel = $this->getModel('Records', 'EasyTableProModel');

			// Get the table
			/* @var $recordModel EasyTableProModelRecords */
			$table = $recordModel->getEasyTable();
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($table);
		JFactory::getApplication()->close();
	}

	/**
	 * getModel()
	 *
	 * @param   string  $name    Model name.
	 *
	 * @param   string  $prefix  Component class name.
	 *
	 * @param   array   $config  Model name.
	 *
	 * @return JModel
	 */
	public function getModel($name='DtRecords', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
