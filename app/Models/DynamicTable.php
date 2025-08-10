<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'original_filename',
        'file_path',
        'columns',
        'metadata',
        'total_records',
        'status',
        'error_message'
    ];

    protected $casts = [
        'columns' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function records()
    {
        return $this->hasMany(DynamicTableRecord::class);
    }

    public function getColumnNames()
    {
        return array_column($this->columns, 'name');
    }

    public function getColumnByName($name)
    {
        return collect($this->columns)->firstWhere('name', $name);
    }
}
