<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class DetachPermissionFromRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:detach {--permission=} {--role=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detaching permission from role';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permission_slug = $this->option('permission');

        $role_slug = $this->option('role');
        if (!$role_slug) {
            $this->error('Требуется опция --role=');
            return 0;
        }

        if ($this->option('all')) {
            $permissions = Permission::query()->get();
            if ($permissions->isEmpty()) {
                $this->error('Не найдено прав в таблице');
                return 0;
            }
        } elseif (!$permission_slug) {
            $this->error('Требуется опция --permission=');
            return 0;
        } else {
            $permissions = Permission::query()->where('slug', $permission_slug)->get();
            if ($permissions->isEmpty()) {
                $this->error('Не найдено право в таблице со слагом ' . $permission_slug);
                return 0;
            }
        }

        $role = Role::query()->where('slug', $role_slug)->first();
        if (!$role) {
            $this->error('Не найдено роли в таблице со слагом ' . $role_slug);
            return 0;
        }

        try {
            foreach ($permissions as $permission) {
                if ($role->permissions()->find($permission->id)) {
                    $role->permissions()->detach($permission->id);
                    $this->info('Право ' . $permission->slug . ' успешно откреплено от роли ' . $role->slug);
                    continue;
                }
                $this->info('Право ' . $permission->slug . ' уже откреплено от роли ' . $role->slug);


            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 0;
        }
        return 1;
    }
}
