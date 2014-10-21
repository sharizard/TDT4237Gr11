<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;

class Controller {

    protected $app;

    function __construct() {
        $this->app = \Slim\Slim::getInstance();
    }

    function render($template, $variables = []) {
        if (!Auth::guest()) {
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = Auth::isAdmin();
            $variables['loggedInUsername'] = $_SESSION['user'];
        }

        print $this->app->render($template, $variables);
    }

}
