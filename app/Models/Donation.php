<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donor_id',
        'donee_id',
        'donation_product_id',
        'amount',
        'status',
        'rejected_reason'
    ];

    public function confirmed()
    {
        if (!$this->product) {
            throw new \Exception('Product not found', 400);
        }

        if ($this->donor_id != auth('sanctum')->user()->id) {
            throw new \Exception('Donation is not your', 400);
        }

        if ($this->donee_id == auth('sanctum')->user()->id) {
            throw new \Exception('You can not donate to yourself', 400);
        }

        if ($this->product->amount < $this->amount) {
            throw new \Exception('Amount is not enough', 400);
        }

        if ($this->status != 'requested') {
            throw new \Exception('Donation not requested', 400);
        }

        $this->update([
            'status' => 'confirmed'
        ]);
    }

    public function taken()
    {
        if (!$this->product) {
            throw new \Exception('Product not found', 400);
        }

        if ($this->donor_id != auth('sanctum')->user()->id) {
            throw new \Exception('Donation is not your', 400);
        }

        if ($this->donee_id == auth('sanctum')->user()->id) {
            throw new \Exception('You can not donate to yourself', 400);
        }

        if ($this->status != 'confirmed') {
            throw new \Exception('Donation not confirmed', 400);
        }

        $this->update([
            'status' => 'taken'
        ]);
    }

    public function rejected($reason)
    {
        if (!$this->product) {
            throw new \Exception('Product not found', 400);
        }

        if ($this->donor_id != auth('sanctum')->user()->id) {
            throw new \Exception('Donation is not your', 400);
        }

        if ($this->donee_id == auth('sanctum')->user()->id) {
            throw new \Exception('You can not donate to yourself', 400);
        }

        if ($this->status != 'requested') {
            throw new \Exception('Donation not requested', 400);
        }

        $this->update([
            'status' => 'rejected',
            'rejected_reason' => $reason
        ]);

        $this->product->update([
            'amount' => $this->product->amount + $this->amount
        ]);
    }

    public function canceled()
    {
        if (!$this->product) {
            throw new \Exception('Product not found', 400);
        }

        if ($this->donor_id == auth('sanctum')->user()->id) {
            throw new \Exception('You can not cancel your own donation', 400);
        }

        if ($this->donee_id != auth('sanctum')->user()->id) {
            throw new \Exception('You can not cancel someone else donation', 400);
        }

        if (!in_array($this->status, ['requested', 'confirmed'])) {
            throw new \Exception('Donation status can not be canceled', 400);
        }

        $this->update([
            'status' => 'canceled'
        ]);

        $this->product->update([
            'amount' => $this->product->amount + $this->amount
        ]);
    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function donee()
    {
        return $this->belongsTo(User::class, 'donee_id');
    }

    public function product()
    {
        return $this->belongsTo(DonationProduct::class, 'donation_product_id');
    }
}
