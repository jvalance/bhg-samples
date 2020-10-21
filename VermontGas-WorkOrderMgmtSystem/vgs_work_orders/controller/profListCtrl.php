<?php 
require_once '../view/profListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Group_User_Xref.php';
require_once '../model/Auth_Profile_Xref.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SEC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prof = new Sec_Profiles($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();
$authProf = new Auth_Profile_Xref($conn);
$groupUser = new Group_User_Xref($conn);

$prof->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $prof->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$prof->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($prof->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	// Retrieve descriptions for codes
	$row['recStatus'] = $cvm->getCodeValue('RECSTATUS', $row['PRF_PROFILE_STATUS']);
	getProfileRelations ( $authProf, $groupUser, $row );
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Create Profile', 
						'profEditCtrl.php?mode=create',
						VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

/** 
 * This retrieves associations for this profile: ie, Profile Authorities,
 * users for a group profile, and groups for a user profile; These will be
 * displayed on a second line in the html table.   
 * 
 * @param authProf: Instance of Auth_Profile_Xref
 * @param groupUser: Instance of Group_User_Xref
 * @param &row: Current database/table row passed by reference 
 */
function getProfileRelations($authProf, $groupUser, &$row) {
	// Get second-level tables to hide/show for each row
	$row['AUTHORITIES'] = $authProf->getProfileAuthorities($row['PRF_PROFILE_ID']);
	if ('GROUP' == $row['PRF_PROFILE_TYPE']) {
		$row['USERS'] = $groupUser->getUsersForGroup($row['PRF_PROFILE_ID']);
	} else { 
		$row['USERS'] = array();
	}
	if ('USER' == trim($row['PRF_PROFILE_TYPE'])) {
		$row['GROUPS'] = $groupUser->getGroupsForUser($row['PRF_PROFILE_ID']);
//		echo "Profile = {$row['PRF_PROFILE_ID']}:<br>";
		foreach ($row['GROUPS'] as $group) {
			$groupAuths = $authProf->getProfileAuthorities($group);
//			echo "Group = $group";
//			pre_dump($groupAuths);
			$row['AUTHORITIES'] = array_merge($row['AUTHORITIES'], $groupAuths);
		}
	} else { 
		$row['GROUPS'] = array();
	}
	// Set 2nd row flag for display
	$row['2NDROW'] = (count($row['AUTHORITIES']) > 0) 
				|| (count($row['USERS']) > 0)
				|| (count($row['GROUPS']) > 0);
}
