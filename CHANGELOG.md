# Changelog

이 문서는 `cms-orbit/sendgo`의 릴리스 노트를 기록합니다.

## 4.1.0 - 2026-08-31

### 변경

- **php 제약 `^8.3` → `^8.4`**: php 8.3 환경에서는 더 이상 설치되지 않습니다.
- **`cms-orbit/core` `^4.1` → `^4.4`**.
- **`pestphp/pest` `^4.0` → `^5.0`**, **`pestphp/pest-plugin-laravel` `^4.0` → `^5.0`** (`require-dev`). Pest 5 가 php `^8.4` 를 요구하는 것이 php 하한을 올린 이유입니다. PHPUnit 도 13.3 으로 함께 올라갑니다.
- **`orchestra/testbench` `^9.0 || ^10.0 || ^11.0` → `^11.0`** (`require-dev`): `pest-plugin-laravel` 5 가 `laravel/framework ^13.23` 을 요구하므로 testbench 9(L11)·10(L12)은 Pest 5 와 함께 설치될 수 없습니다. 해석되지 않는 범위를 남겨두지 않고 사실에 맞게 좁혔습니다.

### 안내

- **생산 의존은 하나도 바뀌지 않았습니다.** php 하한 상향으로 새로 받게 된 패키지는 Pest 5 계열(`require-dev`)뿐입니다. cms-orbit 전 패키지의 직접 의존 20개를 최신판과 전수 대조했고, 나머지 18개는 이미 php `^8.3` 에서 최신을 받고 있었습니다. 이번 상향은 기능 확보가 아니라 장기 정리 목적입니다.
- **소비자의 Laravel 11·12 지원은 유지됩니다** (`laravel/framework ^11.0 || ^12.0 || ^13.0`).

## 4.0.5 - 2026-08-28

### 수정

- **php 제약 `^8.2` → `^8.3` 복구**: 게시된 태그는 모두 `^8.3`이었으나 main에서 `^8.2`로 내려가 있었습니다. Laravel 13은 php `^8.3`을 요구하므로 `php ^8.2` + `laravel/framework ^13` 조합은 php 8.2 환경에서 조용히 Laravel 11을 설치합니다.
- **`laravel/pint` `^1.14` → `^1.30`** (1.30이 php `^8.3`을 요구).
- **`orchestra/testbench` `^10.0` → `^9.0 || ^10.0 || ^11.0`**: 9=Laravel 11, 10=Laravel 12, 11=Laravel 13이므로 지원 프레임워크 범위와 일치시켰습니다. 이전에는 `^10.0`만 허용해 Laravel 13에서 테스트를 돌릴 수 없었습니다.
- 휴대폰 인증 SMS 본문의 소스 키를 영문으로 바꿨습니다.

### 추가

- **릴리스 파이프라인 도입**: `.githooks/pre-push`가 composer.json의 `version` 필드와 태그명이 어긋난 태그의 푸시를 차단합니다. `cms-orbit/core`의 `4.0.8` 태그가 `version: 4.0.7`로 만들어져 Packagist가 아무 오류 없이 그 태그를 무시했고, 4.0.8이 게시되지 않은 사실을 아무도 알지 못한 사고가 있었습니다. `bin/release <버전>`이 version 갱신·검증·커밋·태그·푸시를 한 동작으로 묶어 드리프트를 원천 차단하고, `cms-orbit/*` 의존이 실제로 Packagist에 게시되어 있는지 Composer 리졸버로 확인합니다. 클론 후 `composer install` 시 `core.hooksPath`가 자동 설정됩니다.

### 안내

- **`pestphp/pest`는 `^4.0` 유지**: Pest 5는 php `^8.4`를 요구하는데 이 패키지의 최소 php는 `^8.3`입니다. Pest 5를 받으려면 php 하한을 올려야 해서 보류했습니다.

## 4.0.4 - 2026-08-28

### 수정

- **프런트 매니페스트 추가**: `resources/orbit/frontend.json`을 제공해 `orbit:frontend-sync`가 `@cms-orbit/sendgo` Vite alias를 자동 관리하도록 했습니다. 이전에는 매니페스트가 없어 순정 호스트에서 frontend-sync 실행 시 alias가 누락되어 `Rolldown failed to resolve import "@cms-orbit/sendgo"` 빌드 오류가 발생했습니다.

## 4.0.3 - 2026-08-28

### 추가

- **`SendgoAlimtalkSender`**: 사용량 추적을 위한 발송 로그를 남기는 알림톡 발송기를 추가했습니다.

### 개선

- README 한글 설명을 보완했습니다.

## 4.0.2 - 2026-07-06

### 추가

- `SendgoHubDashboard` 서비스와 React `sendgo-hub-dashboard` 레이아웃을 추가했습니다.
- 최근 캠페인, 메시지 유형 비율 차트, 발신번호·카카오 프로필·동기화 템플릿 개요를 표시합니다.

### 변경

- SendGo Hub를 크레딧 메트릭/링크 테이블에서 대시보드 UI로 전환했습니다.

### 개선

- Hub 관련 한글팩을 보강했습니다(대시보드 새로고침, 설정 안내, 채널·템플릿 문구).

## 4.0.1 - 2026-07-05

### 변경

- `cms-orbit/core` `4.0.1`(RichText breaking change) 호환 릴리스로 의존성을 정렬했습니다.

## 4.0.0 - 2026-07-05

### 추가

- SendGo Hub(크레딧 개요, 빠른 링크), Orbit 설정 그룹, 알림톡 템플릿 동기화, SMS·알림톡·친구톡 캠페인 기록 화면, 발신번호·카카오 프로필 목록을 제공합니다.
- `techigh/sendgo-notification` 기반 휴대폰 인증 발송(`PhoneVerificationSender`)을 통합했습니다.
- `sendgo:sync-templates`, `sendgo:migrate-config` Artisan 명령을 추가했습니다.
- `sendgo_templates` 테이블 단일 create 마이그레이션을 제공합니다.

### 변경

- `cms-orbit/core` 4.0.0 릴리스 라인에 맞춰 패키지 버전을 `4.0.0`으로 정렬했습니다.
- SendGo API 자격 증명·발신키·휴대폰 인증 설정을 코어 인증 그룹에서 독립 **SendGo** 설정 그룹으로 이전했습니다.
- 설정 허브 **API 연동** 섹션(`hubSection: api`)에 SendGo 설정을 배치했습니다.

### 개선

- SendGo 관리자 메뉴·허브·캠페인·발신번호·설정 화면 한글팩을 보강했습니다.
- 번역 경로를 `register()`에서 등록해 PHP `__()` 경로에서도 한글팩이 안정적으로 로드되도록 했습니다.
- SendGo Hub 개요 메뉴에 `active` URL 패턴을 적용해 하위 화면에서도 활성 상태가 유지되도록 했습니다.
- 템플릿 Entity는 허브 하위 메뉴로만 노출하고 사이드바 중복 항목을 숨깁니다.

### 수정

- 레거시 `auth_sendgo.*` orbit config 키를 `sendgo.*`로 이전하는 `sendgo:migrate-config` 명령을 제공합니다.
