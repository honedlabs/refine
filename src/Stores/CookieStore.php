<?php

declare(strict_types=1);

namespace Honed\Refine\Stores;

use Illuminate\Contracts\Session\Session;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;

class CookieStore extends Store
{
    public const NAME = 'cookie';

    /**
     * The default lifetime for the cookie.
     *
     * @var int
     */
    protected $lifetime = 31536000;

    public function __construct(
        protected CookieJar $cookieJar,
        protected Request $request,
    ) {}

    /**
     * Retrieve the data from the store and store it in memory.
     *
     * @return $this
     */
    public function resolve()
    {
        /** @var array<string,mixed>|null $data */
        $data = json_decode(
            $this->request->cookie($this->key, '[]'), true // @phpstan-ignore argument.type
        );

        if (is_array($data)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Set the request to use for the store.
     *
     * @return $this
     */
    public function request(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set the cookie jar to use for the store.
     *
     * @return $this
     */
    public function cookieJar(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;

        return $this;
    }

    /**
     * Set the lifetime for the cookie.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function lifetime($seconds)
    {
        $this->lifetime = $seconds;

        return $this;
    }

    /**
     * Persist the data to the session.
     *
     * @return void
     */
    public function persist()
    {
        match (true) {
            empty($this->data) => $this->cookieJar->forget($this->key),
            default => $this->cookieJar->queue(
                $this->key, json_encode($this->data), $this->lifetime
            ),
        };
    }
}
