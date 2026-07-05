# cms-orbit/sendgo

Orbit companion package for [SendGo](https://www.sendgo.io) integration.

## Features

- SendGo Hub with credit overview and quick links
- Orbit Settings group for `sendgo.*` credentials
- AlimTalk template sync into local database
- SMS, AlimTalk, and FriendTalk campaign record screens (SendGo API v2)
- Sender numbers and Kakao profile listings
- Phone verification via `techigh/sendgo-notification`

## Installation

```bash
composer require cms-orbit/sendgo
php artisan migrate
php artisan sendgo:migrate-config   # when upgrading from auth_sendgo.* keys
```

## Configuration

Configure credentials in Orbit **Settings → SendGo**, or via `.env`:

```env
SENDGO_URL=https://api.sendgo.io
SENDGO_ACCESS_KEY=
SENDGO_SECRET_KEY=
SENDGO_SENDER_KEY=
SENDGO_KAKAO_SENDER_KEY=
SENDGO_API_VERSION=v2
SENDGO_PHONE_VERIFICATION_TEMPLATE_CODE=
```

## Commands

- `php artisan sendgo:sync-templates` — sync AlimTalk templates from SendGo
- `php artisan sendgo:migrate-config` — migrate legacy `auth_sendgo.*` orbit config keys

## License

MIT
