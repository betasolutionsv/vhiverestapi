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

                // $iterator = NULL;
                // while($iterator != 0) {
                //     $arr_keys = $redis->scan($iterator, 'source_1234_[a-zA-Z]*_[0-9]*');
                //     foreach($arr_keys as $str_key) {
                //         echo $str_key;
                //     }
                // }
                // $redis->set("white-list", "Hi from php!");
                // $redis->set("black-list", "Hi from php!");

                // $redis->hmset("white-list", array(
                //     "user_id" => 1,
                //     "token" => "QWEUYFDEWDYG12324",
                //     "exp_time" => 60)
                // );

                // $redis->hincrby("white-list", array(
                //     "user_id" => 2,
                //     "token" => "QWEUYFDEWDYG12324",
                //     "exp_time" => 60), 1);

                // $redis->hset("taxi_car", "nr_starts", 0);

                // $taxi_car = $redis->hgetall("taxi_car");
                // var_dump($taxi_car);

                // $redis->hincrby("taxi_car", "nr_starts", 1);

                // $taxi_car = $redis->hgetall("taxi_car");
                // var_dump($taxi_car);

                // HMSET my_table:1:a:1 field4 'Redis is easy' field5 'SQL is powerful'
                // HMSET my_table:1:a:2 field4 'Redis is easy' field5 'SQL is powerful'
                // HMSET my_table:1:a:3 field4 'Redis is easy' field5 'SQL is powerful'

                // $redis->hmset("token1", array(
                //     "type" => "white",
                //     "user_id" => 1,
                //     "exp_time" => 10)
                // );

                // $redis->hmset("token2", array(
                //     "type" => "black",
                //     "user_id" => 1,
                //     "exp_time" => 10)
                // );


                // $redis->hmset("white-list", array(
                //     "user_id" => 2,
                //     "token" => "QWEUYFDEWDYG12324",
                //     "exp_time" => 60)
                // );

                // $List = $redis->hgetall("token1");
                // var_dump($List);
                // echo "<br>";

                // $redis->set("key2", "Hi from php!");
                // $value = $redis->get("hello_world");
                // var_dump($value);
                // $redis->flushAll();
                // $value = $redis->get("hello_world");
                // var_dump($value);
                // echo 'fdh';

                // $redis->set("t1", "Hi from php!");
                // $redis->set("t2", "Hi from php!");

                // $arList = $redis->keys("*"); 
                // var_dump($arList);

              

                $arList = $redis->keys("*"); 
                // var_dump($arList);
                
              
                // $redis->expire("t1",10); 
                // $redis->del("t1");
                var_dump($arList);
                exit;
                
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