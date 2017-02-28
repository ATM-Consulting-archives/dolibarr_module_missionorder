<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}

dol_include_once('/missionorder/class/missionorder.class.php');

$PDOdb=new TPDOdb;

$o=new TMissionOrder;
$o->init_db_by_vars($PDOdb);

$o=new TMissionOrderUser;
$o->init_db_by_vars($PDOdb);

$o=new TMissionOrderReason;
$o->init_db_by_vars($PDOdb);

$o=new TMissionOrderCarriage;
$o->init_db_by_vars($PDOdb);
