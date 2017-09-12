<?php
    //sudo service elasticsearch start
    require 'vendor/autoload.php';
    $client = Elasticsearch\ClientBuilder::create()->build();
    $profile_url = 'localhost:9200/profile/_search';
    $event_url = 'localhost:9200/event/_search';
    $tap_array=array();
    // create curl resource
    // $date_condition=date("Y-m-d");
    $date_condition=strtotime("-3 day");
    $date_condition= date("Y-m-d", $date_condition);
    $ch = curl_init($profile_url);
    $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                "size" => 10,
                "index" =>"profile",
                'body' => [
                    'query' => [
                        'bool' => [
                            "must" => ["term" => [ "profile_activation_date" => "$date_condition" ]]
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

