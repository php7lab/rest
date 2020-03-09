<?php

namespace PhpLab\Rest\Helpers;

use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Rest\Entities\RouteEntity;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

class RestApiControllerHelper
{

    public static function send(RouteCollection $routeCollection, ContainerInterface $container, $context = '/', Request $request = null)
    {
        $request = $request ?? Request::createFromGlobals();
        $response = self::run($routeCollection, $container, $context);
        $response->send();
    }

    public static function run(RouteCollection $routeCollection, ContainerInterface $container, $context = '/', Request $request = null): Response
    {
        $request = $request ?? Request::createFromGlobals();
        $response = RestApiControllerHelper::runAll($request, $routeCollection, $container, $context);
        return $response;
    }

    private static function runAll(Request $request, RouteCollection $routeCollection, ContainerInterface $container, $context = '/'): Response
    {
        try {
            $routeEntity = self::match($request, $routeCollection, $context);
            $controllerInstance = $container->get($routeEntity->controllerClassName);
            $response = self::runController($controllerInstance, $request, $routeCollection);
        } catch (ResourceNotFoundException $e) {
            $response = self::getResponseByStatusCode(HttpStatusCodeEnum::NOT_FOUND);
        }
        return $response;
    }

    private static function runController(object $controllerInstance, Request $request, RouteCollection $routeCollection, $context = '/'): Response
    {
        $routeEntity = self::match($request, $routeCollection, $context);
        $callback = [$controllerInstance, $routeEntity->actionName];
        try {
            $response = call_user_func_array($callback, $routeEntity->actionParameters);
        } catch (Throwable $e) {
            $response = self::handleException($e);
        }
        return $response;
    }

    private static function match(Request $request, RouteCollection $routeCollection, $context = '/'): RouteEntity
    {
        $requestContext = new RequestContext($context);
        $matcher = new UrlMatcher($routeCollection, $requestContext);
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
