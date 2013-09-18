<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcusamatos
 * Date: 18/09/13
 * Time: 10:31
 * To change this template use File | Settings | File Templates.
 */

namespace JvsRequire\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Zend\Stdlib\ArrayUtils;

class JvsRequireHelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    protected $serviceLocator;

    protected $config;

    protected $autoload;
    protected $components;

    protected $loaded = array();

    public function __invoke(array $autoload = array(), $loadAll = false) {
        if(!isset($this->config)){
            $this->config = $this->getServiceLocator()->getServiceLocator()->get('Config');
            $this->config = $this->config['jvsrequire'];
        }

        $this->autoload = isset($this->config['autoload']) ? $this->config['autoload'] : array();
        $this->components = isset($this->config['components']) ? $this->config['components'] : array();

        $this->autoload =  ArrayUtils::merge($this->autoload, \JvsRequire::getAutoLoad());
        $this->autoload =  ArrayUtils::merge($this->autoload, $autoload);


        if($loadAll)
        {
            foreach($this->config['components'] as $var => $value)
            {
                if(!in_array($var, $this->autoload))
                {
                    $this->autoload[] = $var;
                }
            }
        }

        foreach($this->autoload as $componentName)
        {
            $this->loadComponent($componentName);
        }

        $this->view->headScript()->prependScript('var base_path ="'.$this->view->basePath().'"');
    }

    protected function loadComponent($componentName)
    {
        if(in_array($componentName, $this->loaded))
            return false;

        //echo $componentName . '<br>';
        if(!isset($this->components[$componentName]))
            throw new \Exception('Component "'.$componentName.'" not configured in JvsRequired');

        $component = $this->components[$componentName];
        if(isset($component['components']))
        {
            foreach($component['components'] as $componentDepend){
                $this->loadComponent($componentDepend);
            }
        }

        if(isset($component['js']))
        {
            if(is_string($component['js']))
            {
                $jsLink = $component['js'];
                if(isset($component['conditional']))
                {
                    $this->view->headScript()->appendFile($this->view->basePath($jsLink ), 'text/javascript', array('conditional'=>$component['conditional']));
                }else{
                    $this->view->headScript()->appendFile($this->view->basePath($jsLink ), 'text/javascript');
                }

                //echo $component['js'] . '<br>';
            }else if(is_array($component['js']))
            {
                foreach($component['js'] as $jsLink)
                {
                    if(isset($component['conditional']))
                    {
                        $this->view->headScript()->appendFile($this->view->basePath($jsLink ), 'text/javascript', array('conditional'=>$component['conditional']));
                    }else{
                        $this->view->headScript()->appendFile($this->view->basePath($jsLink ), 'text/javascript');
                    }
                }
            }

        }
        if(isset($component['css']))
        {
            //css
            if(is_string($component['css']))
            {
                $cssLink = $component['css'];
                if(isset($component['conditional']))
                {
                    $this->view->headLink()->appendStylesheet($this->view->basePath($cssLink ));
                }else{
                    $this->view->headLink()->appendStylesheet($this->view->basePath($cssLink ));
                }

                //echo $component['js'] . '<br>';
            }else if(is_array($component['css']))
            {

            }
        }

        $this->loaded[] = $componentName;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}