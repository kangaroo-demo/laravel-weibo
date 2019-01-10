<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * 隐藏字段
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 该方法将在模型完成初始化时进行加载
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        //模型被创建时执行的方法
        static::creating(function ($user){
            $user->activation_token = str_random(30);
        });
    }

    /**
     * 为用户生成头像
     *
     * @return string
     */
    public function gravatar($size = '100')
    {
      $hash = md5(strtolower(trim($this->attributes['email'])));
      return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    /**
     * 发送重置密码通知
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * 用户微博一对多
     *
     * @return void
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * 返回当前用户所有微博列表
     *
     * @return Object
     */
    public function feed()
    {
        return $this->statuses()->orderBy('created_at', 'desc');
    }
}
