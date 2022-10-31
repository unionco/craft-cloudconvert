<?php

namespace unionco\cloudconvert\models\api\responses;

use Psr\Http\Message\ResponseInterface;
use unionco\cloudconvert\models\api\responses\AbstractResponse;

class ShowATaskResponse extends AbstractResponse
{
    public string $id = '';
    public string|int|null $jobId = null;
    public string $status = '';
    public string $operation = '';
    public int|null $percent = 0;
    public int|null $priority = 0;
    public string|null $hostname = null;
    public string|null $message = null;
    public string|null $code = null;
    public int|null $credits = null;
    public string|null $createdAt = null;
    public string|null $startedAt = null;
    public string|null $endedAt = null;
    public $dependsOnTaskIds;
    public string|null $retryOfTaskId = null;
    public string|null $copyOfTaskId = null;
    public string|null $userId = null;
    public string|null $storage = null;
    public string|null $tag = null;
    public array $tasks;
    public $result;
    public array|null $links = null;

    protected static array $fieldsMap = [
        'jobId' => 'job_id',
        'userId' => 'user_id',
        'createdAt' => 'created_at',
        'startedAt' => 'started_at',
        'endedAt' => 'ended_at',
        'dependsOnTaskIds' => 'depends_on_task_ids',
        'retryOfTaskId' => 'retry_of_task_id',
        'copyOfTaskId' => 'copy_of_task_id',
        'hostname' => 'host_name',
    ];
}
