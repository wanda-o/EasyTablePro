<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx ?>" id="etrecord">
	<h2 class="contentheading"><?php echo $this->pt ?></h2><?php echo $this->easytable->description; ?>
	<div id="easytable-record" class="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
		<table  id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" summary="<?php echo htmlspecialchars(strip_tags($this->easytable->description)); ?>">
			<tbody>
				<?php
					$currentImageDir = $this->imageDir;

					$prow = $this->item->record;

					if ($this->show_next_prev_record_links) 
					{
						echo '<tr><td class="etr_prevrecord">';
						if ($this->prevrecord)
						{
							echo '<a href="'.$this->prevrecord.'">'.JText::_('COM_EASYTABLEPRO_SITE_PREV_RECORD_LINK').'</a>';
						}
						echo '</td>';
						echo '<td class="etr_nextrecord">';
						if ($this->nextrecord)
							{ echo '<a href="'.$this->nextrecord.'">'.JText::_('COM_EASYTABLEPRO_SITE_NEXT_RECORD_LINK').'</a>'; }
						echo '</td></tr>';
					}

					foreach ($this->easytable->table_meta as $field_Meta )
						{
							 // ie. Detail_view = 1
							if ($field_Meta['detail_view'])
							{
								$fieldalias = $field_Meta['fieldalias'];
								$f = $prow->$fieldalias;

								$cellType     = (int)$field_Meta['type'];

								$cellData = ET_VHelper::getFWO($f, $cellType, $field_Meta['params'], $prow, $currentImageDir);

								// Open the row
								echo '<tr>';
								// Setup the titleString if required
								$titleString = '';
								if (strlen($field_Meta['description']))
								{
									$titleString = 'title="'.htmlspecialchars($field_Meta['description']).'" ';
								}
	
								// Field Heading
								echo '<th class="sectiontableheader '.$field_Meta['fieldalias'].'" '.$titleString.'>'.$field_Meta['label'].'</th>';
								// Field Data
								echo '<td class="sectiontablerow '.$field_Meta['fieldalias'].'">'.$cellData.'</td>';
								// Close the Row
								echo '</tr>';
							}
						}
				?>
			</tbody>
		</table>
	<?php
		if ($this->show_linked_table)
		{
			echo('<div id="easytable-linkedtable" class="'.htmlspecialchars($this->linked_table->easytablealias).'">');
			echo( $this->loadTemplate('linkedtable') );
			echo('</div>');
		}
	?>
	</div>
</div>
