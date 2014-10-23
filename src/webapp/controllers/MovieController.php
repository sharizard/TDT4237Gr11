<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Movie;
use tdt4237\webapp\models\MovieReview;
use tdt4237\webapp\Auth;

class MovieController extends Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if (Auth::guest()) {
            $this->app->redirect('/login');
        } else {
            $movies = Movie::all();

            usort($movies, function ($a, $b) {
                        return strcmp($a->getName(), $b->getName());
                    });

            $this->render('movies.twig', ['movies' => $movies]);
        }
    }

    /**
     * Show movie by id.
     */
    function show($id) {
        if (Auth::guest()) {
            $this->app->redirect('/login');
        } else {
            // Create token and pass it to the rendered template
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));

            $this->render('showmovie.twig', [
                'movie' => Movie::find($id),
                'reviews' => MovieReview::findByMovieId($id),
                'username' => $_SESSION['user'],
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }
    }

    function addReview($id) {
        if (Auth::guest()) {
            $this->app->redirect('/login');
        } else {
            $text = $this->app->request->post('text');
            $token = $this->app->request->post('csrf_token');

            if ($token == $_SESSION['csrf_token']) {
                $review = MovieReview::makeEmpty();
                $review->setAuthor($_SESSION['user']);
                $review->setText($text);
                $review->setMovieId($id);
                $validationErrors = MovieReview::validate($text);

                if(sizeof($validationErrors) > 0) {
                    $errors = join("<br>\n", $validationErrors);
                    $this->app->flash('error', $errors);
                }
                else {
                    $review->save();
                    $this->app->flash('info', 'The review was successfully saved.');
                }
            }

            $this->app->redirect('/movies/' . $id);
        }
    }

}
