<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Auth;
use tdt4237\webapp\Hash;
use tdt4237\webapp\RandomStringGenerator;
use PDO;

class LoginController extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {

            // Create token and pass it to the rendered template
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
            $this->render('login.twig', [
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }
    }

    function login() {
        if ($this->app->request->post('token') !== null) {

            $request = $this->app->request;
            $user = $request->post('user');
            $pass = $request->post('pass');
            $token = $request->post('token');

            if ($token == $_SESSION['csrf_token']) {
            
            	$ip = $_SERVER['REMOTE_ADDR'];

                if (Auth::checkCredentials($user, $pass)) {
                
                $q = $this->app->db->prepare("DELETE FROM failed_logins WHERE ip_address = ?");
                $q->execute(array($ip));

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
                
                	$q = $this->app->db->prepare("INSERT INTO failed_logins(ip_address) VALUES (?)");
                	$q->execute(array($ip));
                
                	$q = $this->app->db->prepare("SELECT Count (*) FROM failed_logins WHERE ip_address=?");
					$q->execute(array($ip));
					$numRows = $q->fetch(PDO::FETCH_NUM);
                	
                	$delay = $numRows[0] / 10;
					sleep($delay);
                
                    $this->app->flashNow('error', 'Incorrect user/pass combination.');

                    // Create token and pass it to the rendered template
                    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
                    $this->render('login.twig', [
                        'csrf_token' => $_SESSION['csrf_token']
                    ]);

                }
            }
        }
    }

    function sendMail($to, $subject, $body) {

        $this->app->mail->isSMTP();
        $this->app->mail->CharSet = 'UTF-8';
        $this->app->mail->Host = "smtp.gmail.com"; // SMTP server example
        $this->app->mail->SMTPAuth = true;                  // enable SMTP authentication
        $this->app->mail->Port = 587;                    // set the SMTP port for the GMAIL server
        $this->app->mail->Username = "moviereviewsgr11@gmail.com"; // SMTP account username example
        $this->app->mail->Password = "portalen";
        $this->app->mail->FromName = 'moviereviewsgr11@gmail.com';
        $this->app->mail->SMTPSecure = 'tls';
        $this->app->mail->addAddress($to);
        $this->app->mail->Subject = $subject;
        $this->app->mail->Body = $body;
        
        if (!$this->app->mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $this->app->mail->ErrorInfo;
        } else {
            echo "Email sent! <br/> Follow the email's description to reset password.";
        }
    }

    // The recover view is two-fold. The first page contains the username and email field, whereas the second page is only displayed when redirected from URL.
    function recover() {
        if ($this->app->request->isGet()) {
            $request = $this->app->request;
            $get_username = $request->get('username');
            $get_code = $request->get('reset');

            // Returns the username and email page.
            if ($get_code == null) {
                $this->render('recover.twig', []);
            }

            $a = $this->app->db->prepare("SELECT * FROM users WHERE user=?");
            $a->execute(array($get_username));
            $row = $a->fetch(PDO::FETCH_ASSOC);

            $db_code = $row['reset'];
            $db_username = $row['user'];

            // Return the "set new password page".
            if ($get_code != null) {
                if ($get_username == $db_username && $get_code == $db_code) {
                    $this->render('recover.twig', ['reset' => $get_code, 'username' => $db_username]);
                }
            }
        }

        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            $username = $request->post('username');
            $email = $request->post('email');

            $newpass = $request->post('newpass');
            $newpass1 = $request->post('newpass1');
            $code = $request->get('reset');

            // Handles the post request from the "set password page"
            if ($email == NULL) {
                // Validate passwords
                if ($newpass == $newpass1) {

                    $validationErrors = User::validatePass($newpass);

                    if (sizeof($validationErrors) > 0) {
                        $errors = join("<br>\n", $validationErrors);
                        $this->app->flashNow('error', $errors);

                        $this->render('recover.twig', [
                            'reset' => $code,
                            'username' => $username
                        ]);
                    } else {
                        $salt = Hash::createSalt();
                        $hash = Hash::make($newpass, $salt);
                        $q = $this->app->db->prepare("UPDATE users SET pass=?, salt=?, reset=? WHERE user=?");
                        $q->execute(array($hash, $salt, "", $username));
                        $this->app->flash('info', 'Password is updated!');
                        $this->app->redirect('/login');
                    }
                } else {
                    $this->app->flashNow('error', 'The passwords must match');
                    $this->render('recover.twig', [
                        'reset' => $code,
                        'username' => $username
                    ]);
                }
            }
            // Handles the post request from the username and email page.
            else {
                $q = $this->app->db->prepare("SELECT Count (*) FROM users WHERE user=?");
                $q->execute(array($username));
                $numRows = $q->fetch(PDO::FETCH_NUM);

                if ($numRows[0] != 0) {

                    $a = $this->app->db->prepare("SELECT * FROM users WHERE user=?");
                    $a->execute(array($username));
                    $row = $a->fetch(PDO::FETCH_ASSOC);

                    $db_email = $row['email'];

                    if ($email == $db_email) {
                        $code = RandomStringGenerator::generateRandomString();
//                        $code = rand(1000000, 1000000000);
                        $to = $db_email;
                        $subject = "Password Recovery";
                        $body = "Change the URL to fit your local config. Click to reset password: http://localhost:8080/login/recover?reset=$code&username=$username";

                        $sql = $this->app->db->prepare("UPDATE users SET reset=? WHERE user=?");
                        $sql->execute(array($code, $username));
                        $this->sendMail($to, $subject, $body);
                    } else {
                        echo "Incorrect email";
                    }
                } else {
                    echo "Username doesn't exist";
                }
            }
        }
    }

}
