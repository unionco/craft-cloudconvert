<?php

namespace unionco\cloudconvert\console\controllers;

use app\jobs\ThumbnailJob;
use Craft;
use craft\elements\Asset;
use craft\console\Controller;
use unionco\cloudconvert\Plugin;
use unionco\cloudconvert\services\CloudConvert;

class DebugController extends Controller
{
    public function actionGenerateThumbnail(int $assetId)
    {
        //$apiKey = Plugin::getInstance()->getSettings()->apiKey;
        // echo "$apiKey"; die;
        //$asset = Asset::find()->id($assetId)->one();
        //$result = Plugin::getInstance()->cloudconvert->getThumbnail($asset);
        // var_dump($result);

        // This asset should have a thumbnail generated, so start a queue job
        $job = new ThumbnailJob([
            'assetId' => $assetId,
        ]);
        /** @var Queue */
        $queue = Craft::$app->getQueue();
        $queue->push($job);
    }

    public function actionCreateThumbnail(string $url, string $filename)
    {
        //$apiKey = Plugin::getInstance()->getSettings()->apiKey;
        // echo "$apiKey"; die;
        //$asset = Asset::find()->id($assetId)->one();
        //$result = Plugin::getInstance()->cloudconvert->getThumbnail($asset);
        // var_dump($result);

        $asset = Plugin::getInstance()->cloudconvert->createThumbnailAsset($url, $filename);
        /** @var Queue */
        //$queue = Craft::$app->getQueue();
        //$queue->push($job);
    }
}
