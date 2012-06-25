<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * CONTENIDO Note Popup
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Includes
 * @version    1.0.0
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   created unknown
 *   modified 2008-06-27, Dominik Ziegler, add security fix
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


$cpage = new cPage();

if ($action == 'note_save_item') {
    $notes = new NoteCollection();

    $note = stripslashes(nl2br($note));

    if ($category != '') {
        $categoryname = $category;
    }

    if ($categoryentry != '') {
        $categoryname = $categoryentry;
    }

    $item = $notes->create($itemtype, $itemid, $lang, $note, $categoryname);
    $item->store();

    $cpage->setContent('<script>window.close();</script>');
} else {
    $list = new NoteView($itemtype, $itemid);
    $list->setWidth('100%');
    $list->setStyleDefinition('margin-bottom', '10px');
    $ui = new UI_Table_Form('note');
    $ui->addHeader(i18n('Add note'));

    $ui->setVar('area', $area);
    $ui->setVar('frame', $frame);
    $ui->setVar('action', 'note_save_item');
    $ui->setVar('itemtype', $itemtype);
    $ui->setVar('itemid', $itemid);

    // Fetch all note categories
    $propColl = new cApiPropertyCollection();
    $notesData = $propColl->getValuesOnlyByTypeName('note', 'category');
    $notesData = array_unique($notesData);

    $categories = array('' => i18n('No category'));

    $oNoteItem = new NoteItem();

    foreach ($notesData as $noteValue) {
        $sValue = $oNoteItem->_outFilter($noteValue);
        $categories[$sValue] = $sValue;
    }

    $cselect = new cHTMLSelectElement('category');
    $cselect->autoFill($categories);

    $centry = new cHTMLTextbox('categoryentry', '', 30);

    $message = new cHTMLTextarea('note');
    $message->setStyle('width: 100%');
    $ui->add(i18n('Note'), $message->render());
    $ui->add(i18n('Category'), array($cselect, $centry));
    $ui->setWidth('100%');

    $cpage->setcontent($list->render().$ui->render());
}

$cpage->render();

?>