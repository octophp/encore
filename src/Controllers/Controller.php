<?php

namespace Octo\Encore\Controllers;

use Laminas\Diactoros\Response\JsonResponse;

class Controller
{
    public function view(array $data): JsonResponse
    {
        return new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
    }
}