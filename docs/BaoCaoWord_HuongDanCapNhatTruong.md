# Cập nhật trường trong báo cáo Word

1. Mở BaoCaoDoAn_QuanLyNhaHang_Laravel_MySQL.docx bằng Microsoft Word. Bản bàn giao đã được cập nhật bằng Word16 và xuất PDF; không cần cập nhật chỉ để xem.
2. Sau khi điền/sửa, nhấn **Ctrl+A → F9**; ở mục lục chọn **Update entire table**. Cập nhật lại lần nữa nếu số trang thay đổi do mục lục dài hơn. Lưu file.
3. Nếu muốn xem mã trường, nhấn **Alt+F9**; nhấn lại để về kết quả. Không gõ đè số chương, caption, tham chiếu hoặc số trang.
4. Tiêu đề dùng Heading1/2/3 và numbering đa cấp. Mở đầu/Kết luận/Phụ lục dùng style tiêu đề không số; đừng tự gõ thêm số vào Heading đã có numbering.
5. Caption dùng STYLEREF + SEQ Hinh/Bang; tham chiếu dùng REF/bookmark. Danh mục hình/bảng là TOC theo chuỗi SEQ. Khi thêm hình/bảng, dùng References → Insert Caption/Cross-reference phù hợp hoặc giữ nguyên cơ chế field của bản mẫu. Kiểm tra lại cả danh mục sau sửa.
6. Giữ section break: hai bìa không số, phần đầu La Mã, phần chính Arabic bắt đầu1. Footer PAGE căn giữa; không gõ số trang bằng tay.
7. Kiểm tra các bảng dài, nguồn bảng ở đầu trang sau, ảnh/ERD tổng quan và các trang vừa đổi. Giữ chữ bảng13; có thể dùng Keep with next cho header cùng dòng đầu hoặc đưa cả phần mở bảng sang trang sau, rồi kiểm tra lại dung lượng.
8. Chọn File → Export → Create PDF/XPS (Standard để in). PDF mới phải xuất từ DOCX đã lưu và cập nhật field. PDF bàn giao cũ không tự thay đổi khi sửa Word.
9. Điền [CẦN BỔ SUNG] bằng thông tin thật; kiểm tra phân công, phê duyệt giảng viên và quy định khai báo AI. Nhóm tự đọc lại, đối chiếu source trước khi nộp.

Nguồn sơ đồ DOT/SVG và PNG nằm trong docs/BaoCaoWord_SoDo. Chỉnh DOT bằng Graphviz, thay ảnh đúng tỷ lệ; không kéo giãn hoặc tăng khoảng trắng để đủ trang. Bản kiểm định riêng ghi rõ số trang và các giới hạn còn lại.
