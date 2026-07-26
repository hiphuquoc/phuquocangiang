<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Permissions\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'google_id',
        'avatar',
        'wallet_balance',
        'role',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
    ];

    public function isAdmin(): bool
    {
        if (($this->role ?? null) === 'admin') {
            return true;
        }

        return $this->hasRole('admin');
    }

    public static function insertItem($params){
        $id = 0;
        if(!empty($params)){
            $model = new User();
            foreach($params as $key => $value) $model->{$key} = $value;
            $model->save();
            $id = $model->id;
        }
        return $id;
    }

    public static function updateItem($id, $params){
        $flag = false;
        if(!empty($id) && !empty($params)){
            $model = self::find($id);
            if($model){
                foreach($params as $key => $value) $model->{$key} = $value;
                $flag = $model->update();
            }
        }
        return $flag;
    }
}
