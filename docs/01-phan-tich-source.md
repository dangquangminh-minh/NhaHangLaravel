# Phân tích dự án C# trước khi chuyển đổi

## Nguồn đã kiểm tra

- `DoAn_LTW_NhaHang.csproj`: .NET Framework 4.7.2, ASP.NET MVC 5; `packages.config` có Entity Framework 6.4.4.
- `App_Start/RouteConfig.cs`: mặc định `Home/TrangChu`. `Global.asax`, BundleConfig, FilterConfig và Web.config là cấu hình MVC cổ điển.
- `NhaHang.edmx`, `NhaHang.Context.cs`, toàn bộ `tbl*.cs`: EF Database First, DbContext `QL_NhaHangEntities`, SQL Server.
- Sáu controller: `HomeController`, `UserController`, `CartController`, `AdminController`, `NhanVienController`, `MobileApiController` (file tên `MoblieApiController.cs`).
- Models giỏ hàng: `Cart`, `CartItem`; `ThongKeViewModel` hỗ trợ báo cáo.
- Views Home/User/Cart/Admin/NhanVien/Shared: Razor `.cshtml`, layout theo khách/admin/chủ; Bootstrap, một số CDN/font/QR bên ngoài.
- SQL gốc: `../Quản Lí Nhà Hàng.sql`, nằm ở thư mục cha dự án C#. Ảnh: `Content/Images`, `TrangChu/img`. Có Bootstrap 5.2.3 ở Content và Scripts.

Phân tích bằng cách đọc source, không chạy ứng dụng C# trên SQL Server. Không có bằng chứng kiểm thử thời gian chạy C# hoặc dữ liệu thật đang vận hành. Không tuyên bố các luồng bị lỗi trong source đã hoạt động đúng.

## Đề tài thực tế

**Website quản lý nhà hàng và đặt món trực tuyến — Nhà Hàng Ngon**. Khách xem thực đơn, đặt món, đặt bàn, quản lý hồ sơ và tích điểm. Nhân viên quản lý món, loại món, đơn và thống kê. Chủ quản lý thêm nhân viên, khách hàng, voucher.

Không thấy tầng service riêng; phần lớn nghiệp vụ nằm trong controller. `Cart` lưu giỏ trong session, `CartItem` đọc món qua EF. Phân quyền dựa vào `Session["User"]`, `Session["Admin"]`; chủ có `VaiTro == 11`.

## Quy tắc lấy từ code

1. Tìm món theo tên, lọc loại; top 10 dựa số lượng trong đơn có `TinhTrang == 4`.
2. Món thuộc một loại và một nhà cung cấp; album hình thuộc món. Loại có món thì không xóa.
3. Hóa đơn có nhiều chi tiết, mỗi món xuất hiện một dòng; chi tiết giữ số lượng và đơn giá.
4. Trạng thái: 1 chờ, 2 chế biến, 3 phục vụ/giao, 4 đã thanh toán, 5 hủy. Khách chỉ được hủy khi chờ và chỉ xem đơn của mình.
5. Điểm: `floor(tổng tiền các đơn trạng thái 4 / 10000) * 10 - tổng điểm đã đổi`. Làm tròn trên tổng tiền của khách, không làm tròn riêng từng hóa đơn.
6. Voucher có giá trị giảm cố định, điểm đổi, số lượng, ngày hết hạn. Lịch sử đổi có mã riêng, số điểm đã trừ và trạng thái dùng.
7. Đặt bàn mặc định chờ; số người dương. Địa chỉ đầu tiên là mặc định; chọn mặc định mới bỏ mặc định cũ.
8. Chỉ chủ quản lý nhân viên, khách hàng và voucher. Các vai trò nhân viên khác nhau không có sự phân quyền chi tiết ở `AdminController` ngoài kiểm tra session admin.
9. Liên hệ lưu người gửi/nội dung/trạng thái đọc. Bình luận mobile đang dùng bảng liên hệ, nhận diện món bằng chuỗi trong chủ đề.

## Các điểm không nhất quán / cần sửa

- SQL tạo `tblDatBan` hai lần. SQL có `tblKhachHang.DiaChi` nhưng entity EDMX/CS không có thuộc tính tương ứng.
- Comment Boss không nhất quán với vai trò gán trong INSERT SQL; controller lại kiểm tra 11.
- Không hash mật khẩu; nhiều thao tác ghi/xóa dùng GET hoặc thiếu chống CSRF/validation.
- Cập nhật hồ sơ web/mobile tin ID gửi từ client; nhiều API cá nhân không có cơ chế token xác thực.
- API đặt món mobile tin tổng tiền/đơn giá gửi lên.
- `Cart.ApDungVoucher` xóa lịch sử và cộng lại tồn kho ngay khi áp dụng; làm sai điểm và trái với đoạn checkout đánh dấu đã dùng.
- Khách tự xác nhận chuyển khoản sẽ đổi đơn thành đã thanh toán và được tính điểm, không có xác minh ngân hàng.
- Quên mật khẩu đặt lại ngay rồi gửi mật khẩu rõ. Source có thông tin SMTP gắn cứng; bản Laravel không sao chép các thông tin đó. Chủ source nên thu hồi/đổi thông tin đã lộ.
- `ToggleYeuThich` chỉ trả success, không lưu dữ liệu; không coi đây là tính năng yêu thích đã triển khai.

## Đối chiếu thành phần

| C# | Laravel thực tế |
|---|---|
| ASP.NET MVC / RouteConfig | `routes/web.php`, controller MVC và route có tên |
| EF6 / QL_NhaHangEntities | Eloquent model và quan hệ; migration MySQL |
| tblVaiTro | `roles` / Role; ba mức user/admin/owner |
| tblKhachHang + tblNhanVien | `users` / User Authenticatable, FK role_id; phân biệt qua role |
| tblLoaiMon | `categories` / Category; CategoryController + CategoryRequest |
| tblNCC | `suppliers` / Supplier; SupplierController + SupplierRequest |
| tblMonAn | `dishes` / Dish; DishController + DishRequest |
| tblHinhAnh | `dish_images` / DishImage, album ảnh |
| tblTinhTrang | `order_statuses` / OrderStatus, dữ liệu tham chiếu cố định |
| tblHoaDon | `orders` / Order; OrderController, OrderPolicy |
| tblChiTietHoaDon | `order_items` / OrderItem; unique(order_id,dish_id) |
| tblVoucher | `vouchers` / Voucher; VoucherController, chủ quản lý |
| tblLichSuDoiDiem | `voucher_redemptions` / VoucherRedemption; LoyaltyService |
| tblDiaChiGiaoHang | `addresses` / Address; AddressController, AddressPolicy |
| tblDatBan | `reservations` / Reservation; ReservationController |
| tblLienHe | `contacts` / Contact; tách đánh giá món sang `reviews` / Review với FK rõ |
| HomeController | MenuController, ReservationController, ContactController |
| UserController | AuthController, ProfileController, OrderController; Auth/Password Laravel |
| Cart + CartItem + CartController | Giỏ trong session chỉ lưu ID/số lượng; CartController + OrderService |
| AdminController | Category/Dish/Supplier/Order/DashboardController |
| NhanVienController | AccountController (nhóm staff/customers), VoucherController |
| MobileApiController | Chuyển nghiệp vụ địa chỉ/đánh giá/đặt món/hồ sơ/voucher sang Blade có xác thực; không giữ hợp đồng JSON cũ |
| ThongKeViewModel | DashboardController lấy dữ liệu Eloquent, view `admin/dashboard.blade.php` |
| Session xác thực tự viết | Laravel Auth session guard, hash password, regenerate/invalidate session |
| Layout Razor | `resources/views/layouts/app.blade.php`, component field/delete và partial dùng chung |
| SQL Server IDENTITY/NVARCHAR/BIT/GETDATE | MySQL BIGINT AUTO_INCREMENT/VARCHAR utf8mb4/BOOLEAN và thời gian qua Laravel |

Chỉ tách OrderService và LoyaltyService vì hai nghiệp vụ có transaction, khóa bản ghi và được dùng nhiều chỗ. Không dùng repository, microservice hoặc kiến trúc nhiều tầng cho các CRUD đơn giản.
