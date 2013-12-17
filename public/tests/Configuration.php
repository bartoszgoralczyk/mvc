<?php

Framework\Test::add(
    function()
    {
        $configuration = new Framework\Configuration();
        return ($configuration instanceof Framework\Configuration);
    },
    "Tworzenie egzemplarza konfiguracji w niezainicjowanym stanie",
    "Configuration"
);

Framework\Test::add(
    function()
    {
        $configuration = new Framework\Configuration(array(
            "type" => "ini"
        ));

        $configuration = $configuration->initialize();
        return ($configuration instanceof Framework\Configuration\Driver\Ini);
    },
    "Inicjacja Configuration\Driver\Ini",
    "Configuration\Driver\Ini"
);

Framework\Test::add(
    function()
    {
        $configuration = new Framework\Configuration(array(
            "type" => "ini"
        ));

        $configuration = $configuration->initialize();
        $parsed = $configuration->parse("_configuration");

        return ($parsed->config->first == "hello" && $parsed->config->second->second == "bar");
    },
    "Configuration\Driver\Ini przetwarza pliki konfiguracyjne",
    "Configuration\Driver\Ini"
);
