<?php

namespace tdt4237\webapp\models;

class MovieReview
{
    const INSERT_QUERY = "INSERT INTO moviereviews (movieid, author, text) VALUES (:movieid, :author, :text)";
    const SELECT_BY_ID = "SELECT * FROM moviereviews WHERE id = %s";

    private $id = null;
    private $movieId;
    private $author;
    private $text;

    static $app;

    public function getId()
    {
        return $this->id;
    }

    public function getMovieId()
    {
        return $this->movieId;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setMovieId($id)
    {
        $this->movieId = $id;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    static function make($id, $author, $text)
    {
        $review = new MovieReview();
        $review->id = $id;
        $review->author = $author;
        $review->text = $text;

        return $review;
    }

    /**
     * Insert or save review into db.
     */
    function save()
    {
        $movieId = $this->movieId;
        $author = $this->author;
        $text = $this->text;

        if ($this->id === null) {
            $q = static::$app->db->prepare(self::INSERT_QUERY);
        } else {
            // TODO: Update moviereview here
        }
        return $q->execute(array(':movieid'=>$this->movieId, ':author'=>$this->author, ':text'=>$this->text));
    }

    static function makeEmpty()
    {
        return new MovieReview();
    }

    static function validate($text) {
        $validationErrors = [];
        $empty = 0;

        if (strlen($text) <= $empty) {
            array_push($validationErrors, "Textfield can't be empty!");
        }

        return $validationErrors;

    }

    /**
     * Fetch all movie reviews by movie id.
     */
    static function findByMovieId($id)
    {
        $query = "SELECT * FROM moviereviews WHERE movieid = $id";
        $results = self::$app->db->query($query);

        $reviews = [];

        foreach ($results as $row) {
            $review = self::makeFromRow($row);
            array_push($reviews, $review);
        }

        return $reviews;
    }

    static function makeFromRow($row) {
        $review = self::make(
            $row['id'],
            $row['author'],
            $row['text']
        );

        return $review;
    }
}
MovieReview::$app = \Slim\Slim::getInstance();
