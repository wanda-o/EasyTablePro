<?php defined('_JEXEC') or die ('Restricted Access'); ?>
<div id="etrecord">
	<h2 class="contentheading"><a href="<?php echo $this->backlink; ?>"><?php echo htmlspecialchars($this->easytable->easytablename); ?></a></h2>
	<p class="et_description"><?php echo htmlspecialchars($this->easytable->description); ?></p>
	<br />
	<div id="easytable-record" class="<?php echo htmlspecialchars($this->easytable->easytablealias); ?> ">
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

								$cellData = ET_VHelper::getFWO($f, $cellType, $f_params, $prow); //getFWO($field,$type,$params,$row)

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
		if( $this->linked_table && $this->tableHasRecords )
		{
			echo('<div id="easytable-linkedtable" class="'.htmlspecialchars($this->easytable->easytablealias).'">');
			echo( $this->loadTemplate('linkedtable') );
			echo('</div>');
		}
	?>
	</div>
</div>
