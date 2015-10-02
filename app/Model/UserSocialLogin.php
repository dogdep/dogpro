<?php namespace App\Model;

/**
 * Class UserSocialLogin
 *
 * @property integer $user_id
 * @property string $token
 * @property string $provider
 * @property string $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property User $user
 */
class UserSocialLogin extends Model
{
    /**
     * @var string
     */
    protected $table = 'users_social_logins';

    /**
     * @var array
     */
    protected $fillable = ['token', 'data', 'user_id', 'provider'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
