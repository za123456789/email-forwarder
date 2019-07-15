<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Email
 *
 * @package App
 * @property string $from
 * @property string $to
*/
class Email extends Model
{
    use SoftDeletes;

    protected $fillable = ['from', 'to'];
    protected $hidden = [];
    
    
    
}
