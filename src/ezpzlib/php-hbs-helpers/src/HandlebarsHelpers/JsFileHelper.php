<?php

namespace HandlebarsHelpers;

use Handlebars\Helper;
use Handlebars\Context;
use Handlebars\Template;

class JSFileHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if ($parsedArgs[0]) {
            return '<script src="'.$parsedArgs[0].'"></script>';
        }
        return '';
    }
}
