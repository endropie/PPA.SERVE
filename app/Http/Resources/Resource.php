<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    const QUERY_FIELDS_NAME = 'fields';
    const QUERY_INCLUDES_NAME = 'includes';

    protected $prefix = "";
    protected $default = [];
    protected $only = [];

    public function toArray($request)
    {
        return [
            $this->mergeAttributes(),
        ];
    }

    protected function mergeField(string $name, $value, $callback = null)
    {
        if ($callback != null) $callback($this);
        $condition = $this->hasQueryParameter(static::QUERY_FIELDS_NAME, $name);
        $value = $condition ? [$name => value($value)] : [];
        return $this->mergeWhen($condition, $value);
    }

    protected function mergeInclude(string $name, $value, $callback = null)
    {
        $condition = $this->hasQueryParameter(static::QUERY_INCLUDES_NAME, $name);

        if ($condition && $value) {
            $value = value($value);
            $prefix = $this->prefix ? "$this->prefix.$name" : $name;
            if (!property_exists($value, 'collects')) {
                $value->prefix($prefix);
                if ($callback != null) $callback($value);
            } else {
                $value->each->prefix($prefix);
                if ($callback != null) $callback($value);
            }
        }

        return $this->mergeWhen($condition, [$name => $value]);
    }

    protected function mergeAttributes(array $visible = [], $callback = null)
    {
        $attributes = $this->resource->fresh()->toArray();

        if ($callback != null) $attributes = $callback($attributes);

        if ($this->prefix) {
            foreach ($attributes as $name => $value) {
                $condition = $this->resource->getKeyName() === $name
                    ?: $this->hasQueryParameter(static::QUERY_FIELDS_NAME, $name);

                $attributes[$name] = in_array($name, $visible)
                    ? $value
                    : $this->when($condition, $value);
            }
        }

        return $this->mergeWhen(true, $attributes);
    }

    protected function hasQueryParameter($query, $name)
    {
        $prename = $this->prefix ? "$this->prefix.$name" : $name;

        if (count($this->only) && !in_array($name, $this->only)) return false;

        if (count($this->default) && in_array($name, $this->default)) return true;

        if ($values = request()->get($query)) {

            if (gettype($values) == "string") $values = explode(',', $values);

            if (in_array($this->prefix ? ($this->prefix . ".*") : "*", $values)) return true;

            return in_array($prename, $values);
        }

        return false;
    }

    public function prefix(string $name)
    {
        $this->prefix = $this->prefix ? "$this->prefix.$name" : $name;

        return $this;
    }

    public function default(array $values)
    {
        $this->default = array_merge($this->default, $values);

        return $this;
    }

    public function only(array $values)
    {
        $this->only = array_merge($this->only, $values);

        return $this;
    }
}
