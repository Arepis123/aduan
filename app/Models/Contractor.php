<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $connection = 'skim';

    protected $table = 'contractors';

    protected $primaryKey = 'ctr_clab_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'ctr_clab_no',
        'ctr_comp_name',
    ];
}
