# Changelog

이 문서는 `cms-orbit/sendgo`의 릴리스 노트를 기록합니다.

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
