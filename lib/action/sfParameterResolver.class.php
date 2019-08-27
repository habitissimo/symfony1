<?php

class sfParameterResolver
{
  private $container;
  private $request;
  private $component;

  public function __construct(sfServiceContainer $container = null)
  {
    $this->container = $container;
  }

  public function setRequest(sfWebRequest $request)
  {
    $this->request = $request;

    return $this;
  }

  public function setComponent(sfComponent $component)
  {
    $this->component = $component;

    return $this;
  }

  protected function resolveParams($actionToRun)
  {
    $reflection = new ReflectionObject($this->component);
    $method = $reflection->getMethod($actionToRun);

    $parameters = [];
    foreach ($method->getParameters() as $i => $param) {
      $type = $param->getType();

      // handle case where request parameter was not type hinted
      if (null === $type && $i === 0) {
        $parameters[] = $this->request;
        continue;
      }

      if (null === $type) {
        throw new \Exception("Aditional parameters must be type hinted");
      }

      if ($type->getName() == "sfWebRequest") {
        $parameters[] = $this->request;
      } else {
        $parameters[] = $this->getParamFromContainer($type->getName());
      }
    }

    return $parameters;
  }

  protected function getParamFromContainer($param)
  {
    return $this->container->getService($param);
  }

  public function execute($actionToRun = 'execute')
  {
    return call_user_func_array([$this->component, $actionToRun], $this->resolveParams($actionToRun));
  }
}
