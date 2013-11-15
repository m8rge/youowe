<?php

/**
 * Class User
 *
 * @property int id
 * @property string nickname
 * @property string email
 * @property string hashedPassword
 *
 * @property-write string password
 * @method static \Illuminate\Database\Query\Builder where where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 */
class User extends Illuminate\Database\Eloquent\Model
{
    protected $fillable = array('nickname', 'email', 'password');
    protected $hidden = array('hashedPassword');

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        User::deleting(
            function () {
                return false;
            }
        );
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['hashedPassword'] = $this->hashPassword($value);
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}