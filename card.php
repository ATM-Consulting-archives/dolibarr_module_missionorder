<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/missionorder/class/missionorder.class.php');
dol_include_once('/missionorder/lib/missionorder.lib.php');

if(empty($user->rights->missionorder->read)) accessforbidden();

$langs->load('missionorder@missionorder');


$action = GETPOST('action');
$id = GETPOST('id', 'int');
$mode = GETPOST('mode');
if (empty($mode)) $mode = 'view';

$PDOdb = new TPDOdb;
$object = new TMissionOrder;

if (!empty($id)) $object->load($PDOdb, $id);

$hookmanager->initHooks(array('missionordercard', 'globalcard'));

$parameters = array('id' => $id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');


// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	switch ($action) {
		case 'save':
			
			break;
		
		case 'delete':
			
			break;
		case 'confirm_delete':
			
			
			header('Location: '.dol_buildpath('/missionorder/list.php', 1));
			exit;
			break;
	}
}


/**
 * View
 */
_fiche($PDOdb, $object, $mode, $action);

function _fiche(&$PDOdb, &$missionorder, $mode='view', $action)
{
	global $user;
	
	// Force mode 'view' if can't edit object
	if (empty($user->rights->missionorder->write)) $mode = 'view';
	
	$title=$langs->trans("MissionOrder");
	llxHeader('',$title);
	
	$head = mission_order_prepare_head($object);

	$picto = 'generic';
	dol_fiche_head($head, 'card', $langs->trans("MissionOrder"), 0, $picto);

	// TODO print table	
	$formcore = new TFormCore;
	$formcore->Set_typeaff($mode);
	
	$form = new Form($db);
	$formproject = new FormProjets($db);
	
	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;
	
	if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_mission_order');
	
	$TUser = array();
	$TSelectedUser = array();
	
	print $TBS->render('tpl/card.tpl.php'
		,array() // Block
		,array(
			'missionorder'=>$missionorder
			,'view' => array(
				'mode' => $mode
				,'action' => $action
				,'multiselectUser' => $form->multiselectarray('TUser', $TUser, $TSelectedUser, 0, 0, $morecss, 0, '95%', '', '')
			)
			,'langs' => $langs
			,'form' => $form
			,'formproject' => $formproject
		)
	);
	
	if ($mode == 'edit') echo $formcore->end_form();	
	
	llxFooter();
}