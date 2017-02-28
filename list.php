<?php

require 'config.php';
dol_include_once('/missionorder/class/missionorder.class.php');

if(empty($user->rights->missionorder->read)) accessforbidden();

$langs->load('missionorder@missionorder');


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
	global $db,$langs,$user,$conf;
	
	llxHeader('',$langs->trans('listMissionOrder'),'','');
	
	// TODO ajouter les colonnes manquantes ET une colonne action pour la notion de validation rapide
	$sql = 'SELECT mo.rowid, mo.ref, mo.label, mo.location, mo.fk_project, mo.date_start, mo.date_end, mo.date_refuse, mo.date_accept, mo.status
			FROM '.MAIN_DB_PREFIX.'mission_order mo
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
		,'search' => array(
			'ref' => array('recherche' => true)
			,'status' => array(
				$missionorder::STATUS_DRAFT => $langs->trans('Draft')
				,$missionorder::STATUS_VALIDATED => $langs->trans('Validate')
				,$missionorder::STATUS_REFUSED => $langs->trans('Refuse')
				,$missionorder::STATUS_ACCEPTED => $langs->trans('Accept')
			)
		)
		,'translate' => array()
		,'hide' => array()
		,'liste' => array(
			'titre' => $langs->trans('ListMissionOrder')
			,'image' => img_picto('','title.png', '', 0)
			,'picto_precedent' => img_picto('','back.png', '', 0)
			,'picto_suivant' => img_picto('','next.png', '', 0)
			,'noheader' => 0
			,'messageNothing' => $langs->trans('NoMissionOrder')
			,'picto_search' => img_picto('','search.png', '', 0)
		)
		,'title'=>array(
			'ref' => $langs->trans('Ref')
		)
		,'eval'=>array(
			'ref'=>'TMissionOrder::getNomUrl(@rowid@, 1)'
		)
	));
	
	llxFooter('');
}