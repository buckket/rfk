<?php
$template = array();
require_once('../lib/common-web.inc.php');
include('include/listenercount.php');

$template['section'] = "register";
$template['PAGETITLE'] = "Registrieren";

if(isset($_POST['action']) && $_POST['action'] == 'register'){
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
        $template['username'] = $_POST['username'];
        $template['streampassword'] = $_POST['streampassword'];
    }
}

cleanup_h2o($template);
$h2o = new H2o('register.html',$h2osettings);
echo $h2o->render($template);
?>
