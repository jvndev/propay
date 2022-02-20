<?php

class Configuration
{
    private const CONFIG_FILE = __DIR__.'../../../config.cnf';

    private static $_instance;

    private function __construct()
    {
        $this->check();
        $this->parse();
    }

    private function check(): void
    {
        if (!file_exists(Configuration::CONFIG_FILE)) {
            throw new \Exception('Could not find ' . Configuration::CONFIG_FILE);
        }

        if (!$fhandle = fopen(Configuration::CONFIG_FILE, 'r')) {
            throw new \Exception('Could not read ' . Configuration::CONFIG_FILE);
        }

    }

    private function parse(): void
    {
        $fhandle = fopen(__DIR__.'../../../config.cnf', 'r');

        while ($line = fgets($fhandle)) {
            $line = trim($line);

            if (preg_match('/#/', $line) || $line == "") {
                continue;
            }

            preg_match('/^(\w*)\s+"?([^"]*)"?$/', $line, $matches);

            if (count($matches) != 3) {
                throw new \Exception('Invalid configuration file (check syntax)');
            }

            $key = $matches[1];
            $value = $matches[2];

            $this->$key = $value;
        }

        fclose($fhandle);
    }

    public static function get(string $key): string
    {
        if (!isset(Configuration::$_instance)) {
            Configuration::$_instance = new Configuration();
        }

        if (!isset(Configuration::$_instance->$key)) {
            throw new \Exception("$key not set in config.cnf");
        }

        return Configuration::$_instance->$key;
    }
}