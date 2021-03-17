<?php

function getAllMovies()
{
    $pdo      = Database::getInstance()->getConnection();
    $queryAll = "SELECT * FROM tbl_movies";
    $runAll   = $pdo->query($queryAll);
    $movies   = $runAll->fetchAll(PDO::FETCH_ASSOC);

    if ($movies) {
        return $movies;
    } else {
        return 'There was a problem accessing this info';
    }
}

function getSingleMovie($id)
{
    $pdo = Database::getInstance()->getConnection();
    ## TODO: finish the line with a proper SQL query that only fetch movie for the given id
    $querySingle = 'SELECT * FROM tbl_movies WHERE movies_id = "' . $id . '"';
    $runSingle   = $pdo->query($querySingle);

    if ($runSingle) {
        $movie = $runSingle->fetch(PDO::FETCH_ASSOC);
        return $movie;
    } else {
        return 'There was a problem to fetch single movie for ' . $id;
    }
}

function getMoviesbyGenre($genre) {
    $pdo = Database::getInstance()->getConnection();
    // $query = 'SELECT * FROM `tbl_movies` LEFT JOIN tbl_mov_genre ON tbl_movies.movies_id = tbl_mov_genre.movies_id LEFT JOIN tbl_genre ON tbl_mov_genre.genre_id = tbl_genre.genre_id WHERE genre_name = "'.$genre.'"'; -- this query I wrote

    $query = 'SELECT m.*, GROUP_CONCAT(g.genre_name) AS genre_name FROM tbl_movies m';
    $query.= ' LEFT JOIN tbl_mov_genre link ON link.movies_id = m.movies_id'; 
    $query.= ' LEFT JOIN tbl_genre g ON link.genre_id = g.genre_id';
    $query.= ' GROUP BY m.movies_id HAVING genre_name LIKE "%'.$genre.'%"';

    $runQuery = $pdo->query($query);
    if ($runQuery){
        $movies = $runQuery->fetchAll(PDO::FETCH_ASSOC);
        return $movies;
    } else{
        return 'There was a problem fetching by the genre'.$genre;
    }
}
