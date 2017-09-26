<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('/quality/class/doctrace.class.php');
dol_include_once('/quality/lib/quality.lib.php');

if(empty($user->rights->quality->all->read)) accessforbidden();

$langs->load('quality@quality');
$langs->load("products");

$action = 		GETPOST('action');
$idprod = 		GETPOST('idprod', 'int');
$id =                   GETPOST('id');
$com =                  GETPOST('author');
$date = 		GETPOST('bitrate');

$pageBaseUrl =          dol_buildpath('/quality/doctrace.php', 1).'?idprod='. $idprod; 
//$doc =                  ?;

$mode = 'view';
//if (empty($user->rights->playlistabricot->all->write)) 	$mode = 'view'; // Force 'view' mode if can't edit object
//else if ($action == 'create' || $action == 'edit') 	$mode = 'edit';

$PDOdb = new TPDOdb;
$object = new TDoctrace;
$productstatic = new Product($db);

if (!empty($id)) $object->load($PDOdb, $id);

//$hookmanager->initHooks(array('playlistabricotcard', 'globalcard'));

/*
 * Actions
 */
$parameters = array('id' => $id, 'title' => $title, 'author' => $author, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$object->set_values($_REQUEST); // Set standard attributes
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			$object->save($PDOdb);
			header('Location: '.dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$idprod.'&action=showSpecDoctrace');
			exit;
			
			break;
                        
		case 'delete':
			$object->delete($PDOdb);
                        header('Location: '.dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$idprod.'&action=showSpecDoctrace');
			exit;
			
			break;
                    
		case 'showSpecDoctrace':
			$html = _liste($PDOdb, $idprod);			
			break;
                    
		case 'showAllDocTrace':
			$html = _listeAll($PDOdb);			
			break;
                    
                case 'add':
                        $html = _createForm($idprod);
                        //$mode = 'edit';
                        break;
                    
                case 'edit':
                        $html = _editForm($object);
                        //$mode = 'edit';
                        break;
                
                default :
                        header('Location: '.$pageBaseUrl.'&action=showSpecDoctrace');
			exit;
	}
}


/**
 * View
 */
$formcore = new TFormCore;
$formFile = new FormFile($db);

$title=$langs->trans("quality");
llxHeader('',$title);

//$formcore = new TFormCore;
//$formcore->Set_typeaff($mode);

//$form = new Form($db);

//?? $formconfirm = getFormConfirm($PDOdb, $form, $object, $action);
//?? (!empty($formconfirm)) echo $formconfirm;

//if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_doctrace');

//?? $linkback = '<a href="'.dol_buildpath('/playlistabricot/list_playlist.php', 1).'">' . $langs->trans("BackToList") . '</a>';

$productstatic->fetch($idprod);
$head = product_prepare_head($productstatic);
dol_fiche_head($head, 'doctrace', $langs->trans("CardProduct".$object->type), 0, $picto);
print $html;

if($action == 'add')
{
    $formFile->form_attach_new_file($pageBaseUrl.'&action=save','Document lié');
    print '<div class="tabsAction">
                <div class="inline-block divButAction"><a href="" class="butAction">Valier</a></div>
                <!--<div class="inline-block divButAction"><a onclick="if (!confirm(\'Sur ?\')) return false;" href="" class="butAction">Cancel</a></div>-->
                <div class="inline-block divButAction"><a href="'.$pageBaseUrl.'" class="butAction">Cancel</a></div>
           </div>';
    print '</tbody>';
    print '</table>';
    print '</div>';
}
if($action == 'edit')
{
    $formFile->form_attach_new_file($pageBaseUrl.'&action=save','Document lié');
    print '<div class="tabsAction">
                <div class="inline-block divButAction"><a href="" class="butAction">Valier</a></div>
                <!--<div class="inline-block divButAction"><a onclick="if (!confirm(\'Sur ?\')) return false;" href="" class="butAction">Cancel</a></div>-->
                <div class="inline-block divButAction"><a href="'.$pageBaseUrl.'" class="butAction">Cancel</a></div>
           </div>';
    print '</tbody>';
    print '</table>';
    print '</div>';
}

		
//if ($mode == 'edit') echo $formcore->end_form();

//if ($mode == 'view' && $object->getId()) $somethingshown = $form->showLinkedObjectBlock($object->generic);

llxFooter();

//liste des doctrace pour la fiche produit
function _liste(&$PDOdb, $id) {
	global $conf, $langs;
	
	$l=new TListviewTBS('quality');
	$sql= "SELECT d.rowid, d.nlot, d.date_cre, d.comment FROM llx_doctrace d WHERE fk_product = ". $id;

	$html = $l->render($PDOdb, $sql, array(
                        'view_type' => 'list' // default = [list], [raw], [chart]
                        ,'view.mode' => 'list' // default = [list], [raw], [chart]
                        ,'limit'=>array(
                                'nbLine' => 25
                        )
			,'link'=>array(
					//'title' => '<a href="'.dol_buildpath('/playlistabricot/card_track.php', 1).'?id=@rowid@">@val@</a>',
					//'author' => '<a href="'.dol_buildpath('/societe/card.php', 1).'?socid=@rowid@">@val@</a>'
			)
                        //,'search' => array(
                        //    'date_cre' => array('recherche' => 'calendars', 'allow_is_null' => false)
                        //    ,'nlot' => array('recherche' => true, 'table' => 'd', 'field' => 'nlot')
                        //)
			,'title'=>array(
					'nlot'=>"Numero de lot",
					'date_cre'=>"Date de création",
					'comment'=>"Commentaire",
                                        'test'=>'test'
			)
			,'hide' => array(
					'rowid'
			)
			,'liste'=>array(
					'titre'=>'Liste des documents de tracabilité'
					//,'image'=>img_picto('','title.png', '', 0)
					//,'picto_precedent'=>img_picto('','back.png', '', 0)
					//,'picto_suivant'=>img_picto('','next.png', '', 0)
					//,'noheader'=> (int)isset($_REQUEST['fk_soc']) | (int)isset($_REQUEST['fk_product'])
					//,'messageNothing'=>"Il n'y a aucun ".$langs->trans('WorkStation')." à afficher"
					//,'picto_search'=>img_picto('','search.png', '', 0)
			)
            
	));
        $btHtml = '<div class="tabsAction">
                        <div class="inline-block divButAction"><a href="'. dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$id.'&action=add" class="butAction">Ajouter</a></div>
                   </div>';
        
	return $html . $btHtml;	
}

//liste de tout les doc traces pour doctrace
function _listeAll($PDOdb, $id){
    //
}

//afficher form de création
function _createForm($idprod){
    
    global $langs, $user, $conf, $db;
    $TBS=new TTemplateTBS();
    $TBS->TBS->protect=false;
    $TBS->TBS->noerr=true;
    
    $formcore = new TFormCore;
    //$formcore->Set_typeaff($mode);
    //$form = new Form($db);
    $formFile = new FormFile($db);
    
    $html = $TBS->render('tpl/doctrace.tpl.php'
		,array() // Block
		,array(
				'object'=>''
				,'view' => array(
					//'mode' => $mode
					'action' => 'save'
					//,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
					,'inputNlot' => $formcore->texte('','nlot','','')
					,'inputCom' => $formcore->texte('','comment','','')
					,'inputDate' => $formcore->calendrier('','date_cre','','')
				)
				,'langs' => $langs
				,'user' => $user
				,'conf' => $conf
		)
	);
    
    return $html;
}

//afficher form d'edition
function _editForm($object){
    
    global $langs, $user, $conf, $db;
    $TBS=new TTemplateTBS();
    $TBS->TBS->protect=false;
    $TBS->TBS->noerr=true;
    
    $formcore = new TFormCore;
    //$formcore->Set_typeaff($mode);
    //$form = new Form($db);
    $formFile = new FormFile($db);
    
    $html = $TBS->render('tpl/doctrace.tpl.php'
		,array() // Block
		,array(
				'object'=>''
				,'view' => array(
					//'mode' => $mode
					'action' => 'save'
					//,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
					,'inputNlot' => $formcore->texte('','nlot',150,'')
					,'inputCom' => $formcore->texte('','comment',150,'')
					,'inputDate' => $formcore->calendrier('','date_cre',150,'')
				)
				,'langs' => $langs
				,'user' => $user
				,'conf' => $conf
		)
	);
    
    return $html;
}