<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('register.html');
include('include/listenercount.php');

$template->assign('section', "register");
$template->assign('PAGETITLE', "Registrieren");

if($_POST['action'] == 'register'){
    $err = false;
    if($_POST['password'] != $_POST['password2']){
        $_MSG['err'][] = 'Die Passwörter stimmen nicht überein';
        $err = true;
    }
    if(strlen($_POST['username']) < 3){
        $_MSG['err'][] = 'Der Benutzername muss mindestens 3 Zeichen enthalten';
        $err = true;
    }
    if(!$err){
        if($user->register($_POST['username'],$_POST['password']) == 0){
            $user->set_streampassword($_POST['streampassword']);
            $_MSG['msg'][] = 'Erfolgreich registriert';
        }else{
            $_MSG['err'][] = 'Fehler beim registrieren';
        }
    }else{
        $template->assign('username',$_POST['username']);
        $template->assign('streampassword',$_POST['streampassword']);
    }
}

cleanup($template);
echo $template->render();
?>
