<?php

require ('../config.php');
dol_include_once('/missionorder/lib/missionorder.lib.php');
dol_include_once('/projet/class/project.class.php');
dol_include_once('/missionorder/lib/missionorder.lib.php');


$get = GETPOST('get','alpha');
$put = GETPOST('put','alpha');

_put($db, $put);
_get($db, $get);

function _get(&$db, $case) {

	switch ($case) {
		case 'project':
			$TUserId = GETPOST('TUserId');
			if(!empty($TUserId)){
				foreach($TUserId as &$userId){
					$obj = new stdClass();
					$obj->id = $userId;
					$userId=$obj;
				}
			}
			echo json_encode(getProjectView('edit',0,$TUserId));
			break;
		case 'usergroup':
			$TUserId = GETPOST('TUserId');
			if(!empty($TUserId)){
				foreach($TUserId as &$userId){
					$obj = new stdClass();
					$obj->id = $userId;
					$userId=$obj;
				}
			}
			echo json_encode(getUsergroupView('edit',0,$TUserId));
			break;
		
	}

}

function _put(&$db, $case) {
	switch ($case) {
        
	}

}
