<?php

class User
{
    public function __construct(String $username, String $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}