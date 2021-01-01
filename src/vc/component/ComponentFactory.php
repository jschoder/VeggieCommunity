<?php
namespace vc\component;

class ComponentFactory
{
    private $db;

    private $server;

    private $modelFactory;

    private $components;

    public function __construct($db, $server, $modelFactory)
    {
        $this->db = $db;
        $this->server = $server;
        $this->modelFactory = $modelFactory;
        $this->components = new \stdClass();
    }

    /**
     * @return \vc\component\AbstractComponent
     */
    public function getComponent($componentName)
    {
        if (!isset($this->components->$componentName)) {
            $componentClassName = '\\vc\\component\\' . $componentName . 'Component';
            if (!class_exists($componentClassName)) {
                throw new \vc\exception\FatalSystemException('Component ' . $componentName. ' can\'t be created.');
            }
            $this->components->$componentName = new $componentClassName();
            $this->components->$componentName->setDb($this->db);
            $this->components->$componentName->setServer($this->server);
            $this->components->$componentName->setModelFactory($this->modelFactory);
            $this->components->$componentName->setComponentFactory($this);
        }
        return $this->components->$componentName;
    }
}
