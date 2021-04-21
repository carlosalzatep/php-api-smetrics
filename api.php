<?php
/**
 * API Curl helpers methods
 */

class Api{

    function __construct(){}

    /**
     * make a Curl connect, send params and close it
     * @param String $url
     * @param String $request_type POST/GET/PUT...
     * @param Array $data
     * @return json
     */
    function curl_connect($url, $request_type, $data = array())
    {
        $url = ENDPOINT . $url;

        if ($request_type == 'GET'){
            $url .= '?' . http_build_query($data);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, 'CURL_HTTP_VERSION_1_1');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request_type); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);

        if ($request_type != 'GET') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); 
        }

        $response =  curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            echo "Curl Error #:" . $error;
            return 0;
        }

        return json_decode($response);
        
    }

}