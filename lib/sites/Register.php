<?php
class Register extends Site {
    public function render() {
        global $template,$urlParams;

        $params = $urlParams->getParams();

        switch($params[0]) {
            case 'info':
                break;
            case 'checkusername':
                echo json_encode(array('return' => User::checkUsername($_POST['username'])));
                return Site::$DISABLE_TEMPLATE;
                break;
            case 'overview':
            default:
                $url = new UrlParser('register');
                $template->setTitle('Register new account');
                $template->setTemplate('register.html');
                $template->addData('jsonurl', $url->makeUrl());
                return Site::$RENDER_TEMPLATE;
        }

    }
}