<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->roleAdsSuperAdmin();
        $this->roleAdsAdmin();
        $this->roleAdsMediaAdmin();
    }

    private function roleAdsSuperAdmin()
    {
        $role = createRoleWithPermissions('Ads Super Admin', [
            // Event Category
            'ads.view-event-categories',
            'ads.create-event-categories',
            'ads.edit-event-categories',
            'ads.delete-event-categories',
            // Event
            'ads.create-event',
            'ads.view-event',
            'ads.update-event',
            'ads.delete-event',
            // Template
            'ads.view-template',
            'ads.create-template',
            'ads.update-template',
            'ads.delete-template',
            // Content
            'ads.view-content',
            'ads.create-content',
            'ads.update-content',
            'ads.delete-content'
        ]);

        syncUserToNewRole('Super Admin', $role->id);
    }

    private function roleAdsAdmin()
    {
        createRoleWithPermissions('Ads Admin',[
            // Event
            'ads.create-event',
            'ads.view-event',
            'ads.update-event',
            'ads.delete-event',   
            // Content
            'ads.view-content',
            'ads.create-content',
            'ads.update-content',
            'ads.delete-content'
        ]);
    }

    private function roleAdsMediaAdmin()
    {
        createRoleWithPermissions('Ads Admin Media',[
            // Template
            'ads.view-template',
            'ads.create-template',
            'ads.update-template',
            'ads.delete-template',   
            // Event
            'ads.create-event',
            'ads.view-event',
            'ads.update-event',
            'ads.delete-event',
            // Content
            'ads.view-content',
            'ads.create-content',
            'ads.update-content',
            'ads.delete-content'
        ]);
    }

    
}
