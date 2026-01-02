<?php

namespace App\Core;

class Router {
    private $routes = [];

    /**
     * Register a route
     * @param string $page The value of 'page' param
     * @param string $controller The Controller class name (without namespace)
     * @param string $defaultAction Default action to call if none provided
     * @param bool $protected Whether this route requires login
     */
    public function add($page, $controller, $defaultAction = 'index', $protected = true) {
        $this->routes[$page] = [
            'controller' => $controller,
            'default_action' => $defaultAction,
            'protected' => $protected
        ];
    }

    /**
     * Dispatch the request
     * @param string $page
     * @param string $action
     */
    public function dispatch($page, $action = null) {
        // Default to dashboard if page is empty
        if (empty($page)) $page = 'dashboard';

        // Check if route exists
        if (!array_key_exists($page, $this->routes)) {
            // Show error or 404
            echo "الصفحة غير موجودة: " . htmlspecialchars($page);
            return;
        }

        $route = $this->routes[$page];

        // Authorization Check
        if ($route['protected'] && !Session::isLoggedIn()) {
            redirect('index.php?page=login');
        }

        // Determine action
        $methodName = $action ?: $route['default_action'];

        // Instantiate Controller
        $controllerName = "App\\Controllers\\" . $route['controller'];
        
        if (!class_exists($controllerName)) {
            die("Controller class '$controllerName' not found.");
        }

        $controllerInfo = new $controllerName();

        // Check if method exists
        if (method_exists($controllerInfo, $methodName)) {
            $controllerInfo->$methodName();
        } else {
            // If specific action doesn't exist, fall back to default action
            // or show error. For now, fallback to default action
            $defaultAction = $route['default_action'];
             if (method_exists($controllerInfo, $defaultAction)) {
                $controllerInfo->$defaultAction();
            } else {
                die("Action '" . htmlspecialchars($methodName) . "' not found in " . htmlspecialchars($controllerName));
            }
        }
    }
}
