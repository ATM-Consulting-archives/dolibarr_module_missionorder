<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/missionorder.lib.php
 *	\ingroup	missionorder
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function missionorderAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("missionorder@missionorder");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/missionorder/admin/missionorder_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/missionorder/admin/missionorder_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@missionorder:/missionorder/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@missionorder:/missionorder/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'missionorder');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Societe	$object		Object company shown
 * @return 	array				Array of tabs
 */
function mission_order_prepare_head(TMissionOrder $object)
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/missionorder/card.php', 1).'?id='.$object->getId();
    $head[$h][1] = $langs->trans("MissionCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'missionorder');
	
	return $head;
}

function getProjectView($mode='view', $fk_project=0)
{
	global $db,$langs;
	
	if ($mode == 'edit')
	{
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
	
		$formproject = new FormProjets($db);
		ob_start();
		$formproject->select_projects(-1, $fk_project, 'fk_project');
		$htmlProject = ob_get_clean();
		
		return $htmlProject;
	}
	elseif ($fk_project > 0) // mode view mais uniquement si le fetch d'un projet en vos la peine
	{
		require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
		
		$project = new Project($db);
		if ($project->fetch($fk_project) > 0)
		{
			return $project->getNomUrl(1, '', 1);
		}
		else
		{
			setEventMessages($langs->trans('warning_fk_project_fetch_fail', $fk_project), array(), 'warnings');
		}
	}
	
	return '';
}

function getUsersView(&$TMissionOrderUser, &$form, $mode='view')
{
	global $db,$langs;
	
	$res = '';
	
	if ($mode == 'edit')
	{
		$TUser = getAllUserNameById();
		$TSelectedUser = array();
		foreach ($TMissionOrderUser as $missionOrderUser)
		{
			$TSelectedUser[] = $missionOrderUser->fk_user;
		}
		
		$res = $form->multiselectarray('TUser', $TUser, $TSelectedUser, 0, 0, '', 0, '95%', '', '');
	}
	elseif (!empty($TMissionOrderUser))
	{
		foreach ($TMissionOrderUser as $missionOrderUser)
		{
			$u = new User($db);
			if ($u->fetch($missionOrderUser->fk_user) > 0)
			{
				$res .= $u->getNomUrl(1).'&nbsp;';
			}
			else
			{
				setEventMessages($langs->trans('warning_fk_user_fetch_fail', $missionOrderUser->fk_user), array(), 'warnings');
			}
		}
	}
	
	return $res;
}

function getAllUserNameById()
{
	global $db,$langs,$conf,$user;
	
	$TUser = array();
	
	$sql = 'SELECT u.rowid, u.lastname, u.firstname, u.admin, u.entity';
	if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity) $sql.= ', e.label';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'user u';
	if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX .'entity as e ON (e.rowid = u.entity)';
	$sql .= ' WHERE u.statut = 1 AND u.entity IN ('. getEntity('user', 0).')';
	
	$resql = $db->query($sql);
	if ($resql)
	{
		$userstatic = new User($db);
		while ($r = $db->fetch_object($resql))
		{
			$userstatic->id = $r->rowid;
			$userstatic->lastname = $r->lastname;
			$userstatic->firstname = $r->firstname;
			
			$TUser[$userstatic->id] = $userstatic->getFullName($langs, 0, 0, 0);
			if (! empty($conf->multicompany->enabled) && empty($conf->multicompany->transverse_mode) && $conf->entity == 1 && $user->admin && ! $user->entity)
			{
				if ($r->admin && ! $r->entity) $TUser[$userstatic->id] .= '&nbsp;('.$langs->trans("AllEntities").')';
				else $TUser[$userstatic->id] .=  '&nbsp;('.($r->label?$r->label:$langs->trans("EntityNameNotDefined")).')';
			}
		}
	}
	
	return $TUser;
}

function getDateView(&$form, $timestamp, $mode='view', $prefix='re')
{
	if ($mode == 'edit')
	{
		ob_start();
		$form->select_date($timestamp, $prefix, 1, 1, 0, '', 1, 1);
		$htmlDate = ob_get_clean();
	}
	else
	{
		$htmlDate = dol_print_date($timestamp, 'dayhour');
	}
	
	return $htmlDate;
}

function getReasonOrCarriageView(&$missionorder, &$form, $mode='view', $type='reason')
{
	global $langs;
	
	$res = '';
	
	$TChoice = getReasonOrCarraigeFromDict($missionorder, $type, $mode);
	
	if ($type == 'reason')
	{
		$TChildren = 'TMissionOrderReason';
		$fk_dictionary = 'fk_c_mission_order_reason';
		$other_attr = 'other_reason';
	}
	else
	{
		$TChildren = 'TMissionOrderCarriage';
		$fk_dictionary = 'fk_c_mission_order_carriage';
		$other_attr = 'other_carriage';
	}
	
	if ($mode == 'edit')
	{
		$TChildrenByIdDict = array();
		foreach ($missionorder->{$TChildren} as &$child) $TChildrenByIdDict[$child->{$fk_dictionary}] = $child;
		// Show multi checkbox + input text "autre"
		$i=0;
		foreach ($TChoice as &$choice)
		{
			$res .= '<span style="display:inline-block;" class="block minwidth200"><input type="checkbox" name="'.$TChildren.'['.$choice->rowid.']" value="'.$choice->rowid.'" '.(!empty($TReasonId[$choice->rowid]) ? 'checked="checked"' : '').' />&nbsp;'.$choice->label.'</span>';
			if ($i > 0 && $i&1) $res .= '<br />';
			$i++;
		}
		
		if (!empty($res)) $res .= '<br /><br />';
		$res .= $langs->trans('Other').'&nbsp;<input size="80" maxlength="255" type="text" name="'.$other_attr.'" value="'.$missionorder->{$other_attr}.'" />';
	}
	else
	{
		// Affichage comme les tags d'un client
		$res = '<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">';
		foreach ($missionorder->{$TChildren} as &$child)
		{
			if (!empty($TChoice[$child->{$fk_dictionary}]))
			{
				$res .= '<li class="select2-search-choice-dolibarr">'.$TChoice[$child->{$fk_dictionary}]->label.'</li>';
			}
		}
		
		if (!empty($missionorder->{$other_attr})) $res .= '<li class="select2-search-choice-dolibarr">'.$langs->trans('Other').'&nbsp;'.$missionorder->{$other_attr}.'</li>';
		
		$res .= '</ul></div>';
	}
	
	return $res;
}

function getReasonOrCarraigeFromDict(&$missionorder, $type, $mode)
{
	global $db,$conf;
	
	$res = array();
	
	if ($type == 'reason') $table = 'c_mission_order_reason';
	else $table = 'c_mission_order_carriage';
	
	$where = ' WHERE entity = '.($missionorder->entity ? $missionorder->entity : $conf->entity);
	if ($mode == 'edit') $where .= ' AND active = 1'; // Si on est en mode edition, alors on affiche que les valeurs actives
	
	$resql = $db->query('SELECT rowid, label, code, active, entity FROM '.MAIN_DB_PREFIX.$table.$where);
	if ($resql)
	{
		while ($r = $db->fetch_object($resql))
		{
			$res[$r->rowid] = $r;
		}
	}
	
	return $res;
}