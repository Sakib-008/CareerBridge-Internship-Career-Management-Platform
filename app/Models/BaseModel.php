<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * This override normalizes
     * attribute access so $model->USER_ID works even though the raw
     * fetched key is 'user_id'.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value !== null) {
            return $value;
        }

        // Try lowercase version of the key as a fallback
        $lower = strtolower($key);
        if ($key !== $lower && array_key_exists($lower, $this->attributes)) {
            return parent::getAttribute($lower);
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute($key, $value);
    }
}