<?php

class Controller extends Api{

    public $sl_token = '';
    public $data = array();
    public $postsList = array();
    public $errors = array();

    //Stats
    public $AvgCharlenMonth = array();

    function __construct($data){

        $this->data = $data;

        $this->AvgCharlenMonth = array(
            'tite' => 'Average character length of posts per month',
            'data' => array()
        );

        // 1. Register Token
        $this->regiterToken();

        // 2.,3. Fetch posts
        $this->fetchFullPosts( STRATPAGE );

        //echo json_encode($this->postsList, JSON_PRETTY_PRINT);
        //var_dump($this->postsList);

        // 4. Stats
        $this->getAvgCharlenMonth();

        $this->getErrors();
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

        if ( !$TMPregister || !isset($TMPregister->data->sl_token)  || isset($TMPregister->error) ) {
            $this->errors[] = isset($TMPregister->error) ? $TMPregister->error->message : "Error in register process";
            return false;
        }

        $this->sl_token = $TMPregister->data->sl_token;

        return true;
    }

    /**
     * Fetch full posts array list with month number, week number and chars long
     * @param Int $page
     */
    public function fetchFullPosts(int $page = 1){

        do{

            $data = array(
                'sl_token' => $this->sl_token,
                'page' => $page
            );

            $TMPposts = parent::curl_connect('posts', 'GET', $data);

            if ( !$TMPposts || !isset($TMPposts->data)  || isset($TMPposts->error) ) {
                $this->errors[] = isset($TMPposts->error) ? $TMPposts->error->message : "Error fetching posts process";
                return false;
            }

            //Add: month number, week number and chars long
            foreach( $TMPposts->data->posts as &$itemP ){
                
                $itemP->month = date("m", strtotime($itemP->created_time));
                $itemP->week = date("W", strtotime($itemP->created_time));
                $itemP->strlen = strlen($itemP->message);

                $this->setAvgCharlenMonth($itemP);
            }

            $this->postsList = array_merge($this->postsList, $TMPposts->data->posts);
        }
        while ($page++ < PAGES);
    }


    public function setAvgCharlenMonth(object $item)
    {
        $itemMonth = (int)$item->month;
        $total_strlen = isset($this->AvgCharlenMonth['data'][$itemMonth]['total_strlen']) ? $this->AvgCharlenMonth['data'][$itemMonth]['total_strlen'] + $item->strlen : $item->strlen;
        $total_items = isset($this->AvgCharlenMonth['data'][$itemMonth]['total_items']) ? $this->AvgCharlenMonth['data'][$itemMonth]['total_items']+1 : 1;
        $avg = $total_strlen / $total_items;

        $this->AvgCharlenMonth['data'][$itemMonth]['month_number'] = $item->month;
        $this->AvgCharlenMonth['data'][$itemMonth]['total_strlen'] = $total_strlen;
        $this->AvgCharlenMonth['data'][$itemMonth]['total_items'] = $total_items;
        $this->AvgCharlenMonth['data'][$itemMonth]['avg'] = $avg;
    }

    public function getAvgCharlenMonth()
    {
        echo json_encode($this->AvgCharlenMonth, JSON_PRETTY_PRINT);
    }

    /**
     * Get erros list
     * @return echo
     */
    public function getErrors(){
         echo json_encode($this->errors, JSON_PRETTY_PRINT);
    }
}