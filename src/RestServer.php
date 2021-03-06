<?php

namespace Alexdevid;

/**
 * @author Alexander Devid <kerdevid@gmail.com>
 * @since 1.0
 */
class RestServer
{

	/**
	 * @var \Alexdevid\Request
	 */
	private $request;

	/**
	 * @var \Alexdevid\Response
	 */
	private $response;

	/**
	 * @var string Controller namespace
	 */
	public $controllerNamespace = "";

	/**
	 *
	 */
	public function __construct()
	{
		$this->request = new Request;
		$this->response = new Response;
	}

	public function run()
	{
		if ($this->isRestRequest()) {
			$this->processRequest();
		} else {
			$this->response->send();
		}
	}

	/**
	 * @return \Alexdevid\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return \Alexdevid\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @return ControllerClassName or Null
	 */
	public function getController()
	{
		$controllerName = $this->getControllerName();
		return class_exists($controllerName) ? new $controllerName($this->request) : NULL;
	}

	private function getControllerName()
	{
		return '\\' . $this->controllerNamespace . '\\' . ucfirst(explode('/', $this->request->uri)[1]) . 'Controller';
	}

	/**
	 *
	 */
	public function processRequest()
	{
		ini_set("allow_url_fopen", true);
		$this->request
				->setUri()
				->setMethod(filter_input(INPUT_SERVER, "REQUEST_METHOD"))
				->setId($this->request->getIdFromUri())
				->setData($this->request->substractData());

		$controller = $this->getController();
		$actionName = strtolower($this->request->method);

		if ($controller) {
			$this->response->content = $controller->$actionName();
			$this->response->setCode($controller->getResponseStatusCode());
			$this->response->send();
		} else {
			$this->response->error("Controller <" . $this->getControllerName() . "> does not exist or not loaded", 500);
		}
	}

	/**
	 * @param string $uri Full request url
	 * @return boolean
	 */
	public function isRestRequest($uri = NULL)
	{
		if ($uri) {
			return strpos($uri, $this->request->prefix) ? true : false;
		} else {
			return strpos(filter_input(INPUT_SERVER, 'REQUEST_URI'), $this->request->prefix) ? true : false;
		}
	}

}
