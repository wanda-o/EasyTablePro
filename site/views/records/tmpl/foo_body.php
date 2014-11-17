<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');
?>
<tbody>
<?php
$this->assign('currentImageDir', $this->imageDir);
$alt_rv = 0;
$rowNumber = 0;
$leaf = $this->title_leaf;

// Looping through the rows of paginated data
foreach ($this->items as $rowIndex => $prow)
{
	if (is_object($prow))
	{
		// Open the row
		if ($this->pagination && ($this->pagination->total == $prow->id))
		{
			echo "<tr class='row$alt_rv et_last_row' id='row-$prow->id'>";
		}
		else
		{
			echo '<tr class=\'row' . $alt_rv . '\' id=\'row-' . $prow->id . '\'>';
		}

		$columnNumber = 0;

		// Looping through the fields of the row
		foreach ($prow as $k => $f)
		{
			// We skip the row id which is in position 0
			if (!($k == 'id'))
			{
                // Get FooTable Options
                $fooOptions = $this->fooSettings[$this->easytable->table_meta[$k]['id']];

				if (isset($this->easytable->table_meta[$k]))
				{
					$labels			= $this->easytable->table_meta[$k];
				}

				// Is this field shown in the list view?
				if (!$fooOptions->list)
				{
					continue;
				}

				// Make sure cellData is empty before we start this cell.
				$cellData		= '';
				$cellClass		= $labels['fieldalias'];
				$cellType		= (int) $labels['type'];
				$cellDetailLink = (int) $labels['detail_link'];
				$cellOptions	= $labels['params'];

				// We increment labelnumber for next pass.
				$columnNumber++;
				$cellData		= ET_VHelper::getFWO($f, $cellType, $cellOptions, $prow, $this->currentImageDir);

				// As a precaution we make sure the detail link cell is not a URL field
				if ($cellDetailLink && ($cellType != 2))
				{
					$linkToDetail = 'index.php?option=com_easytablepro&view=record&id=' . $this->easytable->id . '&rid=' . $rowId;

					// There is a define label field a the URL leaf.
					$linkToDetail .= $leaf ? '&rllabel=' . JFilterOutput::stringURLSafe(substr($prow->$leaf, 0, 100)) : '';
					$linkToDetail = JRoute::_($linkToDetail);
					$cellData = '<a href="' . $linkToDetail . '">' . $cellData . '</a>';
					$cellDetailLink = '';
				}

				// Finally we can echo the cell string.
				echo "<td class='colfld " . $cellClass . "'>" . trim($cellData) . '</td>';
			}
			else // We store the rowID for possible use in a detaillink
			{
				$rowId = $f;
			}

			// End of row stuff should follow after this.
			unset($f);
		}

		// Close the Row
		echo '</tr>';
		$alt_rv = (int) !$alt_rv;
		$k = '';

		// Clear the rowId to prevent any issues.
		$rowId = '';
		unset($prow);
	}
}	// End of foreach for rows
?>
</tbody>
