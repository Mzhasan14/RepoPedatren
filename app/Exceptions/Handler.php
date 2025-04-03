<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * Daftar exception yang tidak perlu dilaporkan.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * Daftar input yang tidak disimpan pada session saat validasi gagal.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Registrasi exception handling.
     *
     * @return void
     */
    public function register()
    {
        // Custom handler untuk AuthenticationException
        $this->renderable(function (AuthenticationException $exception, $request) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        });

        // Anda bisa menambahkan renderable lainnya di sini sesuai kebutuhan
    }

    /**
     * Menangani exception yang tidak tertangani secara spesifik.
     *
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
