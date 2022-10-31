<?php

namespace unionco\cloudconvert\models\api\requests;

use craft\base\Model;

abstract class AbstractRequest extends Model
{
    /**
     * API handle is the key, model property is the value
     * @var array
     */
    protected array $apiFields;
    protected string $uri;
    protected bool $sync = false;
    protected string $method = 'post';

    public function getBody(): array
    {
        $body = [];
        foreach ($this->apiFields as $key => $handle) {
            $body[$key] = $this->{$handle};
        }
        return $body;
    }

    public function getSync(): bool
    {
        return $this->sync;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
