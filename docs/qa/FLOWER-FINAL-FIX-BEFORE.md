# FLOWER FINAL FIX — before-fix

Base: 675ff89d02966de95987a424821cfddf0472ff85 (origin/dev fetched 2026-10-30).

| Bug | Before | Root cause | Minimal fix / risk / regression |
|---|---|---|---|
|001|VERIFIED_OPEN|SalesService replays callback after standalone exception|Remove fallback; retain transaction retries. Boundary + real rollback/races; unsupported deployments must fail closed.|
|002|VERIFIED_OPEN|Admin complete writes status directly|Delegate to SalesService transitions; expose button only for Shipped. Test both endpoints and races.|
|003|VERIFIED_OPEN|Client-only voucher, no backend policy|Remove unfinished voucher controls/calculation, reject submitted voucher; COD totals remain authoritative.|
|004|VERIFIED_OPEN|Hardcoded unverified bank QR|Disable Banking UI and reject backend requests; COD only. Existing order snapshots remain readable.|
|005|VERIFIED_OPEN|Only http/https prefixes rejected|Strict local-path validation for returnUrl and intended URL, including decoded variants. Preserve role destinations.|
|006|VERIFIED_OPEN|Card renders completedOrders|Render totalOrders; test mixed states and empty dataset.|
|007|VERIFIED_OPEN|Script executes in head before forms|Defer load; verify real Chromium suggestions/empty/escape/Enter and existing debounce tests.|
|008|VERIFIED_OPEN|Image/title assertions stale; User login extra dashboard hop; Product tests assume SQL|Test real bundled image resolution/current heading; direct User home destination; integrate Product tests with Mongo + owned ephemeral DB guard.|
|009|VERIFIED_OPEN|34 files violate Pint|Separate formatting after functional diff review; run full style + regression.|

Evidence reviewed: original BUGS/REPORT/TEST_MATRIX, retest REPORT, HTTP/transaction/readback/browser evidence; TIEN-05 report and diff. No feature branches merged again. All work confined to new worktree; QA clone/server/data preserved.
