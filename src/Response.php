<?php

namespace Frankie\Response;

use Ds\Collection;
use Ds\Map;
use Ds\Vector;
use InvalidArgumentException;
use OutOfBoundsException;
use Traversable;
use UnexpectedValueException;

class Response implements ResponseInterface
{
    public const CONTINUE = 100;
    public const SWITCHING_PROTOCOL = 101;
    public const PROCESSING = 102;
    public const EARLY_HINTS = 101;
    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NO_CONTENT = 204;
    public const RESET_CONTENT = 205;
    public const PARTIAL_CONTENT = 206;
    public const MULTI_STATUS = 207;
    public const ALREADY_REPORTED = 208;
    public const IM_USED = 226;
    public const MULTIPLE_CHOICES = 300;
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const NOT_MODIFIED = 304;
    public const USE_PROXY = 305;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const PAYMENT_REQUIRED = 402;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT = 408;
    public const CONFLICT = 409;
    public const GONE = 410;
    public const LENGTH_REQUIRED = 411;
    public const PRECONDITION_FAILED = 412;
    public const PAYLOAD_TOO_LARGE = 413;
    public const URI_TOO_LONG = 414;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const RANGE_NOT_SATISFIABLE = 416;
    public const EXPECTATION_FAILED = 417;
    public const MISDIRECTED_REQUEST = 421;
    public const UNPROCESSABLE_ENTITY = 422;
    public const LOCKED = 423;
    public const FAILED_DEPENDENCY = 424;
    public const TOO_EARLY = 425;
    public const UPGRADE_REQUIRED = 426;
    public const PRECONDITION_REQUIRED = 428;
    public const TOO_MANY_REQUESTS = 429;
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const VARIANT_ALSO_NEGOTIATES = 506;
    public const INSUFFICIENT_STORAGE = 507;
    public const LOOP_DETECTED = 508;
    public const NOT_EXTENDED = 510;
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];
    protected int $statusCode;
    protected string $reasonPhrase;
    protected ?string $body;
    protected Collection $headers;

    public function __construct()
    {
        $this->statusCode = 200;
        $this->reasonPhrase = 'OK';
        $this->body = null;
        $this->headers = new Map();
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    public function send(): void
    {
        ob_start();
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendBody();
        ob_end_flush();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        if ($code < 100 || $code >= 600) {
            throw new InvalidArgumentException(
                printf('The HTTP status code "%s" is not valid.', $code)
            );
        }
        $this->statusCode = $code;
        if ($reasonPhrase === '') {
            $this->reasonPhrase = self::$statusTexts[$code] ?? 'unknown status';
        } else {
            $this->reasonPhrase = $reasonPhrase;
        }
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getHeaders(): Map
    {
        return clone $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                return true;
            }
        }
        return false;
    }

    public function getHeader(string $name): Vector
    {
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                return $this->headers[$key];
            }
        }
        throw new OutOfBoundsException("Undefined header name: $name");
    }

    public function getHeaderLine(string $name): string
    {
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                return $this->headers[$key]->join(', ');
            }
        }
        throw new OutOfBoundsException("Undefined header name: $name");
    }

    public function withHeader(string $name, $value, bool $keyChanging = false): self
    {
        if (!$this->isCorrectType($value)) {
            throw new UnexpectedValueException(
                'The value must be a string, number,array or object implementing 
                Traversable, ' . \gettype($value) . ' given.'
            );
        }
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                if ($keyChanging) {
                    $this->headers->remove($key);
                    $this->headers[$name] = new Vector();
                    $this->pushToHeaders($name, $value);
                } else {
                    $this->headers[$key] = new Vector();
                    $this->pushToHeaders($key, $value);
                }
                return $this;
            }
        }
        $this->headers[$name] = new Vector();
        $this->pushToHeaders($name, $value);
        return $this;
    }

    public function withAddedHeader(
        string $name, $value, bool $keyChanging = false
    ): ResponseInterface
    {
        if (!$this->isCorrectType($value)) {
            throw new UnexpectedValueException(
                'The value must be a string, number,array or object implementing 
                Traversable, ' . \gettype($value) . ' given.'
            );
        }
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                if ($keyChanging) {
                    $actual = $this->headers[$key]->copy();
                    $this->headers->remove($key);
                    $this->headers[$name] = new Vector();
                    $this->updateHeader($name, $actual, $value);
                } else {
                    $this->pushToHeaders($key, $value);
                }
                return $this;
            }
        }
        $this->headers[$name] = new Vector();
        $this->pushToHeaders($name, $value);
        return $this;
    }

    protected function isCorrectType($value): bool
    {
        return is_numeric($value) || \is_string($value) || \is_array(
                $value
            ) || ($value instanceof Traversable);
    }

    public function withoutHeader(string $name): self
    {
        foreach ($this->headers->keys() as $key) {
            if (strtolower($key) === strtolower($name)) {
                $this->headers->remove($key);
            }
        }
        return $this;
    }

    /**
     * @param mixed $body that can be cast to string
     *
     * @return Response
     */
    public function withBody($body): self
    {
        if (
            $body !== null && !\is_string($body) && !is_numeric($body) && !\is_callable(
                [
                    $body,
                    '__toString'
                ]
            )
        ) {
            throw new UnexpectedValueException(
                'The Response content must be a string or object 
                implementing __toString(), "' . \gettype($body) . '" given.'
            );
        }
        $this->body = (string)$body;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    protected function sendStatus(): void
    {
        http_response_code($this->statusCode);
        header("Status: $this->statusCode $this->reasonPhrase");
    }

    protected function sendHeaders(): void
    {
        foreach ($this->headers as $key => $val) {
            $value = $val->join('; ');
            header($key . ': ' . $value);
        }
    }

    protected function sendBody(): void
    {
        echo $this->body;
    }

    protected function pushToHeaders(string $name, $value): void
    {
        if (\is_string($value)) {
            $this->headers[$name]->push($value);
        } else {
            /** @var array|Traversable $val */
            foreach ($value as $val) {
                $this->headers[$name]->push($val);
            }
        }
    }

    protected function updateHeader(string $name, $actual, $value): void
    {
        if (\is_string($actual)) {
            $this->headers[$name]->push($actual);
        } else {
            /** @var array|Traversable $actual */
            foreach ($actual as $val) {
                $this->headers[$name]->push($val);
            }
        }
        if (\is_string($value)) {
            $this->headers[$name]->push($value);
        } else {
            /** @var array|Traversable $value */
            foreach ($value as $val) {
                $this->headers[$name]->push($val);
            }
        }
    }
}
