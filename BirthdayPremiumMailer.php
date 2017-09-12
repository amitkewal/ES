<?php
    //sudo service elasticsearch start
        require 'vendor/autoload.php';
        $client = Elasticsearch\ClientBuilder::create()->build();
        $tap_array=array();
        $date_condition=strtotime("+1 day");
        echo $date_condition= date("*-m-d", $date_condition);
        $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                    "size" => 2,
                    "index" =>"profile",
                    'body' => [
                        'query' => [
                            'bool' => [
                                "must_not" => ["match" => [ "membership" => "free" ]],
                                "must" => ["term" => [ "dateofbirth_f" => "$date_condition" ]]
                            ]
                        ]
                    ]
                ];
        echo $curl_post_data=json_encode($params['body']);
        $response = $client->search($params);
        print_r($response);
        // Now we loop until the scroll "cursors" are exhausted
        while (isset($response['hits']['hits']) && count($response['hits']['hits']) >0) 
        {
            //SEND THE MAILS BATCWHISE
            // When done, get the new scroll_id
            $scroll_id = $response['_scroll_id'];
            // echo "$scroll_id";
            // Execute a Scroll request and repeat
            $response = $client->scroll([
                    "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
                    "scroll" => "50s"           // and the same timeout window
                ]
            );
        }
?>

