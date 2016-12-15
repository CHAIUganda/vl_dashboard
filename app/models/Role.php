<?php namespace EID\models;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

	protected $fillable = [
       'name',
       'display_name',
       'description'
   ];

    public function permissions()
    {
        return $this->belongsToMany('EID\models\Permission');
    }
    public function users()
    {
        return $this->belongsToMany('EID\user');
    }

}