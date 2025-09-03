<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';
    protected $primaryKey = 'lang_id';
    protected $fillable = ['name','alias','code','iso_code','region','display_order','enabled','display_scope'];
}
