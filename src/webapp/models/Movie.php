<?php

namespace tdt4237\webapp\models;
use PDO;

class Movie
{
    private $id;
    private $name;
    private $imageUrl;

    static $app;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    static function make($id, $name, $imageUrl)
    {
        $movie = new Movie();
        $movie->id = $id;
        $movie->name = $name;
        $movie->imageUrl = $imageUrl;

        return $movie;
    }

    /**
     * Find a movie by id.
     */
    static function find($id)
    {
        $find_movie = "SELECT * FROM movies WHERE id = :id";

        $query = self::$app->db->prepare($find_movie);
        $query->execute(array('id' => $id));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row == false) {
            return null;
        }

        return Movie::makeFromRow($row);
    }

    /**
     * Fetch all movies.
     */
    static function all()
    {
        $query = "SELECT * FROM movies";


        $results = self::$app->db->prepare($query);
        $results->execute(array());

        $movies = [];

        foreach ($results as $row) {
            $movie = Movie::makeFromRow($row);
            array_push($movies, $movie);
        }

        return $movies;
    }

    static function makeFromRow($row) {
        $movie = self::make(
            $row['id'],
            $row['name'],
            $row['imageurl']
        );

        return $movie;
    }
}
Movie::$app = \Slim\Slim::getInstance();
