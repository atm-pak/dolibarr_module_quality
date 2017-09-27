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

$folderDocs = 'doctraces';

//displayLinkToDoc(1, $idprod);
//die('ok');

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
                $upload_dir = $conf->quality->multidir_output[$object->entity].'/'. $folderDocs .'/'.dol_sanitizeFileName($object->ref);
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
    $sql= "SELECT nlot, date_cre, comment, entity, ref, fk_product, rowid FROM llx_doctrace WHERE fk_product = ". $id;

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
                'rowid'=>"Document lié"
            )
        ,'hide' => array(
                'entity',
                'fk_product',
                'ref'
            )
        ,'liste'=>array(
                'titre'=>'Liste des documents de tracabilité'
                ,'image'=>img_picto('','title.png', '', 0)
                //,'picto_precedent'=>img_picto('','back.png', '', 0)
                //,'picto_suivant'=>img_picto('','next.png', '', 0)
                //,'noheader'=> (int)isset($_REQUEST['fk_soc']) | (int)isset($_REQUEST['fk_product'])
                //,'messageNothing'=>"Il n'y a aucun ".$langs->trans('WorkStation')." à afficher"
                //,'picto_search'=>img_picto('','search.png', '', 0)
            )
        ,'eval'=>array(
                'rowid' => '__displayLinkToDoc(@rowid@, @fk_product@)'
                //'rowid' => '_test()'
            )
    ));

    $btHtml = '<div class="tabsAction">
                    <div class="inline-block divButAction"><a href="'. dol_buildpath('/quality/doctrace.php', 1).'?idprod='.$id.'&action=add" class="butAction">Ajouter</a></div>
               </div>';

    return $html . $btHtml;
}

//afficher le contenu du champ "Document lié"
function __displayLinkToDoc($id, $fk_product){
        global $db, $conf, $PDOdb;
    $object = new TDoctrace();
    $object->load($PDOdb, $id);
    
    $formfile = new FormFile($db);
    
    $productstatic = new Product($db);
    $productstatic->fetch($fk_product);
        
    /*$filename=dol_sanitizeFileName($obj->ref);
    $filedir=$conf->propal->dir_output . '/' . dol_sanitizeFileName($obj->ref);
    print $formfile->getDocumentsLink($objectstatic->element, $filename, $filedir);*/
    
    $subdir='doctraces/'.dol_sanitizeFileName($object->ref);
    $filedir=$conf->quality->multidir_output[$object->entity].'/'. $subdir;
    //$urlsource=$_SERVER['PHP_SELF'].'?id='.$object->rowid;
    
    //var_dump($conf->quality->multidir_output, $object->element, $object->ref, $subdir, $filedir);
    
    $linkToDoc = $formfile->getDocumentsLink($object->element, $subdir, $filedir);
    //var_dump($linkToDoc);
    //die('ok');
    $linkToDocManuel = '<a href="'.$_SERVER['DOCUMENT_ROOT'].'"/documents/quality/doctraces"';
        
    $html = '<table class="nobordernopadding"><tr class="nocellnopadd">
                <tr>
                    <td class="nobordernopadding nowrap">

                    </td>
                    <td width="16" align="right" class="nobordernopadding">
                        '. $linkToDoc .'
                    </td>
                </tr>
            </table>';    
                    
    return $html;
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