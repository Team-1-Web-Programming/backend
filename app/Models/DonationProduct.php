<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'address_id',
        'title',
        'description',
        'amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function donationProductCategory()
    {
        return $this->hasMany(DonationProductCategory::class);
    }

    public function donationCategory()
    {
        return $this->belongsToMany(DonationCategory::class, 'donation_product_categories');
    }

    public function donationProductMedia()
    {
        return $this->hasMany(DonationProductMedia::class);
    }
}
