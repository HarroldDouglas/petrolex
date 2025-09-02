<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Petrolex API",
 *     version="1.0",
 *     description="Documentation API pour Petrolex",
 *
 *     @OA\Contact(
 *         email="test@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiInfo {}
