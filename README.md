# CMS Orbit SendGo

`cms-orbit/sendgo`는 Orbit 관리자와 [SendGo](https://www.sendgo.io) API를 연결하는 동반 패키지입니다.  
Hub 대시보드, AlimTalk 템플릿 동기화, SMS·AlimTalk·FriendTalk 캠페인 기록, 발신번호·카카오 프로필 조회, 휴대폰 인증 발송까지 Orbit 설정 화면 하나로 묶어줍니다.

## 무엇을 제공하나요?

- **SendGo Hub** — 최근 30일 캠페인 요약, 메시지 유형 차트, 발신번호·카카오 프로필·동기화 템플릿 현황
- **Orbit 설정 그룹** — `sendgo.*` 자격 증명을 Settings → SendGo에서 관리 (`.env` 값은 UI에서 잠금·마스킹)
- **AlimTalk 템플릿 동기화** — SendGo API → 로컬 `sendgo_templates` 테이블, 관리자에서 Sync 액션
- **캠페인 기록** — SendGo API v2 기준 SMS / AlimTalk / FriendTalk 목록·상세 (읽기 전용)
- **발신자 조회** — SMS 발신번호, Kakao 프로필 목록
- **휴대폰 인증** — `techigh/sendgo-notification` 연동, SMS 또는 AlimTalk 채널 선택

## 요구사항

- PHP `^8.4`
- `cms-orbit/core` `^4.5`
- `techigh/sendgo-notification` `^1.2`

## 설치

```bash
composer require cms-orbit/sendgo:^4.0
php artisan migrate
php artisan sendgo:migrate-config   # auth_sendgo.* 키에서 업그레이드 시
php artisan sendgo:sync-templates   # 선택: AlimTalk 템플릿 초기 동기화
```

`cms-orbit/core`가 먼저 설치·설정(`orbit:install`)되어 있어야 합니다.

## 설정

### 우선순위

1. `.env` — 값이 있으면 Orbit 설정 UI에서 **잠금·마스킹**되어 덮어쓰기 불가
2. `orbit_config('sendgo.*')` — Orbit Settings → SendGo
3. `config/sendgo.php` — 패키지 기본값

### 환경 변수

```env
SENDGO_URL=https://api.sendgo.io
SENDGO_ACCESS_KEY=
SENDGO_SECRET_KEY=
SENDGO_SENDER_KEY=
SENDGO_KAKAO_SENDER_KEY=
SENDGO_API_VERSION=v2
SENDGO_PHONE_VERIFICATION_TEMPLATE_CODE=
```

| 키 | ENV | 기본값 | 설명 |
| --- | --- | --- | --- |
| `url` | `SENDGO_URL` | `https://api.sendgo.io` | SendGo API 베이스 URL |
| `access_key` | `SENDGO_ACCESS_KEY` | — | API Access Key |
| `secret_key` | `SENDGO_SECRET_KEY` | — | API Secret Key |
| `sms_sender_key` | `SENDGO_SENDER_KEY` | — | SMS 발신번호 키 |
| `kakao_sender_key` | `SENDGO_KAKAO_SENDER_KEY` | — | 카카오 발신 프로필 키 |
| `api_version` | `SENDGO_API_VERSION` | `v2` | API 버전 |
| `phone_verification_template_code` | `SENDGO_PHONE_VERIFICATION_TEMPLATE_CODE` | — | AlimTalk 인증 템플릿 코드 |

### 레거시 설정 마이그레이션

`auth_sendgo.*` 키를 쓰던 설치는 아래 명령으로 `sendgo.*`로 이전할 수 있습니다.

```bash
php artisan sendgo:migrate-config
```

| 레거시 키 | 새 키 |
| --- | --- |
| `auth_sendgo.url` | `sendgo.url` |
| `auth_sendgo.access_key` | `sendgo.access_key` |
| `auth_sendgo.secret_key` | `sendgo.secret_key` |
| `auth_sendgo.sms_sender_key` | `sendgo.sms_sender_key` |
| `auth_sendgo.kakao_sender_key` | `sendgo.kakao_sender_key` |
| `auth_sendgo.phone_verification_template_code` | `sendgo.phone_verification_template_code` |

### 휴대폰 인증

Orbit **Authentication & Security**에서 `auth_methods.phone.enabled`가 켜져 있으면 Settings → SendGo에 **Phone verification** 섹션이 표시됩니다.

- **SMS** — `sendgo.sms_sender_key`가 설정되어 있으면 SMS로 인증번호 발송
- **AlimTalk** — `sendgo.phone_verification_template_code`가 필요
- **local/testing** — SendGo 자격 증명이 없으면 로그로 fallback (실제 발송 없음)

## 관리자 화면

설치 후 Orbit **Integrations** 섹션에 SendGo 메뉴가 등록됩니다.

| 화면 | 라우트 이름 | 권한 |
| --- | --- | --- |
| SendGo Hub | `orbit.sendgo.index` | `sendgo.dashboard` |
| SMS 캠페인 | `orbit.sendgo.messages.*` | `sendgo.campaigns` |
| AlimTalk 캠페인 | `orbit.sendgo.notices.*` | `sendgo.campaigns` |
| FriendTalk 캠페인 | `orbit.sendgo.friends.*` | `sendgo.campaigns` |
| SMS 발신번호 | `orbit.sendgo.senders.index` | `sendgo.senders` |
| Kakao 프로필 | `orbit.sendgo.kakao-senders.index` | `sendgo.senders` |
| AlimTalk 템플릿 | `orbit.entities.sendgo-templates.*` | Entity 권한 |

## Artisan 명령

```bash
php artisan sendgo:sync-templates    # AlimTalk 템플릿 API → 로컬 DB
php artisan sendgo:migrate-config    # auth_sendgo.* → sendgo.* 마이그레이션
```

## 데이터베이스

`php artisan migrate` 시 `sendgo_templates` 테이블이 생성됩니다.  
SendGo API에서 동기화한 AlimTalk 템플릿 메타데이터(코드, 제목, 상태, 변수 등)를 로컬에 캐시합니다.

## 업데이트 노트

### 4.2.0

- **`illuminate/http` 와 `illuminate/support` `^11.0 || ^12.0 || ^13.0` → `^13.0`**: Laravel 13 전용으로 좁혔습니다.
- **`cms-orbit/core` `^4.4` → `^4.5`**.
- **왜 좁혔나**: Pest 5 를 채택한 4.4.0(패키지별 4.1.0)부터 `pest-plugin-laravel` 5 가 `laravel/framework ^13.23` 을 요구해, 이 저장소의 테스트가 Laravel 13 으로만 해석됩니다. 즉 Laravel 11·12 호환성을 더 이상 검증할 수 없는 상태로 그 범위를 광고하고 있었습니다. 검증되지 않는 지원 범위를 제약에 남겨두지 않기로 했습니다.
- **Laravel 11·12 사용자는 업그레이드가 필요합니다.** 이번 변경은 실제로 지원 구성을 제거하므로, php 하한 상향과 달리 소비자에게 직접 영향이 있습니다. Laravel 13 으로 올리거나 이전 버전에 머물러야 합니다.

### 4.1.0

- **php 제약 `^8.3` → `^8.4`**: php 8.3 환경에서는 더 이상 설치되지 않습니다.
- **`cms-orbit/core` `^4.1` → `^4.4`**.
- **`pestphp/pest` `^4.0` → `^5.0`**, **`pestphp/pest-plugin-laravel` `^4.0` → `^5.0`** (`require-dev`). Pest 5 가 php `^8.4` 를 요구하는 것이 php 하한을 올린 이유입니다. PHPUnit 도 13.3 으로 함께 올라갑니다.
- **`orchestra/testbench` `^9.0 || ^10.0 || ^11.0` → `^11.0`** (`require-dev`): `pest-plugin-laravel` 5 가 `laravel/framework ^13.23` 을 요구하므로 testbench 9(L11)·10(L12)은 Pest 5 와 함께 설치될 수 없습니다. 해석되지 않는 범위를 남겨두지 않고 사실에 맞게 좁혔습니다.
- **생산 의존은 하나도 바뀌지 않았습니다.** php 하한 상향으로 새로 받게 된 패키지는 Pest 5 계열(`require-dev`)뿐입니다. cms-orbit 전 패키지의 직접 의존 20개를 최신판과 전수 대조했고, 나머지 18개는 이미 php `^8.3` 에서 최신을 받고 있었습니다. 이번 상향은 기능 확보가 아니라 장기 정리 목적입니다.
- **소비자의 Laravel 11·12 지원은 유지됩니다** (`laravel/framework ^11.0 || ^12.0 || ^13.0`).

### 4.0.5

- **php 제약 `^8.3` 복구**: 게시된 태그는 모두 `^8.3` 이었으나 main 에서 `^8.2` 로 내려가 있었습니다. Laravel 13 은 php `^8.3` 을 요구하므로 `php ^8.2` + `laravel/framework ^13` 조합은 php 8.2 환경에서 조용히 Laravel 11 을 설치합니다.
- **`laravel/pint` `^1.14` → `^1.30`** (1.30 이 php `^8.3` 을 요구).
- **`orchestra/testbench` `^10.0` → `^9.0 || ^10.0 || ^11.0`**: 9=Laravel 11, 10=Laravel 12, 11=Laravel 13 이므로 이 패키지가 지원하는 프레임워크 범위와 일치시켰습니다. 이전에는 `^10.0` 만 허용해 Laravel 13 에서 테스트를 돌릴 수 없었습니다.
- **`pestphp/pest` 는 `^4.0` 유지**: Pest 5 는 php `^8.4` 를 요구하는데 이 패키지의 최소 php 는 `^8.3` 입니다. Pest 5 를 받으려면 php 최소 버전을 `^8.4` 로 올려야 해서 보류했습니다.
- **릴리스 파이프라인 도입**: `.githooks/pre-push` 가 composer.json 의 `version` 필드와 태그명이 어긋난 태그의 푸시를 차단합니다. `cms-orbit/core` 의 `4.0.8` 태그가 `version: 4.0.7` 로 만들어져 Packagist 가 아무 오류 없이 그 태그를 무시했고, 4.0.8 이 게시되지 않은 사실을 아무도 알지 못한 사고가 있었습니다. `bin/release <버전>` 이 version 갱신·검증·커밋·태그·푸시를 한 동작으로 묶어 이 드리프트를 원천 차단하고, `cms-orbit/*` 의존이 실제로 Packagist 에 게시되어 있는지 Composer 리졸버로 확인합니다. 저장소를 클론해 `composer install` 하면 `core.hooksPath` 가 자동 설정됩니다.

### 4.0.4

- **프런트 매니페스트 추가**: `resources/orbit/frontend.json`을 제공해 `orbit:frontend-sync`가 `@cms-orbit/sendgo` Vite alias를 자동 관리하도록 했습니다. 이전에는 매니페스트가 없어, 순정 호스트에서 frontend-sync 실행 시 sendgo alias가 누락되어 `Rolldown failed to resolve import "@cms-orbit/sendgo"` 빌드 오류가 발생했습니다.

## License

MIT
