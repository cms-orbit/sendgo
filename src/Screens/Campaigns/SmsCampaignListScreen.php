<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class SmsCampaignListScreen extends AbstractCampaignListScreen
{
    public function name(): ?string
    {
        return __('SMS Campaigns');
    }

    public function description(): ?string
    {
        return __('SMS, LMS, and MMS delivery records from SendGo.');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.messages.index';
    }

    protected function viewRouteName(): string
    {
        return 'orbit.sendgo.messages.view';
    }

    protected function fetchCampaigns(array $filters): array
    {
        return $this->client()->listMessages($filters);
    }
}
