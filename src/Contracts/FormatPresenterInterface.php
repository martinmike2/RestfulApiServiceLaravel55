<?php namespace Entrack\RestfulAPIService\Contracts;
interface FormatPresenterInterface
{
    public static function format(EntityInterface $entity);
}