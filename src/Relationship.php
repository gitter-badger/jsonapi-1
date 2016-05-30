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
namespace Phramework\JSONAPI;

use Phramework\Phramework;

/**
 * JSONAPI relationship class
 * @since 0.0.0
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Relationship
{
    const FLAG_DEFAULT = 0;
    /**
     * @deprecated because relationships should not have attributes
     */
    const FLAG_ATTRIBUTES = 1;

    const FLAG_DATA = 2;

    /**
     * Include relationship by default
     */
    const FLAG_INCLUDE_BY_DEFAULT = 32;

    /**
     * Relationship type to one resource.
     */
    const TYPE_TO_ONE  = 1;

    /**
     * Relationship type to zero, one or more resources.
     */
    const TYPE_TO_MANY = 2;

    /**
     * @var InternalModel
     */
    protected $model;

    /**
     * The type of relationship from the resource to relationship resource
     * @var int
     */
    protected $type;

    /**
     * Attribute name in record containing relationship data
     * @var string|null
     */
    protected $recordDataAttribute;

    /**
     * Callable method can be used to fetch relationship data
     * @var \stdClass
     */
    protected $callbacks;

    /**
     * Relationship flags
     * @var int
     */
    protected $flags;

    /**
     * @param string               $model               Class path of relationship resource model
     * @param int                  $type                *[Optional] Relationship type
     * @param string|null          $recordDataAttribute *[Optional] Attribute name in record containing relationship data
     * @param callable|object|null $callbacks           *[Optional] Callable method can be used
     * to fetch relationship data, see TODO
     * @param int                  $flags               *[Optional] Relationship flags
     * @throws \Exception When model  doesn't extend Phramework\JSONAPI\Model
     * @throws \Exception When is not null, callable or object of callables
     * @example
     * ```php
     * getValidationModel() {
     *     return (object) [
     *         'author' => new Relationship(
     *             Tag::class,
     *             Relationship::TYPE_TO_ONE,
     *             'author-user_id'
     *         );
     *     ];
     * }
     * ```
     * @example
     * ```php
     * getValidationModel() {
     *     return (object) [
     *         'tag' => new Relationship(
     *             Tag::class,
     *             Relationship::TYPE_TO_MANY,
     *             null,
     *             [Tag::class, 'getRelationshipByArticle']
     *         );
     *     ];
     * }
     * ```
     * @example
     * ```php
     * getValidationModel() {
     *     return (object) [
     *         'tag' => new Relationship(
     *             Tag::class,
     *             Relationship::TYPE_TO_MANY,
     *             null,
     *             (object) [
     *                  'GET'  => [Tag::class, 'getRelationshipByArticle'],
     *                  'POST' => [Tag::class, 'postRelationshipByArticle']
     *             ]
     *         );
     *     ];
     * }
     * ```
     */
    public function __construct(
        InternalModel $model,
        int $type = Relationship::TYPE_TO_ONE,
        string $recordDataAttribute = null,
        \stdClass $callbacks = null,
        int $flags = Relationship::FLAG_DEFAULT
    ) {
        if ($callbacks !== null) {
            if (is_object($callbacks)) {
                foreach ($callbacks as $method => $callback) {
                    if (!in_array($method, Phramework::$methodWhitelist)) {
                        throw new \Exception(sprintf(
                            'Not allowed method "%s" for callbacks',
                            $method
                        ));
                    }

                    if (!is_callable($callback)) {
                        throw new \Exception(sprintf(
                            'callbacks for method "%s" MUST be callable',
                            $method
                        ));
                    }
                }

                $this->callbacks = $callbacks;
            } elseif (is_callable($callbacks)) {
                $this->callbacks = (object) [
                    Phramework::METHOD_GET => $callbacks
                ];
            } else {
                throw new \Exception('callbacks MUST be callable');
            }
        } else {
            $this->callbacks = new \stdClass();
        }

        $this->model                = $model;
        $this->type                 = $type;
        $this->recordDataAttribute  = $recordDataAttribute;
        $this->flags                = $flags;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getRecordDataAttribute()
    {
        return $this->recordDataAttribute;
    }

    /**
     * @return \stdClass
     */
    public function getCallbacks() : \stdClass
    {
        return $this->callbacks;
    }

    /**
     * @return int
     */
    public function getFlags() : int
    {
        return $this->flags;
    }
}
