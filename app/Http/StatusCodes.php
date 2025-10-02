<?php

namespace App\Http;

class StatusCodes
{
    /**
     * Success responses
     */
    public const OK = 200;

    public const CREATED = 201;

    public const ACCEPTED = 202;

    public const NO_CONTENT = 204;

    /**
     * Redirection responses
     */
    public const MOVED_PERMANENTLY = 301;

    public const FOUND = 302;

    public const NOT_MODIFIED = 304;

    /**
     * Client error responses
     */
    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const METHOD_NOT_ALLOWED = 405;

    public const CONFLICT = 409;

    public const UNPROCESSABLE_ENTITY = 422;

    public const TOO_MANY_REQUESTS = 429;

    /**
     * Server error responses
     */
    public const INTERNAL_SERVER_ERROR = 500;

    public const BAD_GATEWAY = 502;

    public const SERVICE_UNAVAILABLE = 503;

    public const GATEWAY_TIMEOUT = 504;

    /**
     * Authentication specific status codes
     */
    public const AUTH_FAILED = self::UNAUTHORIZED;

    public const VALIDATION_FAILED = self::UNPROCESSABLE_ENTITY;

    public const RATE_LIMITED = self::TOO_MANY_REQUESTS;

    public const EMAIL_NOT_VERIFIED = self::FORBIDDEN;

    public const ACCOUNT_SUSPENDED = self::FORBIDDEN;
}
