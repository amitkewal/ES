<?php
    //sudo service elasticsearch start
        require 'vendor/autoload.php';
        $client = Elasticsearch\ClientBuilder::create()->build();
        $profile_url = 'localhost:9200/profile/_search';
        $event_url = 'localhost:9200/event/_search';
        $ch = curl_init($profile_url);
        $params=[   "scroll" => "5s",          // how long between scroll requests. should be small!
                    "size" => 1,
                    "index" =>"profile",
                    'body' => [
                        'query' => [
                                "range" => [
                                        "lastlogindate_f" => [
                                            "gt" => "now-60d",
                                            "lt" => "now-15d"
                                        ]
                                    ]
                        ]
                    ]
                ];
                
        $curl_post_data=json_encode($params['body']);
        $response = $client->search($params);

        while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) 
        {
           
            echo "------------------------------------------------------";
            echo "\n\n\n\n";
            print_r($response['hits']['hits'][0]['_source']['memberlogin']);

            $scroll_id = $response['_scroll_id'];
            echo "\n\n\n\n";
            echo "------------------------------------------------------";
            $response = $client->scroll([
                    "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
                    "scroll" => "5s"           // and the same timeout window
                ]
            );
        }
?>

