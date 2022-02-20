<?php

class Language
{
    public function __construct(int $id, string $language)
    {
        $this->id = $id;
        $this->language = $language;
    }
}