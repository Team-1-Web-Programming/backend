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

    public function claim($amount)
    {
        if ($this->user_id == auth('sanctum')->user()->id) {
            throw new \Exception('You can not claim your own product', 400);
        }

        if ($this->amount < $amount) {
            throw new \Exception('Amount is not enough', 400);
        }

        $donation = Donation::create([
            'donor_id' => $this->user_id,
            'donee_id' => auth('sanctum')->user()->id,
            'donation_product_id' => $this->id,
            'amount' => $amount,
            'status' => 'requested',
        ]);

        $this->update([
            'amount' => $this->amount - $amount
        ]);

        return $donation;
    }
}
