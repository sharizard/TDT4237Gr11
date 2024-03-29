<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\NewsItem;

class IndexController extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $request = $this->app->request;
        //$msg = $request->get('msg');
        $msg = filter_var($request->get('msg'), FILTER_SANITIZE_STRING);
        $variables = [];

        if ($msg) {
            $variables['flash']['info'] = $msg;
        }

        $this->render('index.twig', $variables);
    }

}
