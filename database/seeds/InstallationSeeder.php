<?php

use Illuminate\Database\Seeder;

class InstallationSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->installPermissions();
        $this->installUsers();
    }

    /**
     * Create all default permissions in database.
     */
    private function installPermissions()
    {
        $permissions = [
            ['name' => 'manage-users', 'display_name' => 'Gerenciar Usuários', 'description' => ''],
            ['name' => 'manage-core', 'display_name' => 'Gerenciar Empresas', 'description' => ''],
            ['name' => 'manage-core', 'display_name' => 'Gerenciar Contatos', 'description' => ''],
            ['name' => 'manage-core', 'display_name' => 'Gerenciar Documentos', 'description' => ''],
        ];

        array_walk($permissions, function ($permission) {
            \App\Permission::create($permission);
        });
    }

    /**
     * Install default user.
     */
    private function installUsers()
    {
        $user = \App\User::create([
            'name'     => 'Gabriel Buzzi Venturi',
            'email'    => 'gabrielbuzziv@gmail.com',
            'password' => 'gabriel',
        ]);

        $permissions = ['manage-users', 'manage-core', 'manage-core', 'manage-core'];
        $permissions = \App\Permission::whereIn('name', $permissions)->pluck('id')->all();

        $user->perms()->sync($permissions);
    }
}
