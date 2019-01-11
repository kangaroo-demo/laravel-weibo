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
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }

    /**
     * 获取粉丝关系列表
     *
     * @return void
     */
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 获取用户关注人列表
     *
     * @return void
     */
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * 关注
     *
     * @return void
     */
    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    /**
     * 取消关注
     *
     * @return void
     */
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    /**
     * A是否关注了B
     *
     * @return void
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
