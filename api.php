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
    public function curl_connect(string $url, string $request_type, Array $data = array()){
        
        $url = ENDPOINT . $url;

        if ($request_type == 'GET'){
            $url .= '?' . http_build_query($data);
        }

        $curl = curl_init();

        $headers = array(
            'Content-Type: application/json'
        );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
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