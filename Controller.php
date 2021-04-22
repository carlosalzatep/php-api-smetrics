<?php

class Controller extends Api{

    public $sl_token = '';
    public $data = array();
    public $postsList = array();
    public $errors = array();

    //Stats
    public $AvgCharlenMonth = array();
    public $LongestPostMonth = array();
    public $TotalPostWeek = array();
    public $AvgPostUserMonth = array();

    function __construct($data){

        $this->data = $data;

        $this->AvgCharlenMonth = array(
            'tite' => 'Average character length of posts per month',
            'data' => array()
        );

        $this->LongestPostMonth = array(
            'tite' => 'Longest post by character length per month',
            'data' => array()
        );

        $this->LongestPostMonth = array(
            'tite' => 'Total posts split by week number',
            'data' => array()
        );

        $this->AvgPostUserMonth = array(
            'tite' => 'Average number of posts per user per month',
            'data' => array()
        );

        // 1. Register Token
        $this->regiterToken();

        // 2.,3. Fetch posts
        $this->fetchFullPosts( STRATPAGE );

        //echo json_encode($this->postsList, JSON_PRETTY_PRINT);
        //var_dump($this->postsList);

        // 4. Stats
        $this->getStats();

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
                $this->setLongestPostMonth($itemP);
                $this->setTotalPostWeek($itemP);
                $this->setAvgPostUserMonth($itemP);
            }

            $this->postsList = array_merge($this->postsList, $TMPposts->data->posts);
        }
        while ($page++ < PAGES);
    }


    /**
     * Average character length of posts per month
     */
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


    /**
     * Longest post by character length per month
     */
    public function setLongestPostMonth(object $item)
    {
        $itemMonth = (int)$item->month;
        $currrentStrlen = isset($this->LongestPostMonth['data'][$itemMonth]['post']->strlen) ? $this->LongestPostMonth['data'][$itemMonth]['post']->strlen : 0;

        if( $currrentStrlen <= $item->strlen ){

            $this->LongestPostMonth['data'][$itemMonth]['month_number'] = $item->month;
            $this->LongestPostMonth['data'][$itemMonth]['post'] = $item;
        }
    }

    /**
     * Total posts split by week number
     */
    public function setTotalPostWeek(object $item)
    {
        $itemWeek = (int)$item->week;
        $total_items = isset($this->TotalPostWeek['data'][$itemWeek]['total_items']) ? $this->TotalPostWeek['data'][$itemWeek]['total_items'] + 1 : 1;

        $this->TotalPostWeek['data'][$itemWeek]['week_number'] = $item->week;
        $this->TotalPostWeek['data'][$itemWeek]['total_items'] = $total_items;
    }


    /**
     * Average number of posts per user per month
     */
    public function setAvgPostUserMonth(object $item)
    {
        $itemMonth = (int)$item->month;
        $itemfrom_id = $item->from_id;

        $this->AvgPostUserMonth['data'][$itemfrom_id]['from_name'] = $item->from_name;

        $this->AvgPostUserMonth['data'][$itemfrom_id][$itemMonth]['month_number'] = $item->month;
        $total_items = isset($this->AvgPostUserMonth['data'][$itemfrom_id][$itemMonth]['total_items']) ? $this->AvgPostUserMonth['data'][$itemfrom_id][$itemMonth]['total_items'] + 1 : 1;

        $this->AvgPostUserMonth['data'][$itemfrom_id][$itemMonth]['total_items'] = $total_items;
    }


    /**
     * Print JSON stats
     */
    public function getStats()
    {
        sort($this->AvgCharlenMonth['data']);
        echo json_encode($this->AvgCharlenMonth, JSON_PRETTY_PRINT);

        sort($this->LongestPostMonth['data']);
        echo json_encode($this->LongestPostMonth, JSON_PRETTY_PRINT);

        sort($this->TotalPostWeek['data']);
        echo json_encode($this->TotalPostWeek, JSON_PRETTY_PRINT);

        echo json_encode($this->AvgPostUserMonth, JSON_PRETTY_PRINT);
    }


    /**
     * Print JSON erros list
     * @return echo
     */
    public function getErrors(){
         echo json_encode($this->errors, JSON_PRETTY_PRINT);
    }
}