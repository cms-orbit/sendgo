<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class SmsCampaignViewScreen extends AbstractCampaignViewScreen
{
    public function name(): ?string
    {
        return __('SMS Campaign');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.messages.index';
    }

    protected function fetchCampaign(string $campaignId): array
    {
        return $this->client()->getMessage($campaignId);
    }
}
