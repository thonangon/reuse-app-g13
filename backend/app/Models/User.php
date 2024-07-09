<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Fortify\TwoFactorAuthenticatable;
// use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles; // Include HasRoles trait for roles and permissions

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'post_count',
        'has_paid',
        'unlimited_posts'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }




        // Define relationship with Category
        public function category()
        {
            return $this->belongsTo(Category::class);
        }

        // Define relationship with User

        public function comments()
        {
            return $this->hasMany(CommentProduct::class);
        }
    
        public function replies()
        {
            return $this->hasMany(ReplyComment::class);
        }
<<<<<<< HEAD
        public function stores()
        {
            return $this->hasMany(Store::class); // User has many stores
        }
=======
        public function plan()
    {
        return $this->hasOne(Plans::class);
    }
        
>>>>>>> 878e3632b5a0a223b1265481aedb88425302cefb
}
