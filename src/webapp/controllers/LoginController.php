<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            $this->render('login.twig', []);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $user = $request->post('user');
        $pass = $request->post('pass');

        if (Auth::checkCredentials($user, $pass)) {
        
        	// Regenerate session id and clear session array
        	session_regenerate_id();
        	$_SESSION = array();
        
            $_SESSION['user'] = $user;

            $isAdmin = Auth::user()->isAdmin();

            if ($isAdmin) {
                setcookie("isadmin", "yes");
            } else {
                setcookie("isadmin", "no");
            }

            $this->app->flash('info', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
        } else {
            $this->app->flashNow('error', 'Incorrect user/pass combination.');
            $this->render('login.twig', []);
        }
    }
}
