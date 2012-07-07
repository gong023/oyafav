<?php
/**
 *  {$action_name}.php
 *
 *  @author     {$author}
 *  @package    Oyafav
 *  @version    $Id$
 */
chdir(dirname(__FILE__));
require_once '{$dir_app}/Oyafav_Controller.php';

ini_set('max_execution_time', 0);

Oyafav_Controller::main_CLI('Oyafav_Controller', '{$action_name}');
?>
