<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Frontend permissions class
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Classes
 * @version    1.6
 * @author     unknowm
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 * @deprecated [2011-10-06] Use new classes in contenido/classes/contenido/class.frontend.permission.php
 *                          - Use cApiFrontendPermissionCollection instead of FrontendPermissionCollection
 *                          - Use cApiFrontendPermission instead of FrontendPermission
 *
 * {@internal
 *   created  unknown
 *   modified 2008-06-30, Frederic Schneider, add security fix
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *   modified 2011-10-06, Murat Purc, removed in favor of normalizing the API
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

?>