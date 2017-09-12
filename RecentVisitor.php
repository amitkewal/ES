<?php
    //sudo service elasticsearch start
    require 'vendor/autoload.php';
    $client = Elasticsearch\ClientBuilder::create()->build();
    $tap_array=array();
    // create curl resource
    $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                "size" => 5,
                "index" =>"event",
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => [

                                'term' => [ 'name' => 'profile_visited' ]
                            ]                                
                        ]
                    ]
                ]
            ];
    $curl_post_data=json_encode($params['body']);
    $response = $client->search($params);
    print_r($response);
    // Now we loop until the scroll "cursors" are exhausted
    while (isset($response['hits']['hits']) && count($response['hits']['hits']) >0) 
    {
        $hits = $response['hits']['hits'];
        foreach ($hits as $key => $memberData) {
            $id=$memberData['_source']['receiver'];
            $params=[   "scroll" => "50s",          // how long between scroll requests. should be small!
                "size" => 5,
                "index" =>"profile",
                'body' => [
                    'query' => [
                        "bool" => [
                            "must" =>   [  
                                            ["match" => [ "memberlogin" => "$id" ]],
                                            ["match" => [ "email_verified" => "Yes" ]],
                                            ["match" => [ "memberstatus" => "Active" ]]
                                        ]    
                                    ]
                                ]
                            ]
                        ];
            echo  $curl_post_data=json_encode($params['body']);
            $response1 = $client->search($params);
            print_r($response1);
            if($response1['hits']['total']!=0 && !in_array($id,$tap_array))
            array_push($tap_array,$id);
        }
        $scroll_id = $response['_scroll_id'];
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

