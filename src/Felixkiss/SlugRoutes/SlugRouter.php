<?php namespace Felixkiss\SlugRoutes;

use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SlugRouter extends Router
{
    public function model($key, $class, Closure $callback = null, $forceId = false)
    {
        return $this->bind($key, function($value) use ($class, $callback, $forceId)
        {
            if (is_null($value)) return null;

            // For model binders, we will attempt to retrieve the model using the find
            // method on the model instance. If we cannot retrieve the models we'll
            // throw a not found exception otherwise we will return the instance.
            $model = new $class;

            if($forceId === false && $model instanceof SluggableInterface)
            {
                $model = $model->where($model->getSlugIdentifier(), $value)->first();
            }
            else
            {
                $model = $model->find($value);
            }
            
            if ( ! is_null($model))
            {
                return $model;
            }

            // If a callback was supplied to the method we will call that to determine
            // what we should do when the model is not found. This just gives these
            // developer a little greater flexibility to decide what will happen.
            if ($callback instanceof Closure)
            {
                return call_user_func($callback);
            }

            throw new NotFoundHttpException;
        });
    }
}