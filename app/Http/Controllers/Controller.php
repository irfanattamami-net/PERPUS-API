<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Perpustakaan',
    version: '1.0.0',
    description: 'Dokumentasi REST API Sistem Informasi Perpustakaan menggunakan Laravel 12 dan Sanctum.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}