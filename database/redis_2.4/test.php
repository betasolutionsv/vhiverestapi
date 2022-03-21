<?php

    require "predis/autoload.php";
    Predis\Autoloader::register();

    /**
    * Radis(NoSQl) Database Connection
    */
    class Redis {

        public function connect() {

            try {

                $redis = new Predis\Client();

                // This connection is for a remote server
                /*
                    $redis = new PredisClient(array(
                        "scheme" => "tcp",
                        "host" => "153.202.124.2",
                        "port" => 6379
                    ));
                */
                
                // echo "Successfully connected to Redis";
                // exit;

                // $redis->set("hello_world", "Hi from php!");
                // $value = $redis->get("hello_world");
                // var_dump($value);
                // echo ($redis->exists("Santa Claus")) ? "true" : "false";
                // exit;

                $iterator = NULL;
                while($iterator != 0) {
                    $arr_keys = $redis->scan($iterator, 'source_1234_[a-zA-Z]*_[0-9]*');
                    foreach($arr_keys as $str_key) {
                        echo $str_key;
                    }
                }

                echo 'fdh';
                
                // return $redis;
            }
            catch (Exception $e) {
                die("Redis Database Error: ".$e->getMessage());
            }
        }
    }

    $r = new Redis;
    echo $r->connect();

?>