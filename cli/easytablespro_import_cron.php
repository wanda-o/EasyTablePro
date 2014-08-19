<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/easytablespro_import_cron.php --tableId=1 --filepath=/path/to/file.csv
 * Optional parameters are 'fileHasHeaders', 'append' and 'deleteFile'.
 *
 * If 'fileHasHeaders=0' the first line of the file is treated as a record, otherwise,
 * it defaults to 1 and discards the first line (as headings aren't needed).
 *
 * If 'append=1' the contents of the file will be appended to the table, otherwise,
 * it defaults to 0 and replaces the contents of the table with the contents of file.
 *
 * If 'deleteFile=0' the update file will NOT be deleted after processing, otherwise,
 * it defaults to 1 and after a 'Successful' run (i.e. 1 or more records added to table)
 * the datafile will be deleted (unlinked).
 *
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Set the root path to EasyTablePro
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_easytablepro');

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @package  Joomla.Cli
 * @since    2.5
 */
class EasyTablePro_TableUpdate extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   1.4
	 */
	public function doExecute()
	{
		// Load language files
		$lang = JFactory::getLanguage();
		$lang->load('com_easytablepro', JPATH_ADMINISTRATOR);
		$lang->load('com_easytablepro', JPATH_COMPONENT_ADMINISTRATOR);

		// Get the settings etc
		$this->out('---- EasyTable Pro! CRON Import Starting ----');
		$component = JComponentHelper::getComponent('com_easytablepro');

		$params = $component->params;
		$cron_imports_enabled = $params->get('cron_imports_enabled', 0, 'int');
		if ($cron_imports_enabled == 1)
		{
			// Let Joomla know where everything is…
			JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$this->out('EasyTable Pro! models & tables loaded.');

			// Get our DB
			$db = JFactory::getDbo();
			// Load our upload model
			/* @var EasyTableProModelUpload  $uploadModel */
			$uploadModel = JModelLegacy::getInstance('Upload', 'EasyTableProModel');

			// Get the parameters of this run
			$chunkSize = $params->get('chunkSize', 50);
			$tableId = $this->input->getInt('tableId', 0);
			$filepath = $this->input->getString('filePath', '');
			$fileHasHeaders = $this->input->getInt('fileHasHeaders', 1);
			$append = $this->input->getInt('append', 0);
			$deleteFile = $this->input->getInt('deleteFile', 1);

			if ($tableId && $filepath)
			{
				// and inject them into our model…
				$result = $uploadModel->cronUpdate($tableId,
					$filepath,
					$fileHasHeaders,
					$chunkSize,
					$append,
					$db,
					$this);
			}
			else
			{
				$this->out('---- Usage: tableId and filePath must be specified. ----');
				$result = 0;
			}

			if ($result)
			{
				$this->out('---- Update added '. $result . ' records. ----');
				// Clean up the datafile?
				if ($deleteFile)
				{
					unlink($filepath);
				}

			}
			else
			{
				$this->out('---- CRON Update DID NOT add any records ----');
			}

			$this->out('---- EasyTable Pro! CRON Import Finished ----');
		}
		else
		{
			$this->out('EasyTable Pro! regrets to inform you that CRON Imports are not enabled. Have a nice day!');
		}
	}
}

JApplicationCli::getInstance('EasyTablePro_TableUpdate')->execute();
