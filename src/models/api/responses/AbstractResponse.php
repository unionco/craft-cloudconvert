<?php

namespace unionco\cloudconvert\models\api\responses;

use craft\base\Model;
use craft\helpers\Json;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponse extends Model
{
    protected static array $fieldsMap = [];

    public static function create(ResponseInterface $response): static
    {
        $array = static::getResponsePayloadArray($response);
        $array = $array['data'];
        // var_dump($array);
        foreach (static::$fieldsMap as $new => $old) {
            $array[$new] = $array[$old] ?? null;
            unset($array[$old]);
        }

        return new static($array);
    }

    protected static function getResponsePayloadArray(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        return Json::decodeIfJson($contents);
    }
}
