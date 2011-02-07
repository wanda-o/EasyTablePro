<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx ?>" id="etrecord">
	<h2 class="contentheading<?php echo $this->pageclass_sfx ?>"><?php echo $this->pt ?></h2><?php echo $this->et_desc; ?>
	<div id="easytable-record" class="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
		<table  id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" summary="<?php echo htmlspecialchars($this->easytable->description); ?>">
			<thead>
				<tr>
				</tr>
			</thead>
			<tbody>
				<?php
					$this->assign('currentImageDir',$this->imageDir);

					$fieldNumber = 1; // so that we skip the record id from the table record
					$prow = $this->easytables_table_record;
					$row_assoc = $this->et_tr_assoc;
					$row_FNILV = $this->easytables_table_record_FNILV;
					echo '<tr><td class="etr_prevrecord">';
					if($this->prevrecord)
						{ echo '<a href="'.$this->prevrecord.'">'.JText::_('LT__PREVIOUS_RECORD').'</a>'; }
					echo '</td>';
					echo '<td class="etr_nextrecord">';
					if($this->nextrecord)
						{ echo '<a href="'.$this->nextrecord.'">'.JText::_('NEXT_RECORD__GT_').'</a>'; }
					echo '</td></tr>';

					foreach ($this->easytables_table_meta as $field_Meta )
						{// label, fieldalias, type, detail_link, description, id, detail_view, list_view, params
							list($f_label, $f_alias, $f_type, $f_detail_link, $f_description, $f_id, $f_detail_view, $f_list_view, $f_params) = $field_Meta;

							if($f_detail_view) // ie. Detail_view = 1
							{
								$f = $prow[$fieldNumber++];

								$cellType     = (int)$f_type;

								$cellData = ET_VHelper::getFWO($f, $cellType, $f_params, $row_assoc, $row_FNILV); //getFWO($field,$type,$params,$row)

								echo '<tr>';  // Open the row
								$titleString = ''; // Setup the titleString if required
								if(strlen($f_description)){ $titleString = 'title="'.htmlspecialchars($f_description).'" ';}
	
								echo '<td class="sectiontableheader '.$f_alias.'" '.$titleString.'>'.$f_label.'</td>'; // Field Heading
								echo '<td class="sectiontablerow '.$f_alias.'">'.$cellData.'</td>'; // Field Data
								echo '</tr>';  // Close the Row
							}
						}
				?>
			</tbody>
		</table>
	<?php
		if( $this->linked_table && $this->tableHasRecords && $this->show_linked_table)
		{
			echo('<div id="easytable-linkedtable" class="'.htmlspecialchars($this->linked_easytable_alias).'">');
			echo( $this->loadTemplate('linkedtable') );
			echo('</div>');
		}
	?>
	</div>
</div>
