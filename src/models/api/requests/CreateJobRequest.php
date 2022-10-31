<?php

namespace unionco\cloudconvert\models\api\requests;

use unionco\cloudconvert\models\api\requests\AbstractRequest;

class CreateJobRequest extends AbstractRequest
{
    /*
    {
        "tasks": {
            "import-my-file": {
                "operation": "import/s3"
            },
            "convert-my-file": {
                "operation": "convert",
                "input": "import-my-file",
                "input_format": "docx",
                "output_format": "pdf",
                "page_range": "1-2",
                "optimize_print": true
            },
            "export-my-file": {
                "operation": "export/s3",
                "input": "convert-my-file"
            }
        },
        "tag": "myjob-123"
    }
    */

    protected string $uri = 'v2/jobs';

    public array|null $tasks;

    /** @inheritDoc */
    protected array $apiFields = [
        'tasks' => 'tasks',
    ];

    public function rules(): array
    {
        return [
            ['tasks', 'required']
        ];
    }
}
