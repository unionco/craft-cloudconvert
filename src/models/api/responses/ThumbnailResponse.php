<?php

namespace unionco\cloudconvert\models\api\responses;

class ThumbnailResponse extends AbstractResponse
{
    public string $id;
    public string $operation;
    public string $status;
    public string|null $message;
    public string $createdAt;
    public string $startedAt;
    public string $endedAt;
    public array $dependsOnTasks;
    public string $engine;
    public string $engineVersion;
    public array $payload;
    public string|null $result;

    protected static array $fieldsMap = [
        'createdAt' => 'created_at',
        'startedAt' => 'started_at',
        'endedAt' => 'ended_at',
        'dependsOnTask' => 'depends_on_tasks',
        'engineVersion' => 'engine_version'
    ];
}
