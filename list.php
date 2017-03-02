<?php

require 'config.php';
dol_include_once('/missionorder/class/missionorder.class.php');

if(empty($user->rights->missionorder->read)) accessforbidden();

$langs->load('missionorder@missionorder');
$langs->load('abricot@abricot');

$hookmanager->initHooks(array('missionorderlist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');


/*
 * View
 */

_list();

function _list()
{
	global $db,$langs,$user,$conf,$hookmanager;
	
	llxHeader('',$langs->trans('listMissionOrder'),'','');
	
	// TODO ajouter les colonnes manquantes ET une colonne action pour la notion de validation rapide
	$sql = 'SELECT mo.rowid, mo.ref, mo.label, mo.location, mo.fk_project, mo.date_start, mo.date_end, mo.date_refuse, mo.date_accept
					, mo.status, GROUP_CONCAT(mou.fk_user SEPARATOR \',\') as TUserId';
	
	// TODO il faut aussi check si l'user à le droit de d'accepter des OM
	if (!empty($conf->ndfp->enabled)) $sql .= ', \'\' as action';
	
	$sql.= '
			FROM '.MAIN_DB_PREFIX.'mission_order mo
			LEFT JOIN '.MAIN_DB_PREFIX.'projet p ON (p.rowid = mo.fk_project)
			LEFT JOIN '.MAIN_DB_PREFIX.'mission_order_user mou ON (mou.fk_mission_order = mo.rowid)
			WHERE mo.entity IN ('.getEntity('TMissionOrder', 1).')
			GROUP BY mo.rowid
	';
	
	$missionorder = new TMissionOrder;
	
	$PDOdb = new TPDOdb;
	$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_mission_order', 'GET');
	
	$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;
	
	$r = new TSSRenderControler($missionorder);
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine' => $nbLine
		)
		,'subQuery' => array()
		,'link' => array()
		,'type' => array(
			'date_start' => 'date'
			,'date_end' => 'date'
			,'date_refuse' => 'date'
			,'date_accept' => 'date'
		)
		,'search' => array(
			'ref' => array('recherche' => true)
			,'label' => array('recherche' => true)
			,'location' => array('recherche' => true)
			,'fk_project' => array('recherche' => true, 'table' => array('p', 'p'), 'field' => array('ref','title'))
			,'date_start' => array('recherche' => 'calendars', 'allow_is_null' => true)
			,'date_end' => array('recherche' => 'calendars', 'allow_is_null' => true)
			,'date_refuse' => array('recherche' => 'calendars', 'allow_is_null' => true)
			,'date_accept' => array('recherche' => 'calendars', 'allow_is_null' => true)
			,'status' => array('recherche' => TMissionOrder::$TStatus, 'to_translate' => true)
		)
		,'translate' => array()
		,'hide' => array(
			'rowid'
		)
		,'liste' => array(
			'titre' => $langs->trans('ListMissionOrder')
			,'image' => img_picto('','title_generic.png', '', 0)
			,'picto_precedent' => '<'
			,'picto_suivant' => '>'
			,'noheader' => 0
			,'messageNothing' => $langs->trans('NoMissionOrder')
			,'picto_search' => img_picto('','search.png', '', 0)
		)
		,'title'=>array(
			'ref' => $langs->trans('Ref')
			,'label' => $langs->trans('Label')
			,'location' => $langs->trans('Location')
			,'fk_project' => $langs->trans('Project')
			,'date_start' => $langs->trans('DateStart')
			,'date_end' => $langs->trans('DateEnd')
			,'date_refuse' => $langs->trans('DateRefused')
			,'date_accept' => $langs->trans('DateAccepted')
			,'status' => $langs->trans('Status')
			,'TUserId' => $langs->trans('UsersLinked')
		)
		,'eval'=>array(
			'ref' => 'TMissionOrder::getStaticNomUrl(@rowid@, 1)'
			,'fk_project' => '_getProjectNomUrl(@val@)'
			,'date_start' => '_formatDate("@val@")'
			,'date_end' => '_formatDate("@val@")'
			,'date_refuse' => '_formatDate("@val@")'
			,'date_accept' => '_formatDate("@val@")'
			,'status' => 'TMissionOrder::LibStatut(@val@, 4)'
			,'TUserId' => '_getUsersLink("@val@")'
		)
	));
	
	$parameters=array('sql'=>$sql);
	$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $missionorder);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	
	llxFooter('');
}

function _getProjectNomUrl($fk_project)
{
	global $db;
	
	$project = new Project($db);
	$project->fetch($fk_project);
	
	return $project->getNomUrl(1, '', 1);
}

function _formatDate($date)
{
	global $db;
	
	if (empty($date)) return '';
	
	// Besoin du $db->jdate pour éviter un décalage de 1 heure
	return dol_print_date($db->jdate($date), 'dayhour');
}

function _getUsersLink($fk_user_string)
{
	global $db,$TUserLink;
	
	if (empty($TUserLink)) $TUserLink = array();
	$Tab = explode(',', $fk_user_string);
	
	$res = '';
	foreach ($Tab as $fk_user)
	{
		if (!empty($TUserLink[$fk_user])) $u = &$TUserLink[$fk_user];
		else
		{
			$u = new User($db);
			$u->fetch($fk_user);
			$TUserLink[$fk_user] = $u;
		}
		
		if ($u->id > 0) $res .= $u->getNomUrl(1, '', 0, 0, 24, 1).'&nbsp;';
		else $res .= '[IdNotFound:'.$fk_user.']';
	}
	
	return $res;
}