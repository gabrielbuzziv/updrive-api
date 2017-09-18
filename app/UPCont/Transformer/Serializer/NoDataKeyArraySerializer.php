<?php

namespace App\UPCont\Transformer\Serializer;

use League\Fractal\Resource\NullResource;
use League\Fractal\Serializer\ArraySerializer;

class NoDataKeyArraySerializer extends ArraySerializer
{

    /**
     * Custom collection method
     *
     * @param string $resourceKey
     * @param array $data
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $data;
    }
}