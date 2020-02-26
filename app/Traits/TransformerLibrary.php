<?php

namespace App\Traits;

trait TransformerLibrary
{
    protected $extraFields = [];

    public function addExtraFields($values) {
        $this->extraFields = array_merge($this->extraFields, $values);
    }

    public function addExtraIncludes($values) {
        // The method is Not Work
        $this->defaultIncludes = array_merge( $this->defaultIncludes, $values);
    }

    protected function getIdentifier () {
        return ($identifier = $this->currentScope->getScopeIdentifier())
            ? (string) $identifier .'_field'
            : 'field';
    }

    protected function setField($data, $extras = [])
    {
        $fieldKey = $this->getIdentifier();

        $fields = request($fieldKey, null);

        $arr_fields = explode(',', $fields);

        $attrs = array_merge($data, $extras);

        $intersect = array_intersect_key($attrs, array_flip((array) $arr_fields));

        $fixData = array_search("*",$arr_fields) > -1 ? $data : [];
        $fixExtra = array_search("**",$arr_fields) > -1 ? $extras : [];

        $appends = array_intersect_key($attrs, array_flip((array) $this->extraFields));

        // dd(array_search("**",$arr_fields) > -1);
        // dd($defall, $appends, $intersect);
        return is_null($fields)
            ? array_merge($data, $appends)
            : array_merge($fixData, $fixExtra, $appends, $intersect);
    }
}
