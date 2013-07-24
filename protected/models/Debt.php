<?php

/**
 * Class User
 *
 * @property int id
 * @property int sourceUserId
 * @property int destUserId
 * @property string createdDate
 * @property string closedDate
 * @property int sum
 *
 * @method static \Illuminate\Database\Query\Builder where where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 */
class Debt extends Illuminate\Database\Eloquent\Model
{
    protected $fillable = array('sourceUserId', 'destUserId', 'sum');

    const CREATED_AT = 'createdDate';
    const DELETED_AT = 'closedDate';
}