<?php

namespace unionco\cloudconvert\services;

use Craft;
use Closure;
use DateTime;
use RuntimeException;
use GuzzleHttp\Client;
use craft\helpers\Json;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\Assets as AssetsHelper;
use craft\feedme\helpers\AssetHelper;
use craft\helpers\FileHelper;
use craft\queue\QueueInterface;
use unionco\cloudconvert\Plugin;
use yii\base\InvalidConfigException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use unionco\cloudconvert\models\api\requests\AbstractRequest;
use unionco\cloudconvert\models\api\requests\CreateJobRequest;
use unionco\cloudconvert\models\api\responses\ThumbnailResponse;
use unionco\cloudconvert\models\api\requests\WaitForTaskRequest;
use unionco\cloudconvert\models\api\responses\ShowATaskResponse;
use unionco\cloudconvert\models\api\requests\ImportFromUrlRequest;
use unionco\cloudconvert\models\api\requests\CreateThumbnailRequest;
use unionco\cloudconvert\models\Settings;

class CloudConvert extends Component
{
    public const ASYNC_BASE_URL = 'https://api.cloudconvert.com';
    public const SYNC_BASE_URL = 'https://sync.api.cloudconvert.com';
    public const ASYNC_SANDBOX_BASE_URL = 'https://api.sandbox.cloudconvert.com';
    public const SYNC_SANDBOX_BASE_URL = 'https://sync.api.sandbox.cloudconvert.com';

    protected Client|null $client = null;

    protected string $logPath = '';
    protected string $bearerTokenString = '';
    protected bool|null $sandbox = null;
    protected Closure|null $progressCallback;

    public function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }
        if (!$this->bearerTokenString) {
            /** @var Settings */
            $settings = Plugin::getInstance()->getSettings();
            /** @var string */
            $apiKey = $settings->apiKey;
            $this->bearerTokenString = "Bearer $apiKey";
        }
        if (is_null($this->sandbox)) {
            /** @var Settings */
            $settings = Plugin::getInstance()->getSettings();
            /** @var bool */
            $sandbox = $settings->sandbox;
            $this->sandbox = $sandbox;
        }
        return $this->client;
    }

    /**
     * High-level public method - Generate a Craft
     * Asset from another Craft Asset.
     * 
     * @param Asset &$a - The source asset, which will be used to generate a thumbnail
     * @param Closure $callback - Optional closure to report queue job progress
     * @return Asset|null if not null, this is the thumbnail Asset
     */
    public function getThumbnail(Asset &$a, Closure $callback = null): ?Asset
    {
        // First, check if the plugin should operate in this environment.
        /** @var Settings */
        $settings = Plugin::getInstance()->getSettings();
        /** @var bool */
        $enabled = $settings->enabled;
        if (!$enabled) {
            throw new RuntimeException('Plugin is not enabled for this environment');
        }

        $this->progressCallback = $callback;

        $result = $this->createJobTask($a);
        $url = $result->tasks[0]["result"]["files"][0]["url"];
        $filename = $result->tasks[0]["result"]["files"][0]["filename"];
        //$result = $this->waitForTaskToComplete($result, 'Job');

        $asset = $this->createThumbnailAsset($url, $filename);

        // 1 - Upload the file to CC, 'Upload Task'
        /*
        $result = $this->createUploadTask($a);
        $this->setQueueProgress(.2, 'Uploading original file to CloudConvert');

        // 2 - Wait for the task to complete
        $result = $this->waitForTaskToComplete($result, 'Upload');
        $this->setQueueProgress(.3, 'CloudConvert upload complete');

        // 3 - Create the thumbnail task
        $result = $this->createThumbnailTask($a, $result);
        $this->setQueueProgress(.4, 'Generating thumbnail based on original');

        // 4 - Wait for the task to complete
        $result = $this->waitForTaskToComplete($result, 'Thumbnail');
        $this->setQueueProgress(.5, 'Thumbnail generation complete');

        // 5 - Download file
        $this->setQueueProgress(.6, 'Downloading thumbnail from CloudConvert');
        */
        //$bob->debug()
        // THIS IS TEMPORARY
        // return an asset we know is an image
        //return Asset::find()
        //    ->id(735031)
        //    ->one();
        // return null;

        return $asset;
    }

    //
    // Protected Methods
    //

    /**
     * Download the thumbnail from CC and create a Craft Asset
     *
     * @param string $url
     * @param string $filename
     * @return Asset
     */
    protected function createThumbnailAsset(string $url, string $filename): Asset
    {
        $this->log("Starting File Download: " . $url);

        /** @var Settings */
        $settings = Plugin::getInstance()->getSettings();
        /** @var int|null */
        $folderUid = $settings->thumbnailFolderUid;
        $folderId = Craft::$app->getAssets()->getFolderByUid($folderUid);
        if (is_null($folderId)) {
            throw new InvalidConfigException('No folderId specified');
        }

        $tempThumbnailPath = Craft::$app->getPath()->getTempPath() . '/thumbnails/';
        if (!is_dir($tempThumbnailPath)) {
            FileHelper::createDirectory($tempThumbnailPath);
        }

        $filename = AssetsHelper::prepareAssetName($filename, true);
        $fetchedImage = $tempThumbnailPath . $filename;

        AssetHelper::downloadFile($url, $fetchedImage);

        $asset = new Asset();
        $asset->tempFilePath = $fetchedImage;
        $asset->filename = $filename;
        $asset->folderId = $folderId;
        $asset->kind = "Image";
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        $asset->validate();
        Craft::$app->getElements()->saveElement($asset, false);

        $this->log("Asset saved: " . $asset->id);

        return $asset;
    }

    /**
     * Helper method to report queue progress
     *
     * @param float $progress
     * @param string $message
     * @return void
     */
    protected function setQueueProgress(float $progress, string $message): void
    {
        if (!$this->progressCallback) {
            return;
        }
        call_user_func($this->progressCallback, $progress, $message);
    }

    /**
     * Create a CC Job
     *
     * @param Asset $inputFile
     * @return ShowATaskResponse
     */
    protected function createJobTask(Asset &$inputFile): ShowATaskResponse
    {
        $this->log("Creating CC Job");
        $originalFilename = $inputFile->getFilename(false);
        $targetFilename = "{$originalFilename}-thumbnail.png";

        /** @var Settings */
        $settings = Plugin::getInstance()->getSettings();
        $width = $settings->thumbnailWidth;
        $height = $settings->thumbnailHeight;
        $fit = $settings->thumbnailFit;

        $req = new CreateJobRequest([
            'tasks' => [
                'import-url' => [
                    'operation' => 'import/url',
                    'url' => $inputFile->getUrl(),
                    'filename' => $inputFile->getFilename(),
                    'headers' => [],
                ],
                'thumbnail' => [
                    'operation' => 'thumbnail',
                    'input' => 'import-url',
                    'output_format' => 'png',
                    'filename' => $targetFilename,
                    'width' => $width,
                    'height' => $height,
                    'fit' => $fit,
                ],
                'export-url' => [
                    "operation" => 'export/url',
                    "input" => "thumbnail",
                    "archive_multiple_files" => false
                ]
            ]
        ]);
        $resp = $this->makeGuzzleRequest($req);
        $respModel = ShowATaskResponse::create($resp);
        $this->log("Upload Task Created", $respModel->getAttributes());
        return $respModel;
    }

    /**
     * Import the original into CC
     *
     * @param Asset $inputFile
     * @return ShowATaskResponse
     */
    protected function createUploadTask(Asset &$inputFile): ShowATaskResponse
    {
        $this->log("Starting Upload Task");
        $req = new ImportFromUrlRequest([
            'url' => $inputFile->getUrl(),
            'filename' => $inputFile->getFilename(),
            'headers' => [],
        ]);
        $resp = $this->makeGuzzleRequest($req);
        $respModel = ShowATaskResponse::create($resp);
        $this->log("Upload Task Created", $respModel->getAttributes());
        return $respModel;
    }

    /**
     * This method is used for multiple 'stages'.
     * Takes a ShowATaskResponse as input, creates a synchronus version,
     * and waits for it complete.
     * @param ShowATaskResponse $resp
     * @param string $stage
     * @return ShowATaskResponse
     */
    protected function waitForTaskToComplete(ShowATaskResponse $resp, string $stage): ShowATaskResponse
    {
        $this->log("Waiting for $stage Task to complete");
        $id = $resp->id;
        $req = new WaitForTaskRequest(['id' => $id]);

        $resp = $this->makeGuzzleRequest($req);

        /** @var ShowATaskResponse */
        $respModel = ShowATaskResponse::create($resp);

        /** @var string */
        $status = $respModel->status;
        if ($status !== 'finished') {
            throw new RuntimeException('Unexpected status result: ' . $status);
        }
        $this->log("$stage Task completed", $respModel->getAttributes());
        return $respModel;
    }

    /**
     * Create a thumbnail in CC
     *
     * @param Asset $a
     * @param ShowATaskResponse $resp
     * @return ThumbnailResponse
     */
    protected function createThumbnailTask(Asset &$a, ShowATaskResponse $resp): ThumbnailResponse
    {
        $this->log('Starting Create Thumbnail Task');
        $originalFilename = $a->getFilename(false);
        $targetFilename = "{$originalFilename}-thumbnail.png";
        $req = new CreateThumbnailRequest([
            'input' => $resp->id,
            'outputFormat' => 'png',
            'filename' => $targetFilename,
            'width' => 300,
            'height' => 300,
            'fit' => 'crop'
        ]);
        $resp = $this->makeGuzzleRequest($req);
        $respModel = ThumbnailResponse::create($resp);
        $this->log('Thumbnail Task Created', $respModel->getAttributes());
        return $respModel;
    }

    /**
     * Make a Guzzle HTTP request
     *
     * @param AbstractRequest $req
     * @return ResponseInterface
     */
    protected function makeGuzzleRequest(AbstractRequest $req): ResponseInterface
    {
        $client = $this->getClient();
        $url = $this->createRequestUrl($req);
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => $this->bearerTokenString,
            ];
            if ($req->getMethod() === 'post') {
                $resp = $client->post($url, [
                    'headers' => $headers,
                    'json' => $req->getBody(), //Json::encode($req->getBody()),
                ]);
            } else {
                $resp = $client->get($url, [
                    'headers' => $headers,
                ]);
            }
            return $resp;
        } catch (ClientException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                // response is typically HTML
                $content = $response->getBody()->getContents();
                $this->log('Error', $content);
            } else {
                $this->log('Error', $msg);
            }
            throw $e;
        }
    }

    /**
     * Generate the appropriate API Urls
     *
     * @param AbstractRequest $req
     * @return string
     */
    protected function createRequestUrl(AbstractRequest &$req): string
    {
        $sandbox = $this->sandbox;
        $sync = $req->getSync();
        $uri = $req->getUri();
        if (!$sandbox) {
            $base = self::SYNC_BASE_URL;
        } else {
            $base = self::SYNC_SANDBOX_BASE_URL;
        }
        return "${base}/{$uri}";
    }

    /**
     * Helper method to log output
     *
     * @param string $step
     * @param array $payload
     * @return void
     */
    protected function log(string $step, $payload = [])
    {
        if (!$this->logPath) {
            $this->logPath = Craft::$app->getPath()->getLogPath() . '/cloudconvert.log';
        }
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');
        $contents = join('|', [$timestamp, $step, print_r($payload, true),]) . PHP_EOL;
        FileHelper::writeToFile($this->logPath, $contents, ['append' => true]);
    }
}
