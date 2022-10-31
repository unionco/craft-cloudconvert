<?php

namespace unionco\cloudconvert\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * CloudConvert API Key. You must create an account and supply your own
     * API Key here.
     *
     * @var string
     */
    public string $apiKey = '';

    /**
     * Should the CloudConvert API run in sandbox mode?
     *
     * @var boolean
     */
    public bool $sandbox = false;

    /**
     * Should this plugin be enabled in this environment? It is common to disable
     * thumbnail generation in lower environments to reduce API calls.
     *
     * @var boolean
     */
    public bool $enabled = false;

    /**
     * @var int|null
     */
    public $thumbnailFolderId = null;

    /**
     * Width for the generated thumbnail
     *
     * @var int|null
     */
    public $thumbnailWidth = 300;

    /**
     * Height for the generated thumbnail
     *
     * @var int|null
     */
    public $thumbnailHeight = 300;

    /**
     * Fit mode for the generated thumbnail
     *
     * @var string|null
     */
    public $thumbnailFit = 'crop';
}
