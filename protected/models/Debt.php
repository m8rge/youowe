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
 * @method opened
 */
class Debt extends Illuminate\Database\Eloquent\Model
{
    protected $fillable = array('sourceUserId', 'destUserId', 'sum');

    protected $softDelete = true;

    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'updatedDate';
    const DELETED_AT = 'closedDate';

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOpened($query)
    {
        return $query->where('closedDate', 'IS NOT', 'NULL');
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scoreOrderDefault($query)
    {
        return $query->orderBy('createdDate', 'DESC');
    }

}