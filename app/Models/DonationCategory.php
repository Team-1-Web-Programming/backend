<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'title'
    ];

    public static function generateTree(int|null $parent_id = null)
    {
        $data = DonationCategory::where('parent_id', $parent_id)->get();
        foreach ($data as $item) {
            $item->children = DonationCategory::generateTree($item->id);
        }
        return $data;
    }
}
