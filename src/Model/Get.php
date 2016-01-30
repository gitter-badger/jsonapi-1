<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\JSONAPI\Model;

use Phramework\JSONAPI\Fields;
use Phramework\JSONAPI\Filter;
use Phramework\JSONAPI\Page;
use Phramework\JSONAPI\Sort;
use Phramework\JSONAPI\Resource;
use Phramework\Phramework;
use Phramework\Models\Operator;
use Phramework\JSONAPI\Relationship;

/**
 * @since 1.0.0
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
abstract class Get extends \Phramework\JSONAPI\Model\Cache
{
    /**
     * @param Page|null $page       *[Optional]*
     * @param Filter|null $filter   *[Optional]*
     * @param Sort|null $sort       *[Optional]*
     * @param Fields|null $fields   *[Optional]*
     * @param mixed ...$additionalParameters *[Optional]*
     * @throws NotImplementedException
     * @return Resource[]
     */
    public static function get(
        Page   $page = null,
        Filter $filter = null,
        Sort   $sort = null,
        Fields $fields = null,
        ...$additionalParameters
    ) {
        throw new NotImplementedException('This resource model doesn\'t have a get method');
    }

    /**
     * @param string|string[] $id An id of a single resource or ids of multiple resources
     * @param mixed ...$additionalParameters
     * @return Resource|object
     * @todo make sure call to get method with multiple $additionalParameters will work
     */
    public static function getById($id, ...$additionalParameters)
    {
        if (!is_array($id) && ($cached = static::getCache($id)) !== null) {
            //Return immediately if cached
            return $cached;
        } elseif (is_array($id)) {
            $collectionObject = new \stdClass();

            $originalId = $id;

            foreach ($originalId as $resourceId) {
                $collectionObject->{$resourceId} = null;
                if (($cached = static::getCache($resourceId)) !== null) {
                    $collectionObject->{$resourceId} = $cached;
                    //remove $resourceId from id array, so we wont request the same item again,
                    //but it will be returned in $collectionObject
                    $id = array_diff($id, [$resourceId]);
                }
            }
        }

        //Prepare filter
        $filter = new Filter();

        $filter->primary = (
            is_array($id)
            ? $id
            : [$id]
        ); //Force array for primary data

        $collection = static::get(
            new Page(count($id)), //limit number of requested resources
            $filter,
            null, //sort
            null, //fields
            $additionalParameters
        );

        if (!is_array($id)) {
            if (empty($collection)) {
                return null;
            }

            //Store resource
            static::setCache($id, $collection[0]);

            //Return a resource
            return $collection[0];
        } else { //if array
            //$collectionObject = new \stdClass();
//
            //foreach ($id as $resourceId) {
            //    $collectionObject->{$resourceId} = null;
            //}

            foreach ($collection as $resource) {
                $collectionObject->{$resource->id} = $resource;
                static::setCache($resource->id, $resource);
            }

            unset($collection);

            return $collectionObject;
        }
    }

    /**
     * Parse page for pagination by parsing request parameters and using current implementation model's rules.
     * @param object $parameters Request parameters
     * @return null|Page
     */
    public static function parsePage($parameters)
    {
        return Page::parseFromParameters($parameters, self::class);
    }

    /**
     * Parse sort by parsing request parameters and using current implementation model's rules.
     * @param  object $parameters   Request parameters
     * @return Sort|null
     */
    public static function parseSort($parameters)
    {
        return Sort::parseFromParameters($parameters, self::class);
    }

    /**
     * Parse filter by parsing request parameters and using current implementation model's rules.
     * @param  object $parameters   Request parameters
     * @return Filter|null
     */
    public static function parseFilter($parameters)
    {
        return Filter::parseFromParameters($parameters, self::class);
    }
}