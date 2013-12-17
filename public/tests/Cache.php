<?php

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache();
        return ($cache instanceof Framework\Cache);
    },
    "Tworzenie egzemplarza klasy Cache w niezainicjowanym stanie",
    "Cache"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        return ($cache instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached inicjuje się",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        return ($cache->connect() instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached łączy się i zwraca self",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();
        $cache = $cache->disconnect();

        try
        {
            $cache->get("anything");
        }
        catch (Framework\Cache\Exception\Service $e)
        {
            return ($cache instanceof Framework\Cache\Driver\Memcached);
        }

        return false;
    },
    "Cache\Driver\Memcached rozłącza się i zwraca self",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();

        return ($cache->set("foo", "bar", 1) instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached ustawia wartości i zwraca siebie",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();

        return ($cache->get("foo") == "bar");
    },
    "Cache\Driver\Memcached pobiera wartości",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();

        return ($cache->get("404", "baz") == "baz");
    },
    "Cache\Driver\Memcached zwraca domyślne wartości",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();

        // Usypiamy, aby unieważnić powyższy 1-sekundowy bufor klucz-wartość
        sleep(1);

        return ($cache->get("foo") == null);
    },
    "Cache\Driver\Memcached wygasza wartości",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function()
    {
        $cache = new Framework\Cache(array(
            "type" => "memcached"
        ));

        $cache = $cache->initialize();
        $cache = $cache->connect();

        $cache = $cache->set("Witaj,", "świecie");
        $cache = $cache->erase("Witaj,");

        return ($cache->get("Witaj,") == null && $cache instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached usuwa wartości i zwraca self",
    "Cache\Driver\Memcached"
);
