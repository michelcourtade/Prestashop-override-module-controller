<?php

class Dispatcher extends DispatcherCore
{
    /**
     * Load custom modules routes
     */
//     protected function loadRoutesModules() {
      
//         foreach(Module::getModulesDirOnDisk() As $module) {
//             if(Module::isInstalled($module)) {                
//                 require_once( _PS_MODULE_DIR_.$module.'/'.$module.'.php' );
//                 $reflect = new ReflectionClass($module);
//                 $default = $reflect->getDefaultProperties();
             	
      
//                 if (isset($default['moduleRoutes'])) {
//                     foreach ($default['moduleRoutes'] AS $routeName => $routes) {
//                         $this->default_routes[$routeName] = $routes;
//                     }
//                 }
//             }
//         }
//     }
    
//     protected function loadRoutes()
//     {
//         $this->loadRoutesModules();
//         parent::loadRoutes();
//     }

	public function dispatch()
	{
		$controller_class = '';
	
		// Get current controller
		$this->getController();
		if (!$this->controller)
			$this->controller = $this->default_controller;
	
		// Dispatch with right front controller
		if($this->front_controller == self::FC_MODULE) 
		{	
				// Dispatch module controller for front office
				$module_name = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
				$module = Module::getInstanceByName($module_name);
				$controller_class = 'PageNotFoundController';
				if (Validate::isLoadedObject($module) && $module->active)
				{
					$controllers = Dispatcher::getControllers(_PS_MODULE_DIR_.$module_name.'/controllers/front/');
					if (isset($controllers[$this->controller]))
					{
						// Require ModuleOverrie
						require_once 'ModuleFrontControllerOverride.php';
						// and load the right classes (child and mother or just the mother)
						ModuleFrontControllerOverride::load($module_name, $this->controller);
	
						//include_once(_PS_MODULE_DIR_.$module_name.'/controllers/front/'.$this->controller.'.php');
						$controller_class = $module_name.$this->controller.'ModuleFrontController';
					}
				}
				$params_hook_action_dispatcher = array('controller_type' => self::FC_FRONT, 'controller_class' => $controller_class, 'is_module' => 1);
				
				// Instantiate controller
				try
				{
					// Loading controller
					$controller = Controller::getController($controller_class);
				
					// Execute hook dispatcher
					if (isset($params_hook_action_dispatcher))
						Hook::exec('actionDispatcher', $params_hook_action_dispatcher);
				
					// Running controller
					$controller->run();
				}
				catch (PrestaShopException $e)
				{
					$e->displayMessage();
				}				
		}
		else {
			parent::dispatch();
		}	
	}
}

