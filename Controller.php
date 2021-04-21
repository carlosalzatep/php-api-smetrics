<?php

class Controller extends Api{

    public $sl_token = '';
    public $data = array();
    public $postsList = array();
    public $errors = array();

    function __construct($data){

        $this->data = $data;

        // 1. Register Token
        self::regiterToken();

        // 2. Fetch posts
        self::fetchFullPosts( STRATPAGE );

        echo json_encode($this->postsList, JSON_PRETTY_PRINT);

        self::getErrors();
    }

    /**
     * Get sl_token
     * @param String $url
     * @param String $request_type POST/GET/PUT...
     * @param Array $data
     * @return bool 
     */
    public function regiterToken(){

        $TMPregister = parent::curl_connect('register', 'POST', $this->data);

        if (!$TMPregister || !isset($TMPregister->data->sl_token)  || isset($TMPregister->error)) {
            $this->errors[] = isset($TMPregister->error) ? $TMPregister->error->message : "Error in register process";
            return false;
        }

        $this->sl_token = $TMPregister->data->sl_token;

        return true;
    }

    /**
     * Fetch full posts array list
     * @param Int $page
     * @return Array 
     */
    public function fetchFullPosts(int $page = 1){

        do{

            $data = array(
                'sl_token' => $this->sl_token,
                'page' => $page
            );

            $TMPposts = parent::curl_connect('posts', 'GET', $data);

            if (!$TMPposts || !isset($TMPposts->data)  || isset($TMPposts->error)) {
                $this->errors[] = isset($TMPposts->error) ? $TMPposts->error->message : "Error fetching posts process";
                return false;
            }

            array_push($this->postsList, $TMPposts);
        }
        while ($page++ < PAGES);
    }

    /**
     * Get erros list
     * @return echo
     */
    public function getErrors(){
         echo json_encode($this->errors);
    }
}