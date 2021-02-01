<?php

namespace Frankie\Response;

use ArrayAccess;
use Traversable;

interface ResponseInterface
{
    public function __construct();

    public function send(): void;

    /**
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return ResponseInterface
     */
    public function withStatus(int $code, string $reasonPhrase = ''): self;

    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return string
     */
    public function getReasonPhrase(): string;

    /**
     * @return string[][]|ArrayAccess<ArrayAccess<string>>
     */
    public function getHeaders();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader(string $name): bool;

    /**
     * @param string $name
     *
     * @return string[]|ArrayAccess<string>
     */
    public function getHeader(string $name);

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine(string $name): string;

    /**
     * @param string $name
     * @param number|string|array|Traversable $value
     * @param bool $keyChanging
     *
     * @return ResponseInterface
     */
    public function withHeader(string $name, $value, bool $keyChanging = false): self;

    /**
     * @param string $name
     * @param number|string|array|Traversable $value
     * @param bool $keyChanging
     *
     * @return ResponseInterface
     */
    public function withAddedHeader(string $name, $value, bool $keyChanging = false): self;

    /**
     * @param string $name
     *
     * @return ResponseInterface
     */
    public function withoutHeader(string $name): self;

    /**
     * @param mixed $body that can be cast to string
     *
     * @return ResponseInterface
     */
    public function withBody($body): self;

    /**
     * @return string
     */
    public function getBody(): string;
}
