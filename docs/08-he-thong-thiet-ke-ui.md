# Giai đoạn B — Bộ token và mẫu giao diện để duyệt

Phạm vi/phong cách đã được người dùng xác nhận sau Giai đoạn A ngày 18/10/2026. Tài liệu này phản ánh **trang mẫu riêng**; chưa triển khai giao diện đại trà vào nghiệp vụ (Giai đoạn C).

## Mở mẫu

**http://127.0.0.1:18080/ui-style-guide.html**

Chạy cùng Apache hiện có. Nếu cài tại URL khác, mở đường dẫn /ui-style-guide.html trên host đó. Không cần đăng nhập, npm hay dependency mới. Dữ liệu/giá/tên trong mẫu đều minh họa, không đọc database và không gửi request tạo/sửa/xóa nghiệp vụ. Menu bên trái là mục lục của style guide, không thay menu phân quyền của ứng dụng.

- [Ảnh desktop](ui-stage-b-desktop.png)
- [Ảnh mobile 360px](ui-stage-b-mobile.png)
- [Ảnh form và phản hồi](ui-stage-b-forms.png)
- [Kết quả kiểm tra Chrome](ui-stage-b-checks.json)

Đây là ảnh mẫu thiết kế mới để so với baseline Giai đoạn A trong .runtime/ui-audit-before, không phải ảnh sau khi đã sửa toàn bộ ứng dụng.

## Định hướng

Xanh lá đậm, nền sáng trung tính và điểm nhấn ấm. Font hệ thống hỗ trợ tiếng Việt; ba mức đậm 400/600/700. Nội dung chính từ 14px. Bảng có phân cấp rõ, số liệu căn phải, trạng thái có chữ; form và nút cao 44px. Không có dark mode trong phạm vi được duyệt.

## File mới

| File | Vai trò |
|---|---|
| public/assets/ui-tokens.css | Nguồn duy nhất của màu, chữ, spacing, bo góc, bóng, thời lượng và easing |
| public/assets/ui-components.css | Component styles dùng token, chỉ nạp bởi trang mẫu |
| public/assets/ui-preview.js | Tương tác minh họa: chọn/lọc/sắp xếp dữ liệu mẫu, modal, toast, form, số lượng, motion |
| public/ui-style-guide.html | Trang độc lập để duyệt tất cả thành phần |
| docs/08-he-thong-thiet-ke-ui.md | Hướng dẫn token, cách dùng và trạng thái duyệt |
| docs/ui-stage-b-*.png, ui-stage-b-checks.json | Ảnh và chứng cứ kiểm tra |

Không nạp file mới vào layout ứng dụng trước khi được duyệt Giai đoạn B. Không sửa PHP, controller, route, database, tên biến server, field hiện có hay phân quyền. SHA-256 đã đối chiếu 141 file gốc của app/views/routes/database và app.css/app.js, không có file nào thay đổi.

## Bảng token mặc định

Tên dùng tiền tố --ui- để tránh xung đột. Các token duration/easing theo tên yêu cầu; giá trị trong bảng là mặc định trước override giảm chuyển động.

| Token | Giá trị mặc định |
|---|---|
| --ui-primary | #1f5a43 |
| --ui-primary-hover | #174633 |
| --ui-primary-strong | #153c30 |
| --ui-primary-soft | #edf5ef |
| --ui-primary-selected | #dcebdd |
| --ui-accent | #9c4f25 |
| --ui-accent-soft | #fbf0e5 |
| --ui-success | #236244 |
| --ui-success-soft | #edf5ee |
| --ui-warning | #825307 |
| --ui-warning-soft | #fff4dc |
| --ui-danger | #b4313a |
| --ui-danger-soft | #fff0f0 |
| --ui-info | #245c75 |
| --ui-info-soft | #edf5fb |
| --ui-canvas | #f5f7f3 |
| --ui-surface | #ffffff |
| --ui-surface-muted | #f0f3ee |
| --ui-text | #1c3026 |
| --ui-text-muted | #58675e |
| --ui-border | #dce4da |
| --ui-border-input | #7c8b82 |
| --ui-inverse | #ffffff |
| --ui-disabled-bg | #e6ebe4 |
| --ui-disabled-text | #536158 |
| --ui-shimmer-highlight | rgba(255, 255, 255, .55) |
| --ui-shadow-color | 24, 48, 40 |
| --ui-font | system-ui, -apple-system, 'Segoe UI', sans-serif |
| --ui-text-xs | .75rem |
| --ui-text-sm | .875rem |
| --ui-text-base | 1rem |
| --ui-text-lg | 1.25rem |
| --ui-text-xl | 1.5rem |
| --ui-text-2xl | 2rem |
| --ui-weight-regular | 400 |
| --ui-weight-medium | 600 |
| --ui-weight-bold | 700 |
| --ui-leading | 1.55 |
| --ui-space-1 | .25rem |
| --ui-space-2 | .5rem |
| --ui-space-3 | .75rem |
| --ui-space-4 | 1rem |
| --ui-space-6 | 1.5rem |
| --ui-space-8 | 2rem |
| --ui-space-12 | 3rem |
| --ui-radius-sm | .5rem |
| --ui-radius-md | .875rem |
| --ui-radius-lg | 1.25rem |
| --ui-shadow-sm | 0 1px 2px rgba(var(--ui-shadow-color), .04), 0 6px 20px rgba(var(--ui-shadow-color), .04) |
| --ui-shadow-md | 0 8px 28px rgba(var(--ui-shadow-color), .10) |
| --ui-focus | 0 0 0 3px var(--ui-surface), 0 0 0 5px var(--ui-primary) |
| --ui-touch-target | 44px |
| --ui-control-height | 44px |
| --ui-header-height | 72px |
| --ui-content-max | 1440px |
| --ui-stagger-step | 25ms |
| --dur-fast | 120ms |
| --dur-base | 200ms |
| --dur-slow | 320ms |
| --ease-out | cubic-bezier(.22, 1, .36, 1) |
| --ease-in-out | cubic-bezier(.4, 0, .2, 1) |

Spacing tương ứng 4/8/12/16/24/32/48px; radius 8/14/20px. Token chữ .75rem chỉ dùng nhãn phụ; nội dung chính .875rem trở lên.

## Các component mẫu

| Nhóm | Class/hook | Hành vi |
|---|---|---|
| Layout | ui-topbar, ui-shell, ui-sidebar, ui-breadcrumb | Header sticky, mục lục có mục đang chọn, drawer mobile và skip link |
| Bảng | ui-table, ui-table-scroll, ui-table-body, ui-numeric | Header sticky, cuộn trong khung, hover/focus/chọn dòng, số căn phải, tabular-nums, giới hạn animation 12 hàng đầu |
| Thao tác | ui-button, ui-button--primary/quiet/danger/icon | Vùng bấm 44px, hover bằng transform, focus-visible, disabled/loading rõ |
| Badge | ui-badge--success/warning/danger/info | Nhãn chữ đi cùng màu ngữ nghĩa |
| Form | ui-field, ui-input, ui-help, ui-error, ui-input-group | Focus, readonly/disabled, lỗi có aria-invalid và aria-describedby khi invalid |
| Card | ui-card, ui-kpi, ui-food | Bố cục và khoảng cách dùng token, ảnh giữ tỷ lệ |
| Phản hồi | ui-alert, ui-state, ui-skeleton, ui-toast, ui-dialog | Loading/rỗng/lỗi, retry, thông báo đóng được, modal có Esc và trả focus |
| Chuyển động | --dur-fast/base/slow, --ease-out/in-out | 120/200/320ms, giới hạn hàng, hỗ trợ prefers-reduced-motion và nút tắt mẫu |

Sắp xếp trong style guide chỉ đổi thứ tự **6 dòng mẫu ở trình duyệt** để minh họa aria-sort; chưa bổ sung truy vấn hay sorting cho bảng thật.

## Cách dùng và tùy chỉnh

Thứ tự CSS của mẫu: Bootstrap 5.2.3 → ui-tokens.css → ui-components.css. JS dùng Bootstrap bundle đang có và ui-preview.js; không nạp app.js vào trang mẫu để tránh trộn với handler nghiệp vụ.

### Đổi màu

Sửa --ui-primary, --ui-primary-hover, --ui-primary-strong và các nền soft/selected trong ui-tokens.css. Điểm nhấn dùng --ui-accent/soft. Sau thay màu cần đo lại tương phản chữ/nền, focus và viền input; không chỉ đổi một màu hover rời rạc.

### Tắt chuyển động

Mẫu tự tắt chuyển động khi hệ điều hành yêu cầu prefers-reduced-motion: reduce. Có thể đặt data-ui-motion="off" trên html để tắt thủ công; nút cuối trang cho thử trực tiếp. Các transition của modal/backdrop/drawer trong mẫu cũng dùng duration token. Không dùng will-change hàng loạt, không animate kích thước/vị trí gây reflow.

Row fade/translate tối đa 12 hàng, stagger 25ms. Skeleton shimmer giới hạn hai lượt, không lặp vô hạn; spinner chỉ chạy lúc chờ. Không hạ opacity nút thao tác xuống .6 vì sẽ giảm tương phản; phản hồi bằng nền/viền/focus thay thế. KPI hiển thị giá trị ngay, không count-up để số tiền và số liệu luôn dễ đọc, ổn định.

### Thêm bảng theo mẫu

Dùng ui-card bọc ui-table-scroll và table.ui-table; th khai scope="col", số tiền dùng ui-numeric, thao tác dùng ui-button. Giữ caption/label cho trình đọc màn hình. Chỉ đặt aria-sort và icon khi có hành vi sắp xếp thật. Không đặt handler minh họa data-ui-* vào form nghiệp vụ; Giai đoạn C sẽ dùng component/partial phù hợp với các hook đang tồn tại.

### Form lỗi

Giữ nguyên name/id/action đang có trong ứng dụng. Khi lỗi, thêm aria-invalid="true" và nối aria-describedby tới thông báo có id riêng. Khi hợp lệ, bỏ tham chiếu lỗi đã ẩn, đồng thời giữ các mô tả trợ giúp khác. Không chỉ ẩn lỗi bằng CSS mà vẫn để trình đọc màn hình đọc thông báo cũ.

## Kiểm tra đã thực chạy

**34 kiểm tra Chrome headless đạt**, gồm:

- 360/768/1024/1440px không tràn ngang document; bảng cuộn trong khung; nút hiển thị trong mẫu tối thiểu 44px.
- Skip link/Tab, drawer mobile/Esc, modal chi tiết/trả focus, modal xác nhận có tên đối tượng.
- Lọc/chọn dòng mẫu, header sticky, aria-sort khớp thứ tự dữ liệu mẫu.
- Lỗi form liên kết đúng khi invalid, không tham chiếu lỗi ẩn khi valid; loading/disabled, toast và retry.
- Gõ số lượng 49 cho tổng mẫu chính xác; không gửi vào giỏ hàng thật.
- prefers-reduced-motion, nút tắt motion, không lỗi JavaScript hay lỗi tài nguyên trong lần kiểm tra.

Các cặp màu đã đo từ computed styles:

| Thành phần | Tương phản chữ/nền |
|---|---|
| body | 12.99:1 |
| .ui-muted | 5.54:1 |
| .ui-button--primary | 8.07:1 |
| a.ui-button--primary | 8.07:1 |
| .ui-badge--warning | 6.03:1 |
| .ui-badge--danger | 5.51:1 |
| .ui-badge--success | 6.51:1 |
| .ui-badge--info | 6.66:1 |
| .ui-input | 14:1 |

Đây là đo một số cặp màu mẫu, không phải chứng nhận accessibility toàn hệ thống. Chưa kiểm thử Edge/Firefox/Safari, điện thoại vật lý, trình đọc màn hình, Lighthouse/FPS hay nghiệm thu toàn bộ chức năng ứng dụng. Các kiểm thử PHP trước đó không được tính là kiểm thử riêng cho trang mẫu này.

Tổng CSS/JS mới của mẫu: **37548 byte trước nén**, không tính HTML và Bootstrap/ảnh đã có. Các tài nguyên này chưa được nạp vào ứng dụng, nên chưa có số đo so sánh hiệu năng ứng dụng sau nâng cấp.

## Sao lưu / hoàn tác

.runtime/ui-stage-b-backup chứa bản sao views, app.css/app.js và báo cáo khảo sát; manifest.json ghi hash file gốc. Vì Giai đoạn B chỉ thêm file độc lập, bỏ các file mới sẽ loại bỏ mẫu; chưa có file nghiệp vụ cần phục hồi. Không xóa backup/database. Không có dependency, migration hay thay đổi cấu hình server cần hoàn tác.

## Điểm duyệt tiếp theo

Theo Giai đoạn B trong yêu cầu: **“DỪNG và chờ xác nhận phong cách.”**

Chờ người dùng duyệt hoặc chỉnh màu, khoảng cách, mật độ bảng, bo góc và phản hồi của mẫu. Sau khi duyệt mới thực hiện Giai đoạn C lên các trang thật theo nhóm component, giữ nguyên luồng tìm kiếm tức thì, giỏ hàng/nhập số lượng, QR trước chi tiết và quyền báo cáo Thu ngân/Chủ.
