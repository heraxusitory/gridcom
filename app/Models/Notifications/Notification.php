<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Model;

abstract class Notification extends Model
{
    private const ACTION_DRAFT = 'draft';
    private const ACTION_APPROVE = 'approve';

    private const TARGET_TO_CONTRACTOR = 'contractor';
    private const TARGET_TO_ORGANIZATION = 'organization';

    public static function getActions()
    {
        return [
            self::ACTION_DRAFT,
            self::ACTION_APPROVE,
        ];
    }

    public static function ACTION_APPROVE()
    {
        return self::ACTION_APPROVE;
    }

    public static function ACTION_DRAFT()
    {
        return self::ACTION_DRAFT;
    }

    public static function targets()
    {
        return [
            self::TARGET_TO_CONTRACTOR,
            self::TARGET_TO_ORGANIZATION,
        ];
    }

    public static function contractor_target()
    {
        return self::TARGET_TO_CONTRACTOR;
    }

    public static function organzation_target()
    {
        return self::TARGET_TO_ORGANIZATION;
    }
}
