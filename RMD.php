<?php
    //sudo service elasticsearch start
        require 'vendor/autoload.php';
        $client = Elasticsearch\ClientBuilder::create()->build();
        $profile_url = 'localhost:9200/profile/_search';
        $event_url = 'localhost:9200/event/_search';
        $tap_array=array();
        // create curl resource
        $ch = curl_init($profile_url);
        $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                    "size" => 10,
                    "index" =>"event",
                    'body' =>   [
                        'query' =>  [
                            'bool' =>   [
                                'must_not' =>   ['term' =>   [ 'name' => 'recent_visitor_email_sent' ]],
                                "filter" => [
                                    "range" =>  [
                                        "event_property.timestamp" => [
                                                "gt" => "now-1d"
                                                ]
                                            ]
                                        ]                                
                                    ]
                                ]
                            ]
                        ];
        echo $curl_post_data=json_encode($params['body']);
        $response = $client->search($params);
        print_r($response);
        while (isset($response['hits']['hits']) && count($response['hits']['hits']) >0) 
        {
            $hits = $response['hits']['hits'];
            foreach ($hits as $key => $memberData) {
                print_r($memberData['_source']['receiver']);
                $id=$memberData['_source']['receiver'];
                $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                    "size" => 5,
                    "index" =>"profile",
                    'body' => [
                        'query' => [
                            "bool" => [
                                "must" => ["match" => [ "memberlogin" => "$id" ]],
                                "filter" => [
                                    "range" => [
                                        "lastlogindate_f" => [
                                            "gt" => "now-60d",
                                            "lt" => "now-15d"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                echo  "\n\n".$curl_post_data=json_encode($params['body']);
                $response1 = $client->search($params);
                if($response1['hits']['total']!=0 && !in_array($id,$tap_array))
                array_push($tap_array,$id);
            }
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
            echo "+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_+_";
            print_r($tap_array);
?>

