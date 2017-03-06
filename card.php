<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/missionorder/class/missionorder.class.php');
dol_include_once('/missionorder/lib/missionorder.lib.php');
if (!empty($conf->valideur->enabled)) dol_include_once('/valideur/class/valideur.class.php');

if(empty($user->rights->missionorder->read)) accessforbidden();

$langs->load('missionorder@missionorder');


$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');
$mode = GETPOST('mode');

if (empty($mode)) $mode = 'view';
if ($action == 'create' || $action == 'edit') $mode = 'edit';

$PDOdb = new TPDOdb;
$missionorder = new TMissionOrder;

if (!empty($id)) $missionorder->load($PDOdb, $id);
elseif (!empty($ref)) $missionorder->loadBy($PDOdb, $ref, 'ref');

$hookmanager->initHooks(array('missionordercard', 'globalcard'));

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $missionorder, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$PDOdb->beginTransaction();
			
			$missionorder->set_values($_REQUEST); // Set standard attributes
			
			$missionorder->date_start = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));
			$missionorder->date_end = dol_mktime(GETPOST('endhour'), GETPOST('endmin'), 0, GETPOST('endmonth'), GETPOST('endday'), GETPOST('endyear'));
			
			// Check parameters
			if (empty($missionorder->date_start) || empty($missionorder->date_end))
			{
				$error++;
				setEventMessages($langs->trans('warning_date_start_end_must_be_fill'), array(), 'warnings');
			}
			elseif ($missionorder->date_start > $missionorder->date_end)
			{
				$error++;
				setEventMessages($langs->trans('warning_date_start_must_be_inferior_as_date_end'), array(), 'warnings');
			}
			
			$TUserIdTmp = GETPOST('TUser', 'array');
			if (empty($TUserIdTmp))
			{
				$error++;
				setEventMessages($langs->trans('warning_no_user_linked'), array(), 'warnings');
			}
			
			if (empty($missionorder->fk_project))
			{
				$error++;
				setEventMessages($langs->trans('warning_no_project_selected'), array(), 'warnings');
			}
			
			if ($error)
			{
				$PDOdb->rollback();
				$mode = 'edit';
				break;
			}
			
			$TUserId = array();
			foreach ($TUserIdTmp as $fk_user) $TUserId[$fk_user] = $fk_user;
			unset($TUserIdTmp);
			$missionorder->setUsers($PDOdb, $TUserId);
			
			$TReasonId = GETPOST('TMissionOrderReason', 'array');
			$missionorder->setReasons($PDOdb, $TReasonId);
			
			$TCarriageId = GETPOST('TMissionOrderCarriage', 'array');
			$missionorder->setCarriages($PDOdb, $TCarriageId);
			
			$missionorder->save($PDOdb, empty($missionorder->ref));
			
			$PDOdb->commit();
			
			header('Location: '.dol_buildpath('/missionorder/card.php', 1).'?id='.$missionorder->getId());
			exit;
			
			break;
		case 'confirm_clone':
			$missionorder->cloneObject($PDOdb);
			
			header('Location: '.dol_buildpath('/missionorder/card.php', 1).'?id='.$missionorder->getId());
			exit;
			break;
		case 'modif':
			if (!empty($user->rights->missionorder->write)) $missionorder->setDraft($PDOdb);
				
			break;
		case 'confirm_validate':
			if (!empty($user->rights->missionorder->write)) $missionorder->setValid($PDOdb, $user);
			
			header('Location: '.dol_buildpath('/missionorder/card.php', 1).'?id='.$missionorder->getId());
			exit;
			break;
		case 'confirm_delete':
			if (!empty($user->rights->missionorder->write)) $missionorder->delete($PDOdb);
			
			header('Location: '.dol_buildpath('/missionorder/list.php', 1));
			exit;
			break;
		case 'confirm_to_approve':
			$missionorder->setToApprove($PDOdb);
			
			header('Location: '.dol_buildpath('/missionorder/card.php', 1).'?id='.$missionorder->getId());
			exit;
			break;
		case 'confirm_approve':
			$missionorder->addApprobation($PDOdb);
			
			header('Location: '.dol_buildpath('/missionorder/card.php', 1).'?id='.$missionorder->getId());
			exit;
			break;
	}
}


/**
 * View
 */
_fiche($PDOdb, $missionorder, $mode, $action);

function _fiche(&$PDOdb, &$missionorder, $mode='view', $action)
{
	global $db,$user,$langs,$conf;
	
	// Force mode 'view' if can't edit object
	if (empty($user->rights->missionorder->write)) $mode = 'view';
	
	$title=$langs->trans("MissionOrder");
	llxHeader('',$title);
	
	if ($mode == 'edit' && $action == 'create')
	{
		print_fiche_titre($langs->trans("NewMissionOrder"));
		dol_fiche_head();
	}
	else
	{
		$head = mission_order_prepare_head($missionorder);
		$picto = 'generic';
		dol_fiche_head($head, 'card', $langs->trans("MissionOrder"), 0, $picto);
	}

	$formcore = new TFormCore;
	$formcore->Set_typeaff($mode);
	
	$form = new Form($db);
	
	$formconfirm = getFormConfirm($PDOdb, $form, $missionorder, $action);
	if (!empty($formconfirm)) echo $formconfirm;
	
	$htmlProject = getProjectView($mode, $missionorder->fk_project);
	$htmlUsers = getUsersView($missionorder->TMissionOrderUser, $form, $mode);
	
	$htmlDateStart = getDateView($form, $missionorder->date_start, $mode, 'start');
	$htmlDateEnd = getDateView($form, $missionorder->date_end, $mode, 'end');
	
	$htmlReason = getReasonOrCarriageView($missionorder, $form, $mode, 'reason');
	$htmlCarriage = getReasonOrCarriageView($missionorder, $form, $mode, 'carriage');

	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;
	
	if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_mission_order');
	
	$TUsersGroup = $missionorder->getUsersGroup(1);
	$is_valideur = !empty($conf->valideur->enabled) ? TRH_valideur_groupe::isValideur($PDOdb, $user->id, $TUsersGroup, false, 'missionOrder') : false;
	
	$linkback = '<a href="'.dol_buildpath('/missionorder/list.php', 1).'">' . $langs->trans("BackToList") . '</a>';
	print $TBS->render('tpl/card.tpl.php'
		,array() // Block
		,array(
			'missionorder'=>$missionorder
			,'view' => array(
				'mode' => $mode
				,'action' => 'save'
				,'can_accept' => !empty($conf->valideur->enabled) ? TRH_valideur_groupe::canBeValidateByThisUser($PDOdb, $user, $missionorder, $TUsersGroup, 'missionOrder', $missionorder->entity) : false // Fait tout les tests pour checker s'il peut valider
				,'can_delete' => in_array($user->id, $TUsersGroup) || $is_valideur
				,'allowed_user' => in_array($user->id, $TUsersGroup) || $is_valideur
				,'urlcard' => dol_buildpath('/missionorder/card.php', 1)
				,'urllist' => dol_buildpath('/missionorder/list.php', 1)
				,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($missionorder->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
				,'showLabel' => $formcore->texte('', 'label', $missionorder->label, 80, 255)
				,'showProject' => $htmlProject
				,'showUsers' => $htmlUsers
				,'showLocation' => $formcore->texte('', 'location', $missionorder->location, 80, 255)
				,'showDateStart' => $htmlDateStart
				,'showDateEnd' => $htmlDateEnd
				,'showReason' => $htmlReason
				,'showCarriage' => $htmlCarriage
				,'showNote' => $formcore->zonetexte('', 'note', $missionorder->note, 80, 8)
				,'showStatus' => $missionorder->getLibStatut(1)
			)
			,'langs' => $langs
			,'form' => $form
			,'formproject' => $formproject
			,'user' => $user
			,'conf' => $conf
			,'TMissionOrder' => array(
				'STATUS_DRAFT' => TMissionOrder::STATUS_DRAFT
				,'STATUS_VALIDATED' => TMissionOrder::STATUS_VALIDATED
				,'STATUS_TO_APPROVE' => TMissionOrder::STATUS_TO_APPROVE
				,'STATUS_REFUSED' => TMissionOrder::STATUS_REFUSED
				,'STATUS_ACCEPTED' => TMissionOrder::STATUS_ACCEPTED
			)
		)
	);
	
	if ($mode == 'edit') echo $formcore->end_form();
	
//	var_dump('sdg', count($missionorder->generic->linkedObjects));
	// Show NDFP link // TODO vérifier si un trigger existe sur le delete d'une NDFP
	$somethingshown = $form->showLinkedObjectBlock($missionorder->generic);
	
//	global $hookmanager;
//	$hookmanager->initHooks(array('commonobject'));
//	$parameters=array();
//	$reshook=$hookmanager->executeHooks('showLinkedObjectBlock',$parameters,$missionorder->generic,$action); 
		
	llxFooter();
}