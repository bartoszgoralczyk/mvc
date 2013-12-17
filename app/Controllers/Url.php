<?php

namespace App\Controllers
{
    use App\Models as Models;

    class Url extends \Framework\Controller
    {
        function __construct($options = array())
        {
            parent::__construct($options);
        }

	function l($url)
	{
	    header("Location: ".$url);
	    exit();
	}
    }
}