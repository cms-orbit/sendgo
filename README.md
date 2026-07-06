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

- PHP `^8.3`
- `cms-orbit/core` `^4.0.1`
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

## License

MIT
