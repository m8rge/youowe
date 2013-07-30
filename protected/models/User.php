<?php

/**
 * Class User
 *
 * @property int id
 * @property string email
 * @property string hashedPassword
 *
 * @method static \Illuminate\Database\Query\Builder where where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 */
class User extends Illuminate\Database\Eloquent\Model
{
    protected $fillable = array('email', 'password');

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