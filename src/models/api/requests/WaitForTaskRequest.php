<?php

namespace unionco\cloudconvert\models\api\requests;

use unionco\cloudconvert\models\api\requests\AbstractRequest;

class WaitForTaskRequest extends AbstractRequest
{
    protected string $method = 'get';
    protected bool $sync = true;

    public string $id;

    public function getUri(): string
    {
        return 'v2/jobs/' . $this->id;
    }
}
