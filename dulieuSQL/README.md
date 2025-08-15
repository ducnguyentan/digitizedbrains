# DigitizedBrains Data Storage System

Hệ thống lưu trữ và theo dõi dữ liệu người dùng cho website DigitizedBrains sử dụng MySQL và PHP.

## 📁 Cấu trúc thư mục

```
dulieuSQL/
├── database.sql           # Script tạo cơ sở dữ liệu và các bảng
├── db_connection.php      # Class kết nối và quản lý database
├── form_handler.php       # API xử lý các form và dữ liệu từ frontend
├── data_tracker.js        # JavaScript tracker cho frontend
├── admin_dashboard.php    # Dashboard quản trị để xem thống kê
├── export_data.php        # Script xuất dữ liệu ra file CSV
└── README.md             # Tài liệu hướng dẫn
```

## 🗄️ Cấu trúc Database

### Bảng chính:
- **`users`**: Thông tin người dùng và session
- **`contact_submissions`**: Dữ liệu form liên hệ
- **`service_requests`**: Yêu cầu dịch vụ
- **`newsletter_subscriptions`**: Đăng ký newsletter
- **`chatbot_conversations`**: Cuộc trò chuyện với chatbot
- **`page_visits`**: Lượt truy cập trang
- **`form_interactions`**: Tương tác với form
- **`ai_agent_interactions`**: Tương tác với AI agents
- **`file_downloads`**: Tải xuống file
- **`language_changes`**: Thay đổi ngôn ngữ

### Views:
- **`user_summary`**: Tổng hợp thông tin người dùng
- **`popular_pages`**: Trang phổ biến nhất
- **`service_analytics`**: Phân tích yêu cầu dịch vụ

## 🚀 Cài đặt

### 1. Tạo cơ sở dữ liệu

```sql
-- Chạy file database.sql trong MySQL
mysql -u root -p < database.sql
```

### 2. Cấu hình kết nối

Chỉnh sửa file `db_connection.php`:

```php
private $host = 'localhost';
private $dbname = 'digitizedbrains_data';
private $username = 'your_username';
private $password = 'your_password';
```

### 3. Thêm tracker vào trang web

Thêm vào cuối tất cả các trang HTML:

```html
<script src="dulieuSQL/data_tracker.js"></script>
```

### 4. Cấu hình server

Đảm bảo PHP và MySQL đã được cài đặt và chạy:
- PHP 7.4+
- MySQL 5.7+ hoặc MariaDB 10.2+
- PDO MySQL extension

## 📊 Theo dõi dữ liệu

### Dữ liệu được thu thập:

1. **Thông tin người dùng**:
   - Session ID, IP address, User Agent
   - Thời gian visit đầu/cuối
   - Ngôn ngữ ưa thích

2. **Tương tác trang**:
   - URL và tiêu đề trang
   - Thời gian xem trang
   - Chuyển đổi ngôn ngữ

3. **Form submissions**:
   - Contact forms
   - Service requests
   - Newsletter signups

4. **AI interactions**:
   - Chatbot conversations
   - AI Friends Talk usage
   - Agent interactions

5. **Downloads**:
   - PDF files, guides, whitepapers
   - Thời gian và loại file

## 🔧 Sử dụng API

### Gửi dữ liệu từ JavaScript:

```javascript
// Tự động được xử lý bởi data_tracker.js
// Hoặc thủ công:
fetch('dulieuSQL/form_handler.php', {
    method: 'POST',
    body: new FormData(form)
});
```

### Các action được hỗ trợ:

- `contact_form`: Gửi form liên hệ
- `newsletter_signup`: Đăng ký newsletter
- `service_request`: Yêu cầu dịch vụ
- `chatbot_message`: Tin nhắn chatbot
- `page_visit`: Truy cập trang
- `language_change`: Thay đổi ngôn ngữ
- `ai_agent_interaction`: Tương tác AI agent
- `file_download`: Tải xuống file

## 📈 Dashboard quản trị

Truy cập: `dulieuSQL/admin_dashboard.php`

### Tính năng:
- Thống kê tổng quan
- Danh sách liên hệ gần đây
- Yêu cầu dịch vụ
- Trang phổ biến nhất
- Phân tích người dùng
- Xuất dữ liệu CSV

## 📤 Xuất dữ liệu

URL: `dulieuSQL/export_data.php?type={type}`

### Các loại xuất:
- `contacts`: Danh sách liên hệ
- `service_requests`: Yêu cầu dịch vụ
- `analytics`: Phân tích người dùng
- `page_visits`: Lượt truy cập
- `chatbot_conversations`: Trò chuyện chatbot
- `ai_interactions`: Tương tác AI

## 🔒 Bảo mật

### Biện pháp bảo mật:
- Sanitize tất cả input data
- Sử dụng prepared statements
- Validation email và phone
- Error logging
- Session management

### Khuyến nghị:
- Thay đổi username/password database
- Giới hạn quyền truy cập admin dashboard
- Backup dữ liệu định kỳ
- Monitor logs thường xuyên

## 📝 Logs

Logs được ghi vào PHP error log:
- Database connection errors
- Form submission errors
- API call errors

## 🔄 Backup

### Script backup tự động:

```bash
#!/bin/bash
mysqldump -u username -p digitizedbrains_data > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Khôi phục:

```bash
mysql -u username -p digitizedbrains_data < backup_file.sql
```

## 📞 Hỗ trợ

Liên hệ: ducnguyen@digitizedbrains.online

---

**Lưu ý**: Đảm bảo tuân thủ GDPR và các quy định bảo mật dữ liệu khi triển khai hệ thống này.