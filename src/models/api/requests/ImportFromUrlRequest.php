<?php

namespace unionco\cloudconvert\models\api\requests;

use unionco\cloudconvert\models\api\requests\AbstractRequest;

class ImportFromUrlRequest extends AbstractRequest
{
    protected string $uri = 'v2/import/url';

    public string $url;
    public string|null $filename;
    public array|null $headers;

    /** @inheritDoc */
    protected array $apiFields = [
        'url' => 'url',
        'filename' => 'filename',
        'headers' => 'headers',
    ];

    public function rules(): array
    {
        return [
            ['url', 'required']
        ];
    }
}
