<?php namespace Felixkiss\SlugRoutes;

interface SluggableInterface
{
    /**
     * Returns the name of the database column, which should be used to search
     * for a record. The column should be URL friendly and unique to avoid collisions.
     *
     * @return string
     */
    public function getSlugIdentifier();
}