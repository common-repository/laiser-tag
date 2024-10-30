<?php

namespace LTOC;

class Templates
{
    public function __call($template, $arguments)
    {
    	include_once LTOC_TEMPLATES . $template . '.php';
    }
}
