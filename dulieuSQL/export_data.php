<?php
/**
 * Data Export Script for DigitizedBrains Analytics
 * Xuất dữ liệu ra file CSV
 */

require_once 'db_connection.php';

$type = $_GET['type'] ?? '';

if (empty($type)) {
    die('Export type is required');
}

try {
    $db = new DatabaseConnection();
    $pdo = $db->getPDO();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="digitizedbrains_' . $type . '_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    switch ($type) {
        case 'contacts':
            exportContacts($pdo, $output);
            break;
            
        case 'service_requests':
            exportServiceRequests($pdo, $output);
            break;
            
        case 'analytics':
            exportAnalytics($pdo, $output);
            break;
            
        case 'page_visits':
            exportPageVisits($pdo, $output);
            break;
            
        case 'chatbot_conversations':
            exportChatbotConversations($pdo, $output);
            break;
            
        case 'ai_interactions':
            exportAIInteractions($pdo, $output);
            break;
            
        default:
            fclose($output);
            die('Invalid export type');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Export error: ' . $e->getMessage());
}

function exportContacts($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'ID', 'Session ID', 'Họ tên', 'Email', 'Công ty', 'Điện thoại', 
        'Tin nhắn', 'Nguồn trang', 'Thời gian gửi'
    ]);
    
    // Get data
    $stmt = $pdo->query("
        SELECT id, session_id, name, email, company, phone, message, page_source, submitted_at
        FROM contact_submissions 
        ORDER BY submitted_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['session_id'],
            $row['name'],
            $row['email'],
            $row['company'],
            $row['phone'],
            $row['message'],
            $row['page_source'],
            $row['submitted_at']
        ]);
    }
}

function exportServiceRequests($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'ID', 'Session ID', 'Loại dịch vụ', 'Tên công ty', 'Người liên hệ', 
        'Email', 'Điện thoại', 'Quy mô công ty', 'Ngành nghề', 'Nhu cầu cụ thể',
        'Ngân sách', 'Thời gian', 'Nguồn trang', 'Trạng thái', 'Thời gian tạo'
    ]);
    
    // Get data
    $stmt = $pdo->query("
        SELECT id, session_id, service_type, company_name, contact_person, email, phone,
               company_size, industry, specific_needs, budget_range, timeline, 
               page_source, request_status, created_at
        FROM service_requests 
        ORDER BY created_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['session_id'],
            $row['service_type'],
            $row['company_name'],
            $row['contact_person'],
            $row['email'],
            $row['phone'],
            $row['company_size'],
            $row['industry'],
            $row['specific_needs'],
            $row['budget_range'],
            $row['timeline'],
            $row['page_source'],
            $row['request_status'],
            $row['created_at']
        ]);
    }
}

function exportAnalytics($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'Session ID', 'Lần đầu truy cập', 'Lần cuối truy cập', 'Tổng lượt truy cập',
        'Ngôn ngữ ưa thích', 'Tổng lượt xem trang', 'Số form liên hệ', 
        'Số yêu cầu dịch vụ', 'Số cuộc trò chuyện chatbot', 'Số file tải về'
    ]);
    
    // Get data
    $stmt = $pdo->query("SELECT * FROM user_summary ORDER BY last_visit DESC");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['session_id'],
            $row['first_visit'],
            $row['last_visit'],
            $row['total_visits'],
            $row['preferred_language'],
            $row['total_page_views'],
            $row['contact_forms_submitted'],
            $row['service_requests_made'],
            $row['chatbot_conversations'],
            $row['files_downloaded']
        ]);
    }
}

function exportPageVisits($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'ID', 'Session ID', 'URL trang', 'Tiêu đề trang', 'Thời gian xem (giây)', 'Thời gian truy cập'
    ]);
    
    // Get data
    $stmt = $pdo->query("
        SELECT id, session_id, page_url, page_title, visit_duration, visited_at
        FROM page_visits 
        ORDER BY visited_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['session_id'],
            $row['page_url'],
            $row['page_title'],
            $row['visit_duration'],
            $row['visited_at']
        ]);
    }
}

function exportChatbotConversations($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'ID', 'Session ID', 'Tin nhắn người dùng', 'Phản hồi bot', 'Ngữ cảnh trang', 'Thời gian'
    ]);
    
    // Get data
    $stmt = $pdo->query("
        SELECT id, session_id, user_message, bot_response, page_context, conversation_at
        FROM chatbot_conversations 
        ORDER BY conversation_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['session_id'],
            $row['user_message'],
            $row['bot_response'],
            $row['page_context'],
            $row['conversation_at']
        ]);
    }
}

function exportAIInteractions($pdo, $output) {
    // Write headers
    fputcsv($output, [
        'ID', 'Session ID', 'Loại Agent', 'Loại tương tác', 'Dữ liệu tương tác', 'Thời gian'
    ]);
    
    // Get data
    $stmt = $pdo->query("
        SELECT id, session_id, agent_type, interaction_type, interaction_data, interacted_at
        FROM ai_agent_interactions 
        ORDER BY interacted_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['session_id'],
            $row['agent_type'],
            $row['interaction_type'],
            $row['interaction_data'],
            $row['interacted_at']
        ]);
    }
}
?>