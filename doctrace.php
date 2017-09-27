<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('/quality/class/doctrace.class.php');
dol_include_once('/quality/lib/quality.lib.php');

if(empty($user->rights->quality->all->read)) accessforbidden();

$langs->load('quality@quality');
$langs->load("products");

$action = 		GETPOST('action');
$idprod = 		GETPOST('idprod', 'int');
$com =          GETPOST('comment');
$nlot =         GETPOST('nlot');
$date = 		GETPOST('date_cre');
$id = 		    GETPOST('dtid');


$pageBaseUrl =  dol_buildpath('/quality/doctrace.php', 1).'?idprod='. $idprod;

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

            //save echoue
            if(empty($object->nlot) || empty($object->date_cre))
            {
                $errorPost = true;
                $html = _createEditForm($idprod);
                break;
            }
            else if ($error > 0)
            {
                $action = 'edit';
                exit;
            }

            //save in db
            $object->save($PDOdb, true);

            //file gestion
            if($_FILES['userfile']['size'] > 0){
                $productstatic->fetch($idprod);//get ref product to store inside product folder
                $upload_dir = $conf->service->multidir_output[$object->entity].'/'.$productstatic->ref.'/doctraces/'.get_exdir(0, 0, 0, 0, $object, 'doctrace').dol_sanitizeFileName($object->ref);
                include_once DOL_DOCUMENT_ROOT.'/core/actions_linkedfiles.inc.php';
            }
            header('Location: '.dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$idprod.'&action=showSpecDoctrace');
            exit;

        case 'delete':
            $object->delete($PDOdb);
            header('Location: '.dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$idprod.'&action=showSpecDoctrace');
            exit;

        case 'showSpecDoctrace':
            $html = _liste($PDOdb, $idprod);
            break;

        case 'showAllDocTrace':
            $html = _listeAll($PDOdb);
            break;

        case 'add':
            $html = _createEditForm($idprod);
            //$mode = 'edit';
            break;

        case 'edit':
            $html = _createEditForm($idprod, 'edit', $object);
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

llxHeader('',$langs->trans("quality"));

if($action == 'showSpecDoctrace')
{
    $productstatic->fetch($idprod);
    $head = product_prepare_head($productstatic);
    dol_fiche_head($head, 'doctrace', $langs->trans("CardProduct".$object->type), 0, $picto);

    print $html;
}
if($action == 'add' || $action == 'edit' || $action == 'save')
{
    $productstatic->fetch($idprod);
    $head = product_prepare_head($productstatic);
    dol_fiche_head($head, 'doctrace', $langs->trans("CardProduct".$object->type), 0, $picto);

    $formcore = new TFormCore;
    print $formcore->begin_form($pageBaseUrl . '&action=save', 'form_doctrace','POST', true);
    print $html;
    print $formcore->end_form();

    if($errorPost) setEventMessage('Les champs Numéro de lot et Date sont obligatoires', 'errors');
}

llxFooter();

/**
 * Display functions
 */

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
function _createEditForm($idprod, $mode = 'create', $object = null){
    global $langs, $user, $conf, $db;
    $TBS=new TTemplateTBS();
    $TBS->TBS->protect=false;
    $TBS->TBS->noerr=true;

    $formcore = new TFormCore;
    //$formcore->Set_typeaff($mode);
    //$form = new Form($db);
    //$formFile = new FormFile($db);

    //vars
    $defValNlot = 		GETPOST('nlot');
    $defValComment = 	GETPOST('comment');
    $defValDate = 		GETPOST('date_cre');
    if($mode == 'edit')
    {
        $defValNlot = 		$object->nlot;
        $defValComment = 	$object->comment;
        $defValDate = 		$object->date_cre;
    }

    $html = $TBS->render('tpl/doctrace.tpl.php'
        ,array() // Block
        ,array(
            'object'=>$object
            ,'view' => array(
                    'action' => 'save'
                    //,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object->generic, 'ref', $linkback, 1, 'ref', 'ref', '')
                    ,'hiddenSendIt' => $formcore->hidden('sendit',1)
                    ,'hiddenIdProd' => $formcore->hidden('fk_product',$idprod)
                    ,'hiddenEntity' => $formcore->hidden('entity',1)
                    ,'inputNlot' => $formcore->texte('','nlot',$defValNlot,'')
                    ,'inputCom' => $formcore->zonetexteXP('','comment',$defValComment,'')
                    ,'inputDate' => $formcore->calendrier('','date_cre',$defValDate,'')
                    ,'inputFile' => $formcore->fichier('','userfile','test','')
                    ,'btSubmit' => $formcore->btsubmit('Valider','','','button')
                )
        ,'langs' => $langs
        ,'user' => $user
        ,'conf' => $conf
        )
    );

    return $html;
}