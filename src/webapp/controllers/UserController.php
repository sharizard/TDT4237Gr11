<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;
use tdt4237\webapp\Auth;

class UserController extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if (Auth::guest()) {
            // Create token and pass it to the rendered template
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
            $this->render('newUserForm.twig', [
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create() {
        if ($this->app->request->post('csrf_token') !== null) {
            $request = $this->app->request;
            $username = $request->post('user');
            $email = $request->post('email');
            $pass = $request->post('pass');
            $token = $request->post('csrf_token');
            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->app->flash('error', "Email is not valid");
                return $this->app->redirect('/user/new');
            }

            if ($token == $_SESSION['csrf_token']) {
                $salt = Hash::createSalt();
                $hashed_password = Hash::make($pass, $salt);
                $user = User::makeEmpty();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setSalt($salt);
                $user->setHash($hashed_password);
                $validationErrors = User::validate($user, $pass);

                if (sizeof($validationErrors) > 0) {
                    $errors = join("<br>\n", $validationErrors);
                    $this->app->flashNow('error', $errors);
                    self::generateToken($username);
                } else {
                    $user->save();
                    $this->app->flash('info', 'Thanks for creating a user. Now log in.');
                    $this->app->redirect('/login');
                }
            }
        } else {
            $this->app->redirect('/user/new');
        }
    }

    function generateToken($username) {
        // Create token and pass it to the rendered template
        $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        $this->render('newUserForm.twig', [
            'username' => $username,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    function all() {
        if (Auth::guest()) {
            $this->app->redirect('/login');
        } else {
            $users = User::all();
            $this->render('users.twig', ['users' => $users]);
        }
    }

    function logout() {
        Auth::logout();
        $this->app->redirect('/?msg=Successfully logged out.');
    }

    function show($username) {
        $user = User::findByUser($username);
        $this->render('showuser.twig', [
            'user' => $user,
            'username' => $username
        ]);
    }

    function edit() {
        if (Auth::guest()) {
            $this->app->redirect('/login');
            return;
        }

        $user = Auth::user();

        if (!$user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        }

        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            $email = $request->post('email');
            $bio = $request->post('bio');
            $age = $request->post('age');
            // Upload avatar if selected
            if ($_FILES["avatar"]["error"] != 4) {
                $user->upload($user->getUserName());
            }
            // Validate Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->app->flash('error', 'Invalid email');
                return $this->app->redirect('edit');
            }
            $user->setEmail($email);
            $user->setBio($bio);
            $user->setAge($age);
            $token = $request->post('csrf_token');

            if ($token == $_SESSION['csrf_token']) {
                $user->setEmail($email);
                $user->setBio($bio);
                $user->setAge($age);

                if (!User::validateAge($user)) {
                    $this->app->flashNow('error', 'Age must be between 0 and 150.');
                } else {
                    $user->save();
                    $this->app->flashNow('info', 'Your profile was successfully saved.');
                }
            }
        }
        // Create token and pass it to the rendered template
        $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        $this->render('edituser.twig', [
            'user' => $user,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

}
