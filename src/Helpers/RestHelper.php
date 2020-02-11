<?php

namespace PhpLab\Rest\Helpers;

use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Core\Legacy\Yii\Helpers\Inflector;
use PhpLab\Rest\Entities\RouteEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RestHelper
{

    public static function defineCrudRoutes(string $endpoint, string $controllerClassName): RouteCollection
    {
        $routeNamePrefix = self::extractRoutePrefix($controllerClassName);

        $endpoint = '/' . trim($endpoint, '/');
        $routes = new RouteCollection;

        $route = new Route($endpoint . '/{id}', ['_controller' => $controllerClassName, '_action' => 'view'], ['id'], [], null, [], [HttpMethodEnum::GET]);
        $routes->add($routeNamePrefix . '_view', $route);

        $route = new Route($endpoint . '/{id}', ['_controller' => $controllerClassName, '_action' => 'delete'], ['id'], [], null, [], [HttpMethodEnum::DELETE]);
        $routes->add($routeNamePrefix . '_delete', $route);

        $route = new Route($endpoint . '/{id}', ['_controller' => $controllerClassName, '_action' => 'update'], ['id'], [], null, [], [HttpMethodEnum::PUT]);
        $routes->add($routeNamePrefix . '_update', $route);

        $route = new Route($endpoint, ['_controller' => $controllerClassName, '_action' => 'index'], [], [], null, [], [HttpMethodEnum::GET]);
        $routes->add($routeNamePrefix . '_index', $route);

        $route = new Route($endpoint, ['_controller' => $controllerClassName, '_action' => 'create'], [], [], null, [], [HttpMethodEnum::POST]);
        $routes->add($routeNamePrefix . '_create', $route);
        return $routes;
    }

    public static function runAll(Request $request, RouteCollection $routes, array $controllers, $context = '/'): Response
    {
        try {
            $routeEntity = RestHelper::match($request, $routes, $context);
            $controllerInstance = $controllers[$routeEntity->controllerClassName];
            $response = RestHelper::run($controllerInstance, $request, $routes);
        } catch (ResourceNotFoundException $e) {
            $response = RestHelper::getResponseByStatusCode(HttpStatusCodeEnum::NOT_FOUND);
        }
        return $response;
    }

    private static function extractRoutePrefix(string $controllerClassName): string
    {
        $controllerClass = basename($controllerClassName);
        $controllerClass = str_replace('Controller', '', $controllerClass);
        $routeNamePrefix = Inflector::underscore($controllerClass);
        return $routeNamePrefix;
    }

    private static function run(object $controllerInstance, Request $request, RouteCollection $routes, $context = '/'): Response
    {
        $routeEntity = RestHelper::match($request, $routes, $context);
        $callback = [$controllerInstance, $routeEntity->actionName];
        try {
            $response = call_user_func_array($callback, $routeEntity->actionParameters);
        } catch (Throwable $e) {
            $response = RestHelper::handleException($e);
        }
        return $response;
    }

    private static function match(Request $request, RouteCollection $routes, $context = '/'): RouteEntity
    {
        $requestContext = new RequestContext($context);
        $matcher = new UrlMatcher($routes, $requestContext);
        $parameters = $matcher->match($request->getPathInfo());
        $routeEntity = new RouteEntity;
        $routeEntity->controllerClassName = $parameters['_controller'];
        $routeEntity->actionName = $parameters['_action'];
        if (in_array($routeEntity->actionName, ['view', 'update', 'delete'])) {
            $id = $parameters['id'];
            $routeEntity->actionParameters = [$id, $request];
        } elseif (in_array($routeEntity->actionName, ['index', 'create'])) {
            $routeEntity->actionParameters = [$request];
        }
        return $routeEntity;
    }

    private static function getResponseByStatusCode(int $statusCode): JsonResponse
    {
        $message = Response::$statusTexts[$statusCode];
        $data = ['message' => $message];
        $response = new JsonResponse($data, $statusCode);
        return $response;
    }

    private static function handleException(Throwable $exception): Response
    {
        if ($exception instanceof ResourceNotFoundException) {
            $response = self::getResponseByStatusCode(HttpStatusCodeEnum::NOT_FOUND);
        } elseif ($exception instanceof MethodNotAllowedException) {
            $response = self::getResponseByStatusCode(HttpStatusCodeEnum::METHOD_NOT_ALLOWED);
        } else {
            throw $exception;
        }
        return $response;
    }

}
