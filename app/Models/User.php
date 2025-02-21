<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Fields that can be mass-assigned.
     *
     * These are the columns that can be assigned values via methods like create() and update() 
     * without having to set them individually. Only the listed fields are allowed for mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_code',
        'code_expires_at',
        'email_verified',
        'role'
    ];

    /**
     * Fields that are hidden from array or JSON representations.
     *
     * These fields will not be exposed when the model is converted to an array or JSON format.
     * Typically used to hide sensitive information like passwords and verification codes.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',           // The password is hidden to prevent exposure.
        'verification_code',  // The verification code is hidden for security reasons.
    ];

    /**
     * Encrypt the password before saving it.
     *
     * This method automatically hashes the user's password whenever it is set.
     * The hashed password is securely stored in the database to prevent exposure of plaintext passwords.
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Check if the verification code has expired.
     *
     * This method compares the current time with the expiration time of the verification code
     * to determine whether the code is still valid. If the current time exceeds the expiration 
     * time, the verification code is considered expired.
     *
     * @return bool
     */
    public function isVerificationCodeExpired()
    {
        return now() > $this->verification_code_expiration;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * This is useful for automatically casting attributes like dates to the appropriate format.
     * In this case, the 'email_verified_at' field is cast to a DateTime instance.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',  // Automatically cast 'email_verified_at' to a DateTime object.
    ];
}