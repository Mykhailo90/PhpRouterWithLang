<?php

class Router
{
    protected $routes = [];

    public static function load($file)
    {
        $router = new static;

        require $file;

        return $router;
    }

    public function define($routes)
    {
        $this->routes = $routes;
    }

    public function direct($uri)
    {
        $baseUri = $uri;
        $extraOptions = $this->getExtraOptions($uri);

        if (array_key_exists($uri, $this->routes)) {
            $_REQUEST['extraOptions'] = $extraOptions;
            if ($this->isNeedRedirect($baseUri, $uri)){
                $this->makeRedirect($uri);
            }

            return $this->routes[$uri];
        }

        die('No route defined for this URI.');
    }

    private function isNeedRedirect($baseUri, $uri)
    {
        $country = $_REQUEST['extraOptions']['country'];
        $lang = $_REQUEST['extraOptions']['lang'];

        if ($lang == $country && $baseUri == trim($country.'/'.$lang.'/'.$uri, '/'))
            return false;

        if ($lang == $country && $baseUri == trim($country.'/'.$uri, '/'))
            return false;

        if ($lang != $country && $baseUri == trim($country.'/'.$lang.'/'.$uri, '/'))
            return false;

        return true;
    }

    private function getExtraOptions(&$uri)
    {
        $app = require 'config.php';
        $uriArray = explode('/', $uri);

        $params = require 'countryLanguageCodes.php';
        $languages = $params['lang'];
        $countries = $params['countries'];

        $country = array_shift($uriArray);

        if ($this->isParamExists($country, $countries))
        {
            $extraOptions['country'] = $country;
            $uri = implode($uriArray, '/');

            $lang = array_shift($uriArray);

            if ($this->isParamExists($lang, $languages)){
                $extraOptions['lang'] = $lang;
                $uri = implode($uriArray, '/');
            }
            else {
                $extraOptions['lang'] = $extraOptions['country'];
            }

        }
        else {
            $extraOptions['country'] =  $extraOptions['lang'] = $app['country'];
        }

        return $extraOptions;
    }

    private function isParamExists($param, $resources)
    {
        foreach ($resources as $item){
            if ($param == $item)
                return true;
        }

        return false;
    }

    private function makeRedirect($uri)
    {

        $newPathWithLang = ($_SERVER['HTTP_HOST'] .'/'.
            $_REQUEST['extraOptions']['country']. '/'.
            $_REQUEST['extraOptions']['lang'] . '/'.
            $uri);

        $newPathCountry = ($_SERVER['HTTP_HOST'] .'/'.
            $_REQUEST['extraOptions']['country']. '/'.
            $uri);

        $newPath = (  $_REQUEST['extraOptions']['country'] == $_REQUEST['extraOptions']['lang']) ?
            $newPathCountry : $newPathWithLang;

        header('Location: http://'.$newPath);
    }
}