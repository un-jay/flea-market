<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'profile_image',
        'postal_code',
        'address',
        'building',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function chatsAsBuyer()
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    public function chatsAsSeller()
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function chatNotifications()
    {
        return $this->hasMany(ChatNotification::class, 'receiver_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'evaluator_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'evaluated_id');
    }

    public function profileUpload($data, $dir = null, $file_name = null)
    {
        $this->user_name = $data['user_name'];
        if ($dir !== null && $file_name !== null) {
            $this->profile_image = 'storage/' . $dir . '/' . $file_name;
        }
        $this->postal_code = $data['postal_code'];
        $this->address = $data['address'];
        $this->building = $data['building'];
        $this->save();
    }

    public function addressUpload($data)
    {
        $this->postal_code = $data['postal_code'];
        $this->address = $data['address'];
        $this->building = $data['building'];
        $this->save();
    }
}
