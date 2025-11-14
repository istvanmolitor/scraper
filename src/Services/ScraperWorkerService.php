<?php

namespace Molitor\Scraper\Services;

use Molitor\Scraper\Jobs\ScraperWorker;
use Molitor\Setting\Repositories\SettingRepositoryInterface;

class ScraperWorkerService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private ScraperService $scraperService
    )
    {
    }

    const SCRAPER_WORKER_ENABLED = 'scraper_worker_enabled';

    const SCRAPER_LIMIT = 'scraper_limit';

    public function isEnabled(): bool
    {
        return $this->settingRepository->get(static::SCRAPER_WORKER_ENABLED);
    }

    public function start(): void
    {
        $this->settingRepository->set(static::SCRAPER_WORKER_ENABLED, true);
        $this->handleWork();
    }

    public function stop(): void
    {
        $this->settingRepository->set(static::SCRAPER_WORKER_ENABLED, false);
    }

    public function getLimit(): int
    {
        return $this->settingRepository->get(static::SCRAPER_LIMIT);
    }

    public function handleWork(): void
    {
        if($this->isEnabled()) {
            $this->scraperService->work($this->getLimit());
            ScraperWorker::dispatch()->delay(now()->addMinutes(1));
        }
    }
}
