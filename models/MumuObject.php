<?php

class MumuObject extends \Phalcon\Mvc\Collection
{
    /**
     * Class name for data on Flyway.
     *
     * @var string
     */
    public $className;

    /**
     * Unique identifier on Flyway.
     *
     * @var string
     */
    public $objectId;

    /**
     * Timestamp when object was created.
     *
     * @var \DateTime
     */
    public $createdAt;

    /**
     * Timestamp when object was last updated.
     *
     * @var \DateTime
     */
    public $updatedAt;
}
