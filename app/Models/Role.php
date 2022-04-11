<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    private const ROLE_ADMIN = 'admin';
    private const ROLE_METHODOLOGIST = 'methodologist';
    private const ROLE_PROVIDER = 'provider';
    private const ROLE_CONTRACTOR = 'contractor';

    private const HUMAN_READABLE_ADMIN = 'Администратор';
    private const HUMAN_READABLE_METHODOLOGIST = 'Методолог';
    private const HUMAN_READABLE_PROVIDER = 'Поставщик';
    private const HUMAN_READABLE_CONTRACTOR = 'Подрядчик';

    private const HUMAN_READABLE_ROLES = [
        self::ROLE_ADMIN => self::HUMAN_READABLE_ADMIN,
        self::ROLE_METHODOLOGIST => self::HUMAN_READABLE_METHODOLOGIST,
        self::ROLE_PROVIDER => self::HUMAN_READABLE_PROVIDER,
        self::ROLE_CONTRACTOR => self::HUMAN_READABLE_CONTRACTOR,
    ];

    public static function ROLES()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_METHODOLOGIST,
            self::ROLE_PROVIDER,
            self::ROLE_CONTRACTOR,
        ];
    }

    public static function ADMIN()
    {
        return self::ROLE_ADMIN;
    }

    public static function METHODOLOGIST()
    {
        return self::ROLE_METHODOLOGIST;
    }

    public static function PROVIDER()
    {
        return self::ROLE_PROVIDER;
    }

    public static function CONTRACTOR()
    {
        return self::ROLE_CONTRACTOR;
    }

    public static function humanReadableRoles()
    {
        return self::HUMAN_READABLE_ROLES;
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }
}
