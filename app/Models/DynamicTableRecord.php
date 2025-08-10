<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTableRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_table_id',
        'data',
        'row_number'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function dynamicTable()
    {
        return $this->belongsTo(DynamicTable::class);
    }

    public function getValue($columnName)
    {
        return $this->data[$columnName] ?? null;
    }

    public function setValue($columnName, $value)
    {
        $data = $this->data;
        $data[$columnName] = $value;
        $this->data = $data;
    }
}
