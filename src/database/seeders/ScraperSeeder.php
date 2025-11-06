<?php

namespace Molitor\Scraper\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Scraper\Services\ScraperService;
use Molitor\User\Exceptions\PermissionException;
use Molitor\User\Services\AclManagementService;

class ScraperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('scraper', 'Weboldalak scrapelése', 'admin');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }

        if(app()->isLocal()) {
            app(ScraperService::class);
        }
    }
}
