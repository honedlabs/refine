<?php

declare(strict_types=1);

namespace Honed\Refine\Stores;

use Illuminate\Contracts\Session\Session;

class SessionStore extends Store
{
    public const NAME = 'session';

    public function __construct(
        protected Session $session,
    ) {}

    /**
     * Retrieve the data from the store and put it in memory.
     *
     * @return $this
     */
    public function resolve()
    {
        /** @var array<string,mixed>|null $data */
        $data = $this->session->get($this->key, []);

        if (is_array($data)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Set the session to use for the store.
     *
     * @param  Session  $session
     * @return $this
     */
    public function session($session)
    {
        $this->session = $session;

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
            empty($this->data) => $this->session->forget($this->key),
            default => $this->session->put($this->key, $this->data),
        };
    }
}
