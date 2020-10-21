<?php
require_once '../model/VGS_DB_Table.php';

function addFieldGroupEntry($fieldName, &$rowArray, &$text) {
	$field = array(
		'name' => $fieldName,
		'value' => $rowArray[$fieldName],
		'label' => $text[$fieldName],
		'class' => ''
	);
	return $field;
}

function getMetaData($conn, $table, $schema = "WORKORD") {
	$syscols = new VGS_DB_Table($conn);
	$query = "select * from qsys2/syscolumns where table_schema = '$schema' and table_name = '$table' ";
//	pre_dump($query);
	$rs = $syscols->execListQuery($query);
	
	$meta = array();
	while ($rowMeta = db2_fetch_assoc($syscols->stmt)) {
//		pre_dump($rowMeta);
    	$meta[] = $rowMeta; 
	} 
   	
	return $meta;
}

function getColumnText($conn, $table, $schema = "WORKORD") {
	$coltext = array();
	$syscols = new VGS_DB_Table($conn);
	$query = "select * from qsys2/syscolumns where table_schema = '$schema' and table_name = '$table' ";
	$rs = $syscols->execListQuery($query);
	while ($rowC = db2_fetch_assoc($syscols->stmt)) {
    	$coltext[$rowC["COLUMN_NAME"]] = 
    		str_replace('Work Order', 'W/O', $rowC["COLUMN_TEXT"]);
	} 
   	return $coltext;
}

function createFieldGroup ($caption, $fieldNames, $row, $columnText) {
	$fieldGroup = array();
	if (isset($caption)) {
		$fieldGroup['caption'] = $caption; 
	}
	foreach ($fieldNames as $fieldName) {
		$fieldGroup['fields'][] = addFieldGroupEntry($fieldName, $row, $columnText);
	}
	return $fieldGroup;
}

function renderFieldGroups ($columns) {
?>	
<table class="inquiry" width="100%">
	<tr>
	<?php 
	$colCount = count($columns) <= 0? 1 : count($columns);
	$colWidth = floor( 100 / $colCount);
	foreach ($columns as $column => $field_groups) :
	?>
	<td id="column_<?=$column?>" class="fg_column" width="<?= $colWidth?>%" valign="top">
		<?php 
		foreach ($field_groups as $field_group => $fields) :
		//	echo "fields array:"; pre_dump($fields);
		?>
		<table class="field_group" width="100%">
			<caption><?= $fields['caption']; ?></caption>
			<?php 
			foreach ($fields['fields'] as $field) :
			// echo "field list array:"; pre_dump($field);
			?>
			<tr>
			<td class="field_label" id="<?=$field['name']?>_label" width="50%">
				<?= $field['label']?>
			</td>
			<td class="field_value" id="<?=$field['name']?>_value" width="50%">
				<?= $field['value']?> &nbsp;
			</td>
			</tr>
			<?php 
			endforeach;
			?>
		</table>
		<?php 
		endforeach;
		?>
	</td>
	<?php 
	endforeach;
	?>
	</tr>
</table>
<?php 	
}
