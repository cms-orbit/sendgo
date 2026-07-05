<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

class NoticeCampaignListScreen extends AbstractCampaignListScreen
{
    public function name(): ?string
    {
        return __('AlimTalk Campaigns');
    }

    public function description(): ?string
    {
        return __('Kakao AlimTalk delivery records from SendGo.');
    }

    protected function listRouteName(): string
    {
        return 'orbit.sendgo.notices.index';
    }

    protected function viewRouteName(): string
    {
        return 'orbit.sendgo.notices.view';
    }

    protected function fetchCampaigns(array $filters): array
    {
        return $this->client()->listNotices($filters);
    }
}
