<?php

declare(strict_types=1);

use CmsOrbit\Sendgo\Screens\Campaigns\FriendCampaignListScreen;
use CmsOrbit\Sendgo\Screens\Campaigns\FriendCampaignViewScreen;
use CmsOrbit\Sendgo\Screens\Campaigns\NoticeCampaignListScreen;
use CmsOrbit\Sendgo\Screens\Campaigns\NoticeCampaignViewScreen;
use CmsOrbit\Sendgo\Screens\Campaigns\SmsCampaignListScreen;
use CmsOrbit\Sendgo\Screens\Campaigns\SmsCampaignViewScreen;
use CmsOrbit\Sendgo\Screens\KakaoSenderListScreen;
use CmsOrbit\Sendgo\Screens\SenderListScreen;
use CmsOrbit\Sendgo\Screens\SendgoHubScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::screen('sendgo', SendgoHubScreen::class)
    ->name('sendgo.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.index')
        ->push(__('SendGo Hub')));

Route::screen('sendgo/messages', SmsCampaignListScreen::class)
    ->name('sendgo.messages.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.sendgo.index')
        ->push(__('SMS Campaigns')));

Route::screen('sendgo/messages/{id}', SmsCampaignViewScreen::class)
    ->name('sendgo.messages.view')
    ->breadcrumbs(fn (Trail $trail, string $id) => $trail
        ->parent('orbit.sendgo.messages.index')
        ->push($id));

Route::screen('sendgo/notices', NoticeCampaignListScreen::class)
    ->name('sendgo.notices.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.sendgo.index')
        ->push(__('AlimTalk Campaigns')));

Route::screen('sendgo/notices/{id}', NoticeCampaignViewScreen::class)
    ->name('sendgo.notices.view')
    ->breadcrumbs(fn (Trail $trail, string $id) => $trail
        ->parent('orbit.sendgo.notices.index')
        ->push($id));

Route::screen('sendgo/friends', FriendCampaignListScreen::class)
    ->name('sendgo.friends.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.sendgo.index')
        ->push(__('FriendTalk Campaigns')));

Route::screen('sendgo/friends/{id}', FriendCampaignViewScreen::class)
    ->name('sendgo.friends.view')
    ->breadcrumbs(fn (Trail $trail, string $id) => $trail
        ->parent('orbit.sendgo.friends.index')
        ->push($id));

Route::screen('sendgo/senders', SenderListScreen::class)
    ->name('sendgo.senders.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.sendgo.index')
        ->push(__('Senders')));

Route::screen('sendgo/kakao-senders', KakaoSenderListScreen::class)
    ->name('sendgo.kakao-senders.index')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('orbit.sendgo.index')
        ->push(__('Kakao Profiles')));
