<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class FriendCampaignListScreen extends AbstractCampaignListScreen
{
    public function name(): ?string
    {
        return __('FriendTalk Campaigns');
    }

    public function description(): ?string
    {
        return __('Kakao FriendTalk delivery records from SendGo.');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.friends.index';
    }

    protected function viewRouteName(): string
    {
        return 'orbit.sendgo.friends.view';
    }

    protected function fetchCampaigns(array $filters): array
    {
        return $this->client()->listFriends($filters);
    }
}
