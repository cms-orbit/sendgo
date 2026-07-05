<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class NoticeCampaignViewScreen extends AbstractCampaignViewScreen
{
    public function name(): ?string
    {
        return __('AlimTalk Campaign');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.notices.index';
    }

    protected function fetchCampaign(string $campaignId): array
    {
        return $this->client()->getNotice($campaignId);
    }
}
