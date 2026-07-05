<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class FriendCampaignViewScreen extends AbstractCampaignViewScreen
{
    public function name(): ?string
    {
        return __('FriendTalk Campaign');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.friends.index';
    }

    protected function fetchCampaign(string $campaignId): array
    {
        return $this->client()->getFriend($campaignId);
    }
}
