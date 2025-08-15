-- DigitizedBrains User Data Storage Database
-- Lưu trữ dữ liệu người dùng từ tất cả các trang web

-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS digitizedbrains_data;
USE digitizedbrains_data;

-- Bảng lưu trữ thông tin người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    first_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_visits INT DEFAULT 1,
    preferred_language VARCHAR(10) DEFAULT 'vi'
);

-- Bảng lưu trữ contact form submissions
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    phone VARCHAR(20),
    message TEXT,
    page_source VARCHAR(100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ newsletter subscriptions
CREATE TABLE newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ chatbot conversations
CREATE TABLE chatbot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    user_message TEXT NOT NULL,
    bot_response TEXT,
    page_context VARCHAR(100),
    conversation_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ page visits
CREATE TABLE page_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(255),
    visit_duration INT, -- in seconds
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ form interactions
CREATE TABLE form_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    form_type VARCHAR(100), -- 'contact', 'newsletter', 'quote_request', etc.
    form_data JSON, -- Store all form fields as JSON
    page_url VARCHAR(500),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ service requests / consultation requests
CREATE TABLE service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    service_type VARCHAR(100), -- 'ai_agents', 'digital_transformation', 'consultation', etc.
    company_name VARCHAR(255),
    contact_person VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company_size ENUM('startup', 'small', 'medium', 'large', 'enterprise'),
    industry VARCHAR(100),
    specific_needs TEXT,
    budget_range VARCHAR(50),
    timeline VARCHAR(50),
    page_source VARCHAR(100),
    request_status ENUM('new', 'contacted', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ AI agent interactions
CREATE TABLE ai_agent_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    agent_type VARCHAR(100), -- 'ai_friends_talk', 'chatbot', etc.
    interaction_type VARCHAR(50), -- 'launch', 'message', 'topic_select', etc.
    interaction_data JSON,
    interacted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ file downloads
CREATE TABLE file_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50), -- 'pdf', 'doc', etc.
    file_category VARCHAR(100), -- 'guide', 'whitepaper', 'case_study', etc.
    download_url VARCHAR(500),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Bảng lưu trữ language preferences
CREATE TABLE language_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    previous_language VARCHAR(10),
    new_language VARCHAR(10),
    page_url VARCHAR(500),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES users(session_id)
);

-- Tạo indexes để tối ưu hóa performance
CREATE INDEX idx_users_session ON users(session_id);
CREATE INDEX idx_contact_session ON contact_submissions(session_id);
CREATE INDEX idx_contact_email ON contact_submissions(email);
CREATE INDEX idx_newsletter_email ON newsletter_subscriptions(email);
CREATE INDEX idx_page_visits_session ON page_visits(session_id);
CREATE INDEX idx_page_visits_url ON page_visits(page_url);
CREATE INDEX idx_chatbot_session ON chatbot_conversations(session_id);
CREATE INDEX idx_service_requests_session ON service_requests(session_id);
CREATE INDEX idx_service_requests_status ON service_requests(request_status);
CREATE INDEX idx_ai_interactions_session ON ai_agent_interactions(session_id);
CREATE INDEX idx_downloads_session ON file_downloads(session_id);

-- Views để truy xuất dữ liệu dễ dàng hơn
CREATE VIEW user_summary AS
SELECT 
    u.session_id,
    u.first_visit,
    u.last_visit,
    u.total_visits,
    u.preferred_language,
    COUNT(DISTINCT pv.id) as total_page_views,
    COUNT(DISTINCT cs.id) as contact_forms_submitted,
    COUNT(DISTINCT sr.id) as service_requests_made,
    COUNT(DISTINCT cc.id) as chatbot_conversations,
    COUNT(DISTINCT fd.id) as files_downloaded
FROM users u
LEFT JOIN page_visits pv ON u.session_id = pv.session_id
LEFT JOIN contact_submissions cs ON u.session_id = cs.session_id
LEFT JOIN service_requests sr ON u.session_id = sr.session_id
LEFT JOIN chatbot_conversations cc ON u.session_id = cc.session_id
LEFT JOIN file_downloads fd ON u.session_id = fd.session_id
GROUP BY u.session_id;

-- View cho popular pages
CREATE VIEW popular_pages AS
SELECT 
    page_url,
    page_title,
    COUNT(*) as visit_count,
    AVG(visit_duration) as avg_duration
FROM page_visits 
WHERE page_url IS NOT NULL
GROUP BY page_url, page_title
ORDER BY visit_count DESC;

-- View cho service request analytics
CREATE VIEW service_analytics AS
SELECT 
    service_type,
    COUNT(*) as total_requests,
    COUNT(CASE WHEN request_status = 'completed' THEN 1 END) as completed_requests,
    COUNT(CASE WHEN request_status = 'new' THEN 1 END) as pending_requests,
    industry,
    company_size
FROM service_requests
GROUP BY service_type, industry, company_size;