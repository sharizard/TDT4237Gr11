<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class AdminController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
    	$_SESSION["csrf_token"] = md5(uniqid(mt_rand(), true));
        $crsf_token = $_SESSION["csrf_token"];
    
        if (Auth::guest()) {
            $this->app->flash('info', "You must be logged in to view the admin page.");
            $this->app->redirect('/');
        }

        if (! Auth::isAdmin()) {
            $this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
        }

        $variables = [
            'users' => User::all(),
            'csrf_token' => $crsf_token
        ];
        $this->render('admin.twig', $variables);
    }

    function delete($username)
    {
    	$request = $this->app->request;
        $token = $request->get('csrf_token');
        
    	if (Auth::guest() || !Auth::isAdmin()) {
	    	$this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
    	}
    	
    	else if (Auth::isAdmin()) {
    	
    		// Only performs the deletion if the admin pushed the delete button
    		if ($token == $_SESSION["csrf_token"]) {
    		
	    		if (User::deleteByUsername($username) === 1) {
	            	$this->app->flash('info', "Sucessfully deleted '$username'");
				} else {
	            	$this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
				}
    		}
	
	        $this->app->redirect('/admin');
        }
 
        $this->app->redirect('/admin');
    }
}
