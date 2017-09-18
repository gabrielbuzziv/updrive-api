<?php

namespace App\Http\Controllers\Traits;

use Spatie\Fractal\Fractal;

trait Transformable
{

    /**
     * Transform collection as a fractal transformer.
     *
     * @param $collection
     * @param $transformer
     * @param null $include
     * @param null $exclude
     * @return $this
     */
    public function transformCollection($collection, $transformer, $include = null, $exclude = null)
    {
        return Fractal::create()
            ->collection($collection)
            ->transformWith($transformer)
            ->parseIncludes($include)
            ->parseExcludes($exclude)
            ->toArray();
    }

    /**
     * Transform item as a fractal transformer.
     *
     * @param $item
     * @param $transformer
     * @param null $include
     * @param null $exclude
     * @return mixed
     */
    public function transformItem($item, $transformer, $include = null, $exclude = null)
    {
        $include = $include ?: explode(',', app('request')->input('include'));
        $exclude = $exclude ?: explode(',', app('request')->input('exclude'));

        return Fractal::create()
            ->item($item)
            ->transformWith($transformer)
            ->parseIncludes($include)
            ->parseExcludes($exclude)
            ->toArray();
    }
}