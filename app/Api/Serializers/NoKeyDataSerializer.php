<?php
namespace App\Api\Serializers;

use League\Fractal\Serializer\ArraySerializer;

class NoKeyDataSerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     */

    public function collection($resourceKey, array $data)
    {
        // dd('col', $this, $resourceKey, $data);
        return ($resourceKey) ? array($resourceKey ?: 'data' => $data) : $data ;
    }

    /**
     * Serialize an item.
     */
    public function item($resourceKey, array $data)
    {
        // dd('item', $resourceKey, $data);
        return ($resourceKey) ? array($resourceKey ?: 'data' => $data) : $data ;
    }
}
