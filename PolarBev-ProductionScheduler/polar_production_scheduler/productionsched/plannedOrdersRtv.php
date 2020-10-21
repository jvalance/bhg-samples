<?php
require_once 'model/PlannedOrders.php';
require_once 'model/JQGrid_Paginator.php';

// connect to the database server
require_once '../common/autoloader.php';
require_once '../common/DB2_Adapter.php';
// use polarbev\DB2_Adapter as DB2_Adapter;

// $conn = DB2_Adapter::getInstance();

$debug = ($_REQUEST['debug'] == '1');
if ($debug) {
	$logger = new Zend\Log\Logger;
	$writer = new Zend\Log\Writer\Stream('logs/plannedOrdersRtvDebug.log');
	$logger->addWriter($writer);
	$logger->debug("=================================");
}

// calculate the number of rows for the query. We need this for paging the result
$plannedOrders = new PlannedOrders();
$select = new polarbev\DB_Select();
$filters = array();
$filters['facility'] = $_REQUEST['facility'];
$filters['from_date'] = $_REQUEST['from_date'];
$filters['to_date'] = $_REQUEST['to_date'];
$filters['work_ctr'] = $_REQUEST['work_ctr'];
$plannedOrders->getPlannedOrdersSelect($select, $filters);
$select->andWhere("KFP.FTYPE = 'P'");

$searchOn = Strip ( $_REQUEST ['_search'] );
if ($searchOn == 'true') {
	$searchstr = Strip ( $_REQUEST ['filters'] );
	$jsona = json_decode ( $searchstr, true );
	$wh = getStringForGroup ( $jsona );
	$select->andWhere($wh);
}

$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
$sord = $_GET['sord']; // get the direction
if(!$sidx) $sidx =1;
$select->order = " $sidx $sord";

$totalRowCount = $plannedOrders->getRowCount($select);
if ($debug) $logger->debug("totalRowCount = $totalRowCount");

$pager = new JQGrid_Paginator($totalRowCount);

if ($debug) $logger->debug($select->toString());
if ($debug) $logger->debug(var_export($select->parms,true));

// the actual query for the grid data
$plannedOrders->execScrollableListQuery($select);
// pre_dump($select->toString());

// prepare json response data
$response->page = $pager->pageToView;
$response->total = $pager->numberOfPages;
$response->records = $totalRowCount;

// initialize looping variables
$row_count = 0;
// $rowNumber = ($pager->startRow < 1) ? 1 : $pager->startRow;
$rowNumber = $pager->startRow+1;

if ($debug) {
	$logger->debug(var_export($pager, true));
	$logger->debug("Before loop: rowNumber = $rowNumber");
}

while ( $row = db2_fetch_assoc($plannedOrders->stmt, $rowNumber++ )) {
	if (++$row_count > $pager->pageSize) break;
 	if ($debug) {
 		$logger->debug("rowNumber = $rowNumber; row_count = $row_count");
 		$logger->debug(var_export($row, true));
 	}

	$response->rows [] = array(
		'id' => $rowNumber,
		'cell' => array (
			$row['ITEM_TYPE'],
			htmlentities($row['GROUP_TECH']),
			$row['ROUTING_METHOD'],
			$row['PACKAGE'],
			$row['ORDER_TYPE'],
			trim($row['ITEM_NUMBER']),
			trim(htmlentities($row['ITEM_DESC'])),
			formatDate($row['DUE_DATE']),
			(int)$row['PLAN_QTY'],
			$row['HOURS'],
			(int)$row['ON_HAND'],
			(int)$row['AVAIL'],
			formatDate($row['RESCHEDULE'])
		)
	);
}

echo json_encode ( $response );
// http://172.25.0.1:10088/prodsched/plannedOrdersRtv.php?facility=WO&from_date=20121107&to_date=20121119&work_ctr=10&debug=1&_search=false&nd=1352323402635&rows=10&page=1&sidx=ITEM_TYPE&sord=desc

//===================================================================================
//	END OF MAINLINE
//===================================================================================

function getStringForGroup($group) {
	$i_ = '';
	$sopt = array (
			'eq' => "=",
			'ne' => "<>",
			'lt' => "<",
			'le' => "<=",
			'gt' => ">",
			'ge' => ">=",
			'bw' => " {$i_}LIKE ",
			'bn' => " NOT {$i_}LIKE ",
			'in' => ' IN ',
			'ni' => ' NOT IN',
			'ew' => " {$i_}LIKE ",
			'en' => " NOT {$i_}LIKE ",
			'cn' => " {$i_}LIKE ",
			'nc' => " NOT {$i_}LIKE ",
			'nu' => 'IS NULL',
			'nn' => 'IS NOT NULL'
	);

	$fieldNames = array(
			'SEQNO' => 'KFP.FPPRTM',
			'ITEM_TYPE' => 'IIM.IITYP',
			'GROUP_TECH' => 'IIM.IPFDV',
			'ROUTING_METHOD' => 'KFP.FPRTEM',
			'PACKAGE' => 'IIM.IREF04',
			'ITEM_NUMBER' => 'KFP.FPROD',
			'ITEM_DESC' => 'IIM.IDESC',
			'DUE_DATE' => 'KFP.FRDTE',
			'PLAN_QTY' => 'KFP.FQTY',
			'ON_HAND' => 'IWI.WOPB + IWI.WRCT + IWI.WADJ - IWI.WISS',
			'AVAIL' => 'IWI.WOPB + IWI.WRCT + IWI.WADJ - IWI.WISS - IWI.WCUSA',
			'RESCHEDULE' => 'KFP.FRSDT',
			'ORDER_TYPE' => 'KFP.FTYPE',
			'SEQ_NO' => 'KFP.FPCSEQ',
			'HOURS' => 'DEC(ROUND(DEC((KFP.FQTY/FRT.RMAC),9,2) * DEC((LWK.WKDLP/100),7,2),2),7,2)'
	);

	$fieldQuotes = array(
			'SEQNO' => false,
			'ITEM_TYPE' => true,
			'GROUP_TECH' => true,
			'ROUTING_METHOD' => true,
			'PACKAGE' => true,
			'ITEM_NUMBER' => true,
			'ITEM_DESC' => true,
			'DUE_DATE' => false,
			'PLAN_QTY' => false,
			'ON_HAND' => false,
			'AVAIL' => false,
			'RESCHEDULE' => false,
			'ORDER_TYPE' => true,
			'SEQ_NO' => false,
			'HOURS' => false
	);

	$s = "(";
	if (isset ( $group ['groups'] )
	&& is_array ( $group ['groups'] )
	&& count ( $group ['groups'] ) > 0) {
		for($j = 0; $j < count ( $group ['groups'] ); $j ++) {
			if (strlen ( $s ) > 1) {
				$s .= " " . $group ['groupOp'] . " ";
			}
			try {
				$dat = getStringForGroup ( $group ['groups'] [$j] );
				$s .= $dat;
			} catch ( Exception $e ) {
				echo $e->getMessage ();
			}
		}
	}
	if (isset ( $group ['rules'] )
	&& count ( $group ['rules'] ) > 0) {
		try {
			foreach ( $group ['rules'] as $key => $val ) {
				if (strlen ( $s ) > 1) {
					$s .= " " . $group ['groupOp'] . " ";
				}
				$field = $fieldNames[$val['field']];
				$op = $val ['op'];
				$v = $val ['data'];
				if ($op) {
// 					'eq','ne','lt','le','gt','ge'
					switch ($op) {
						case 'bw' :
						case 'bn' :
							$s .= $field . ' ' . $sopt [$op] . "'$v%'";
							break;
						case 'ew' :
						case 'en' :
							$s .= $field . ' ' . $sopt [$op] . "'%$v'";
							break;
						case 'cn' :
						case 'nc' :
							$s .= $field . ' ' . $sopt [$op] . "'%$v%'";
							break;
						case 'in' :
						case 'ni' :
							$s .= $field . ' ' . $sopt [$op] . "( '$v' )";
							break;
						case 'nu' :
						case 'nn' :
							$s .= $field . ' ' . $sopt [$op] . " ";
							break;
						default :
							if ($fieldQuotes[$val['field']]==true) {
								$s .= $field . ' ' . $sopt [$op] . " '$v' ";
							} else {
								$s .= $field . ' ' . $sopt [$op] . " $v ";
							}
							break;
					}
				}
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	$s .= ")";
	if ($s == "()") {
		// return array("",$prm); // ignore groups that don't have rules
		return " 1=1 ";
	} else {
		return $s;
		;
	}
}

function Strip($value) {
	if (get_magic_quotes_gpc () != 0) {
		if (is_array ( $value ))
			if (array_is_associative ( $value )) {
			foreach ( $value as $k => $v )
				$tmp_val [$k] = stripslashes ( $v );
			$value = $tmp_val;
		} else
			for($j = 0; $j < sizeof ( $value ); $j ++)
			$value [$j] = stripslashes ( $value [$j] );
			else
				$value = stripslashes ( $value );
	}
	return $value;
}

function array_is_associative($array) {
	if (is_array ( $array ) && ! empty ( $array )) {
		for($iterator = count ( $array ) - 1; $iterator; $iterator --) {
			if (! array_key_exists ( $iterator, $array )) {
				return true;
			}
		}
		return ! array_key_exists ( 0, $array );
	}
	return false;
}
