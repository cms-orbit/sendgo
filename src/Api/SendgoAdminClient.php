<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Api;

use Techigh\SendgoNotification\Exceptions\SendGoException;
use Techigh\SendgoNotification\SendGo;

class SendgoAdminClient extends SendGo
{
    public function __construct()
    {
        $this->initializeKeys()
            ->initializeSenderKeys()
            ->initializeApiVersion()
            ->initializeApiUrl()
            ->initializeHeaders();
    }

    protected function initializeKeys(): static
    {
        $this->accessKey = (string) (config('sendgo.access_key') ?? '');
        $this->secretKey = (string) (config('sendgo.secret_key') ?? '');

        return $this;
    }

    protected function initializeApiUrl(): static
    {
        $this->endpoint = (string) (config('sendgo.url') ?? '');
        $this->url = rtrim($this->endpoint, '/').'/api';

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function credits(): array
    {
        return $this->performGet($this->endpoint('/credits'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listMessages(array $filters = []): array
    {
        return $this->performGet($this->endpoint('/messages'), $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessage(string $campaignId): array
    {
        return $this->performGet($this->endpoint('/messages/'.$campaignId));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listNotices(array $filters = []): array
    {
        return $this->performGet($this->endpoint('/notices'), $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function getNotice(string $campaignId): array
    {
        return $this->performGet($this->endpoint('/notices/'.$campaignId));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listFriends(array $filters = []): array
    {
        return $this->performGet($this->endpoint('/friends'), $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFriend(string $campaignId): array
    {
        return $this->performGet($this->endpoint('/friends/'.$campaignId));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listTemplates(array $filters = []): array
    {
        return $this->performGet($this->endpoint('/notices/templates'), $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function listSenders(): array
    {
        return $this->performGet($this->endpoint('/senders'));
    }

    /**
     * @return array<string, mixed>
     */
    public function getSender(string $senderId): array
    {
        return $this->performGet($this->endpoint('/senders/'.$senderId));
    }

    /**
     * @return array<string, mixed>
     */
    public function listKakaoSenders(): array
    {
        return $this->performGet($this->endpoint('/kakao-senders'));
    }

    /**
     * @return array<string, mixed>
     */
    public function getKakaoSender(string $senderId): array
    {
        return $this->performGet($this->endpoint('/kakao-senders/'.$senderId));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function performGet(string $url, array $query = []): array
    {
        if (! $this->validateKeys()) {
            throw new SendGoException('SendGo credentials are not configured.');
        }

        if (! $this->validateToken()) {
            $this->issueToken();
        }

        $response = $this->client()->get($url, $query);

        if ($this->shouldRefreshToken($response)) {
            $this->forceRefreshToken();
            $response = $this->client()->get($url, $query);
        }

        if ($response->failed()) {
            $endpointName = basename(parse_url($url, PHP_URL_PATH) ?? $url);
            throw SendGoException::fromResponse(
                $response->status(),
                $response->json() ?? [],
                $endpointName,
                $this->apiVersion
            );
        }

        return $response->json() ?? [];
    }

    protected function endpoint(string $path): string
    {
        return $this->url.'/v2'.$this->start($path);
    }
}
