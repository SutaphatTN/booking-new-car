<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $username
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $password_de
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable;

	protected $casts = [
		'email_verified_at' => 'datetime',
		'two_factor_confirmed_at' => 'datetime',
		'current_team_id' => 'int'
	];

	protected $hidden = [
		'password',
		'two_factor_secret',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'username',
		'email_verified_at',
		'role',
		'cardID',
		'password',
		'password_plain',
		'two_factor_secret',
		'two_factor_recovery_codes',
		'two_factor_confirmed_at',
		'remember_token',
		'current_team_id',
		'profile_photo_path'
	];
}
