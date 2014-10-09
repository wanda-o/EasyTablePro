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
		$this->outf('EasyTable Pro! CRON Import Starting');
		$component = JComponentHelper::getComponent('com_easytablepro');

		$params = $component->params;
		$cron_imports_enabled = $params->get('cron_imports_enabled', 0, 'int');
		if ($cron_imports_enabled == 1)
		{
			// Let Joomla know where everything is…
			JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$this->outf('EasyTable Pro! models & tables loaded.');

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
				$this->outf('Usage: tableId and filePath must be specified.');
				$result = 0;
			}

			if ($result)
			{
				$this->outf('Update added %s records.', $result);
				// Clean up the datafile?
				if ($deleteFile)
				{
					unlink($filepath);
				}

			}
			else
			{
				$this->outf('CRON Update DID NOT add any records');
			}

			$this->outf('EasyTable Pro! CRON Import Finished');
		}
		else
		{
			$this->outf('EasyTable Pro! regrets to inform you that CRON Imports are not enabled. Have a nice day!');
		}
	}

    /**
     * Produces formatted output via vsprintf and our line length conformer.
     *
     * @param   string  $msgFormat  The format string.
     * @param   array   $values     The optional array of values to insert into the formatted string.
     *
     * @return  null
     */
    private function outf($msgFormat, $values = array())
    {
        if (empty($values) || (is_array($values) && count($values) == 0))
        {
            $msg = $this->fitTo($msgFormat);
        }
        else
        {
            $msg = vsprintf($msgFormat, $values);
            $msg = $this->fitTo($msg);
        }

        $this->out($msg);
    }

    /**
     * Conforms a message to a fixed length (default 60 for terminal mode) and applies
     * a prefix and suffix string to each line.
     *
     * @param   string  $msg        The message string.
     * @param   int     $lineLength The desired output length
     * @param   string  $prefix     The string prepended to the message, counts towards line length.
     * @param   string  $suffix     The string appended to the message, counts towards line length.
     *
     * @return string
     */
    private function fitTo($msg, $lineLength = 60, $prefix = '** ', $suffix  = ' **')
    {
        $msgLength = $lineLength - strlen($prefix . $suffix);

        if (strlen($msg) <= $msgLength)
        {
            $finalMsg = $this->makeMsgLine($msg, $lineLength, $prefix, $suffix);
        }
        else
        {
            $lineArray = $this->makeSizedLines($msg, $msgLength);
            $lines = count($lineArray);
            $currentLine = 1;
            $finalMsg = '';

            foreach ($lineArray as $line)
            {
                $finalMsg .= $this->makeMsgLine($line, $lineLength, $prefix, $suffix);

                // If we have multiple lines we need to add a \newline
                if ($lines > 1 && $currentLine != $lines) {
                    $finalMsg .= "\n";
                }
            }

        }

        return $finalMsg;
    }

    /**
     * Attempts to make lines of the right length using spaces as word boundaries, if then event a part
     * is longer than the line it revert to splitting it at a fixed length.
     *
     * @param   string  $msg   The message string.
     * @param   int     $size  The line length.
     *
     * @return  array
     */
    private function makeSizedLines($msg, $size=50)
    {
        $parts = explode(' ', $msg);

        $lines = array();
        $currentLine = '';

        foreach ($parts as $part)
        {
            $partsOfPart = str_split($part, $size);

            if (count($partsOfPart) > 1)
            {
                foreach ($partsOfPart as $subPart)
                {
                    $this->processMsgPart($currentLine, $lines, $subPart, $size);
                }
            }
            else
            {
                $subPart = $partsOfPart[0];
                $this->processMsgPart($currentLine, $lines, $subPart, $size);
            }

        }

        if ($currentLine != '')
        {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * Assembles the actual line, only rudamentary checking as it should be all done in prior steps.
     *
     * @param   string  $msg
     * @param   int     $lineLength
     * @param   string  $prefix
     * @param   string  $suffix
     *
     * @return string
     */
    private function makeMsgLine($msg, $allowedMsgLength, $prefix = '** ', $suffix = ' **')
    {
        if (strlen($msg) > $allowedMsgLength)
        {
            return '**** ERROR: Incorrect use of makeMsgLine() $msg longer than $lineLength ****';
        }
        else
        {
            return $prefix . str_pad($msg, $allowedMsgLength, ' ') . $suffix;
        }
    }

    /**
     * @param $currentLine
     * @param $lines
     * @param $subPart
     * @param $size
     */
    private function processMsgPart(&$currentLine, &$lines, $subPart, $size)
    {
        if ($currentLine != '' && strlen($currentLine . ' ' . $subPart) > $size) {
            $lines[] = $currentLine;
            $currentLine = $subPart;
        } else {
            if ($currentLine != '') {
                $currentLine .= ' ' . $subPart;
            } else {
                $currentLine = $subPart;
            }
        }
    }
}

JApplicationCli::getInstance('EasyTablePro_TableUpdate')->execute();
