<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class AdminController extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $_SESSION["csrf_token"] = md5(uniqid(mt_rand(), true));
        $crsf_token = $_SESSION["csrf_token"];

        if (Auth::guest()) {
            $this->app->redirect('/login');
        }

        if (!Auth::isAdmin()) {
            $this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
        }

        $users = User::all();

        $i = 0;
        foreach ($users as $user) {
            if ($user->getUserName() == $_SESSION['user']) {
                unset($users[$i]);
            }
            $i++;
        }


        $variables = [
            'users' => $users,
            'csrf_token' => $crsf_token
        ];
        $this->render('admin.twig', $variables);
    }

    function delete($username) {
        $request = $this->app->request;
        $token = $request->post('csrf_token');

        if (Auth::guest() || !Auth::isAdmin()) {
            $this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
        } else if (Auth::isAdmin()) {

            if ($username != $_SESSION['user']) {
                // Only performs the deletion if the admin pushed the delete button
                if ($this->app->request->isPost() && $token == $_SESSION["csrf_token"]) {

                    // if # rows is bigger than 0, meaning that we've successfully deleted an user
                    if (User::deleteByUsername($username) > 0) {
                        $this->app->flash('info', "Sucessfully deleted '$username'");
                    } else {
                        $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
                    }
                }
            }

            $this->app->redirect('/admin');
        }
        $this->app->redirect('/admin');
    }

}
