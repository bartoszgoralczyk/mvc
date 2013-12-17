<?php

namespace App\Controllers
{
    use App\Models as Models;

    class Home extends \Framework\Controller
    {
        function __construct($options = array())
        {
            parent::__construct($options);
        }

        public function index()
        {
            echo "here";

        }

	public function user()
	{

	    $database = new \Framework\Database(array(
		"type" => "mysql",
		"options" => array(
		    "host" => "localhost",
		    "username" => "root",
		    "password" => "",
		    "schema" => "mvc"
		)
	    ));
	    $database = $database->initialize()->connect();
/*
	    $user = new Models\User(array(
		"connector" => $database
	    ));
	    $database->sync($user);
*/
	    $elijah = new Models\User(array(
		"connector" => $database,
		"first" => "chris",
		"last" => "pitt",
		"email" => "chris@example.com",
		"password" => "password",
		"live" => true,
		"deleted" => false,
		"created" => date("Y-m-d H:i:s"),
		"modified" => date("Y-m-d H:i:s")
	    ));
	    $elijah->save();
	    $all = User::all(array(
		"last = ?" => "Pitt"
	    ));
	    $elijah->delete();
	}

	public function user2()
	{
	    $user = new Models\User(array());
	}

        public function db()
        {
            $database = new \Framework\Database(array(
                "type" => "mysql",
                "options" => array(
                    "host" => "localhost",
                    "username" => "root",
                    "password" => "",
                    "schema" => "mvc",
                    "port" => "3306"
                )
            ));
            $database = $database->initialize()->connect();

            $all = $database->query()
                    ->from("users", array(
                        "first_name",
                        "last_name" => "surname"
                    ))
                    ->join("points", "points.id = users.id", array(
                        "points" => "rewards"
                    ))
                    ->where("first_name = ?", "chris")
                    ->order("last_name", "desc")
                    ->limit(100)
                    ->all();

            $print = print_r($all, true);
            echo "all => {$print}";

            $id = $database->query()
                    ->from("users")
                    ->save(array(
                "first_name" => "Liz",
                "last_name" => "Pitt"
            ));

            echo "id => {$id}\n";

            $affected = $database->query()
                    ->from("users")
                    ->where("first_name = ?", "Liz")
                    ->delete();

            echo "affected => {$affected}\n";

            $id = $database->query()
                    ->from("users")
                    ->where("first_name = ?", "Chris")
                    ->save(array(
                "modified" => date("Y-m-d H:i:s")
            ));

            echo "id => {$id}\n";

            $count = $database->query()
                    ->from("users")
                    ->count();

            echo "count => {$count}\n";
        }

    }

}