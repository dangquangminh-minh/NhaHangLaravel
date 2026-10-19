# Kiểm định báo cáo Word/PDF — bản bàn giao

Báo cáo: BaoCaoDoAn_QuanLyNhaHang_Laravel_MySQL.docx và PDF cùng tên, đặt ngay cạnh README.md. Ngày kiểm định: 18/10/2026. Nội dung dựa trên bản Laravel hiện tại; người dùng đã duyệt A, B và cho phép tạo Word/PDF.

## Kết quả đo thực tế

- Xuất PDF bằng **Microsoft Word 16.0**, cập nhật trường và phân trang thực tế; PyMuPDF kiểm tra PDF và xuất ảnh từng trang.
- **50 trang nội dung chính**, từ Mở đầu đến hết Kết luận, tương ứng trang PDF vật lý **14–63**, số trang in **1–50**.
- **89 trang toàn tài liệu**. Bìa, phần đầu, tài liệu tham khảo và phụ lục không tính vào phần chính.
- **13,967 từ phần chính; 19,764 từ toàn tài liệu**, theo Word ComputeStatistics; bảng có tính trong phạm vi phần chính. Không dùng ước lượng số từ để giả định số trang.
- **9 nguồn chính thức**, mở đọc trước khi dùng, IEEE [1]–[9] đúng thứ tự xuất hiện. Không đặt năm xuất bản giả cho tài liệu web liên tục cập nhật.
- **15 hình**: 8 sơ đồ tự dựng và 7 ảnh hệ thống đã chạy với dữ liệu demo. **52 bảng đánh số**, trong đó 27 bảng chính và 25 bảng phụ lục; 1 bảng từ viết tắt không đánh số.
- Có **67 trường SEQ**, **67 REF** với bookmark hợp lệ, 3 TOC (mục lục, danh mục hình, danh mục bảng), PAGE trong footer, 3 section. Số field Word ghi nhận sau cập nhật là 527 (kể cả các field kết quả/danh mục).
- Đã xuất và xem contact sheet của **toàn bộ 89 trang**, đối chiếu ảnh riêng của các sơ đồ. Có bản render ban đầu và 3 chu trình kiểm tra → sửa → render lại (4 bản xuất); đây là giới hạn đã đặt, không tiếp tục sửa vô hạn.

## Checklist nghiệm thu

| Hạng mục | Trạng thái | Căn cứ / ghi chú |
|---|---|---|
| Nội dung đúng phạm vi Laravel | Đạt | Có đặc tả 10 UC; tiền, quyền, trạng thái, bàn/voucher, snapshot và giới hạn bám source. |
| Không tự nhận chạy lại test ứng dụng | Đạt | Nêu rõ XML/JSON là bằng chứng đợt trước; chỉ công cụ tạo/render tài liệu chạy trong nhiệm vụ này. |
| Thông tin hành chính, phân công thật | Chưa kiểm chứng | Để [CẦN BỔ SUNG]; nhóm phải điền, ký và xác nhận phê duyệt GV. |
| Nguồn và trích dẫn | Đạt | 9 trang chính thức đã mở; thứ tự trích khớp danh mục; không dùng nguồn lỗi truy cập. |
| Heading / numbering | Đạt | Styles và đa cấp liên kết; 5 chương, Mở đầu, Kết luận; TOC đến cấp 3. |
| A4 / lề / font / dòng | Đạt | TNR13 body/bảng/chú thích; H1 16, H2 14, H3 13; lề 3,5/2/2,5/2,5cm; body 1,5. Công thức là OMML native. |
| TOC / PAGE / SEQ / REF | Đạt | Cập nhật bằng Word; không có Error! hoặc bookmark thiếu. Bìa không số, đầu La Mã, chính bắt đầu 1. |
| Bảng và vùng in | Đạt | Bảng dùng chữ13, giãn đơn, repeat header, cantSplit hàng; không phát hiện tràn ngang văn bản. |
| Ngắt trang bảng dài | Đạt trong lượt rà soát | Bảng dài tiếp trang và lặp header. Đã đặt keep-next cho header và dòng cuối để giữ caption/dữ liệu đầu cùng nhau, giữ nguồn theo dòng cuối. |
| Hình và sơ đồ | Đạt có lưu ý | UML có actor/ranh giới/include/extend; ERD đối chiếu Migration. ERD tổng quan và ảnh màn hình có chữ nhỏ khi in A4; dùng sơ đồ trọng tâm/từ điển hoặc phóng to PDF, nguồn PNG/DOT đi kèm. |
| Trang trắng, ký tự lỗi | Đạt | Không có trang trắng ngoài ý muốn; không phát hiện chuỗi lỗi field, bảng cha FK viết sai hoặc số tài khoản thật trong văn bản. Không dùng ảnh QR chứa tài khoản thật. |
| Ngôn ngữ / thuật ngữ | Đạt trong lượt rà soát | Dùng nhất quán cách diễn đạt Báo cáo/Hệ thống; không bịa khảo sát, thành viên hay phê duyệt. Nhóm vẫn phải đọc lại trước nộp. |
| Dung lượng | Đạt | 50 trang phần chính, xác nhận bằng Word/PDF thực tế. |
| Source và DB được giữ nguyên | Đạt | 39 file sửa/thêm của đợt UI và 84 file backend được đối chiếu hash; không có lệch. Không chạy thao tác DB. |
| Cài đặt ở máy khác / SMTP / ngân hàng / tải | Chưa kiểm chứng | Giới hạn trình bày trong Chương 5; không công bố thành công ngoài bằng chứng. |

## Kết quả ứng dụng được trích dẫn, không chạy lại ở lần viết báo cáo

- SQLite: 118 tests, 598 assertions; MySQL: cùng 118 tests, 598 assertions; mỗi tệp có 0 failure/error/skipped. Không cộng thành 236 ca khác nhau.
- Chrome: 71 + 16 + 15 = 102 checks, không phải người dùng khảo sát. Chưa có coverage, benchmark tải, Lighthouse hoặc kết luận mọi trình duyệt.
- Hạn chế source đã nêu: email hồ sơ còn UI đầy đủ, top món trang chủ khác lọc thực đơn, loại thống kê dùng danh mục hiện tại, quyền vận hành phần lớn chung, nhận tiền/hoàn tiền thủ công.

## Tệp truy vết và lưu ý trước khi nộp

- Bằng chứng riêng: BaoCaoWord_BangDoiChieuBangChung.csv và BaoCaoWord_GiaiDoanA_BangChung.md.
- Bản đo tự động: BaoCaoWord_KiemDinh.json, gồm hash DOCX/PDF, styles, trường, nguồn và hash source đã đối chiếu.
- Nguồn sơ đồ: BaoCaoWord_SoDo; ảnh trang/nhật ký render: ../.runtime/bao-cao-word/round-4.
- Hướng dẫn field: BaoCaoWord_HuongDanCapNhatTruong.md.
- Điền trường/khoa/môn/lớp/GV/thành viên/MSSV, phân công thật, lời cam đoan/cảm ơn và phê duyệt đề tài. Mẫu mặc định chưa thay thế quy định riêng của trường.
- Nhóm cần kiểm tra quy định khai báo sử dụng AI, đọc lại và đối chiếu với sản phẩm trước khi nộp. Khi sửa nội dung phải cập nhật field và xuất PDF mới.
