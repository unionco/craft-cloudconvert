<?php

namespace unionco\cloudconvert\models\api\requests;

use unionco\cloudconvert\models\api\requests\AbstractRequest;

class CreateThumbnailRequest extends AbstractRequest
{
    public string $uri = 'v2/thumbnail';

    public string|array $input;
    //public string|null $inputFormat;
    public string $outputFormat;
    // public string|null $engine;
    // public string|null $engineVersion;
    public string|null $filename;
    // public int|null $timeout;
    public int|null $width;
    public int|null $height;
    public string|null $fit;
    // public int|null $count;

    /** @inheritDoc */
    protected array $apiFields = [
        'input' => 'input',
        'output_format' => 'outputFormat',
        'filename' => 'filename',
        "width" => 'width',
        "height" => 'height',
        "fit" => "fit"
    ];

    public function rules(): array
    {
        return [
            ['input', 'outputFormat', 'filename', 'required']
        ];
    }
}
