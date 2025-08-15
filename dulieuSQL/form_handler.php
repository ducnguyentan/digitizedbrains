<?php
/**
 * Form Handler for DigitizedBrains Website
 * Xử lý và lưu trữ dữ liệu form từ tất cả các trang
 */

require_once 'db_connection.php';

// Set content type for AJAX responses
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = new DatabaseConnection();
    $sessionId = getSessionId();
    $clientIP = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Ensure user exists in database
    $db->getOrCreateUser($sessionId, $clientIP, $userAgent);
    
    $response = ['success' => false, 'message' => ''];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'contact_form':
                $response = handleContactForm($db, $sessionId);
                break;
                
            case 'newsletter_signup':
                $response = handleNewsletterSignup($db, $sessionId);
                break;
                
            case 'service_request':
                $response = handleServiceRequest($db, $sessionId);
                break;
                
            case 'chatbot_message':
                $response = handleChatbotMessage($db, $sessionId);
                break;
                
            case 'page_visit':
                $response = handlePageVisit($db, $sessionId);
                break;
                
            case 'language_change':
                $response = handleLanguageChange($db, $sessionId);
                break;
                
            case 'ai_agent_interaction':
                $response = handleAIAgentInteraction($db, $sessionId);
                break;
                
            case 'file_download':
                $response = handleFileDownload($db, $sessionId);
                break;
                
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }
    }
    
} catch (Exception $e) {
    error_log("Form handler error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error occurred'];
}

echo json_encode($response);

/**
 * Handle contact form submissions
 */
function handleContactForm($db, $sessionId) {
    $required = ['name', 'email', 'message'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }
    
    $data = [
        'session_id' => $sessionId,
        'name' => sanitizeInput($_POST['name']),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'company' => sanitizeInput($_POST['company'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'message' => sanitizeInput($_POST['message']),
        'page_source' => sanitizeInput($_POST['page_source'] ?? '')
    ];
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    if ($db->saveContactSubmission($data)) {
        return ['success' => true, 'message' => 'Contact form submitted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to save contact form'];
    }
}

/**
 * Handle newsletter signups
 */
function handleNewsletterSignup($db, $sessionId) {
    if (empty($_POST['email'])) {
        return ['success' => false, 'message' => 'Email is required'];
    }
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    try {
        $pdo = $db->getPDO();
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_subscriptions (session_id, email, name) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        
        if ($stmt->execute([$sessionId, $email, $_POST['name'] ?? ''])) {
            return ['success' => true, 'message' => 'Successfully subscribed to newsletter'];
        } else {
            return ['success' => false, 'message' => 'Failed to subscribe'];
        }
    } catch (PDOException $e) {
        error_log("Newsletter signup error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error'];
    }
}

/**
 * Handle service requests
 */
function handleServiceRequest($db, $sessionId) {
    $required = ['service_type', 'contact_person', 'email'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }
    
    $data = [
        'session_id' => $sessionId,
        'service_type' => sanitizeInput($_POST['service_type']),
        'company_name' => sanitizeInput($_POST['company_name'] ?? ''),
        'contact_person' => sanitizeInput($_POST['contact_person']),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'company_size' => sanitizeInput($_POST['company_size'] ?? ''),
        'industry' => sanitizeInput($_POST['industry'] ?? ''),
        'specific_needs' => sanitizeInput($_POST['specific_needs'] ?? ''),
        'budget_range' => sanitizeInput($_POST['budget_range'] ?? ''),
        'timeline' => sanitizeInput($_POST['timeline'] ?? ''),
        'page_source' => sanitizeInput($_POST['page_source'] ?? '')
    ];
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }
    
    if ($db->saveServiceRequest($data)) {
        return ['success' => true, 'message' => 'Service request submitted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to save service request'];
    }
}

/**
 * Handle chatbot messages
 */
function handleChatbotMessage($db, $sessionId) {
    if (empty($_POST['message'])) {
        return ['success' => false, 'message' => 'Message is required'];
    }
    
    $userMessage = sanitizeInput($_POST['message']);
    $pageContext = sanitizeInput($_POST['page_context'] ?? '');
    
    // Here you would integrate with your AI chatbot service
    $botResponse = generateBotResponse($userMessage);
    
    if ($db->logChatbotConversation($sessionId, $userMessage, $botResponse, $pageContext)) {
        return [
            'success' => true, 
            'message' => 'Message logged',
            'bot_response' => $botResponse
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to log conversation'];
    }
}

/**
 * Handle page visits
 */
function handlePageVisit($db, $sessionId) {
    $pageUrl = sanitizeInput($_POST['page_url'] ?? '');
    $pageTitle = sanitizeInput($_POST['page_title'] ?? '');
    $visitDuration = intval($_POST['visit_duration'] ?? 0);
    
    if ($db->logPageVisit($sessionId, $pageUrl, $pageTitle, $visitDuration)) {
        return ['success' => true, 'message' => 'Page visit logged'];
    } else {
        return ['success' => false, 'message' => 'Failed to log page visit'];
    }
}

/**
 * Handle language changes
 */
function handleLanguageChange($db, $sessionId) {
    $previousLang = sanitizeInput($_POST['previous_language'] ?? '');
    $newLang = sanitizeInput($_POST['new_language'] ?? '');
    $pageUrl = sanitizeInput($_POST['page_url'] ?? '');
    
    if ($db->updateLanguagePreference($sessionId, $previousLang, $newLang, $pageUrl)) {
        return ['success' => true, 'message' => 'Language preference updated'];
    } else {
        return ['success' => false, 'message' => 'Failed to update language preference'];
    }
}

/**
 * Handle AI agent interactions
 */
function handleAIAgentInteraction($db, $sessionId) {
    $agentType = sanitizeInput($_POST['agent_type'] ?? '');
    $interactionType = sanitizeInput($_POST['interaction_type'] ?? '');
    $interactionData = $_POST['interaction_data'] ?? null;
    
    if ($db->logAIAgentInteraction($sessionId, $agentType, $interactionType, $interactionData)) {
        return ['success' => true, 'message' => 'AI interaction logged'];
    } else {
        return ['success' => false, 'message' => 'Failed to log AI interaction'];
    }
}

/**
 * Handle file downloads
 */
function handleFileDownload($db, $sessionId) {
    $fileName = sanitizeInput($_POST['file_name'] ?? '');
    $fileType = sanitizeInput($_POST['file_type'] ?? '');
    $fileCategory = sanitizeInput($_POST['file_category'] ?? '');
    $downloadUrl = sanitizeInput($_POST['download_url'] ?? '');
    
    if ($db->logFileDownload($sessionId, $fileName, $fileType, $fileCategory, $downloadUrl)) {
        return ['success' => true, 'message' => 'File download logged'];
    } else {
        return ['success' => false, 'message' => 'Failed to log file download'];
    }
}

/**
 * Simple bot response generator (integrate with your AI service)
 */
function generateBotResponse($userMessage) {
    // Simple responses - replace with your AI service integration
    $responses = [
        'hello' => 'Xin chào! Tôi có thể giúp gì cho bạn về dịch vụ chuyển đổi số của DigitizedBrains?',
        'help' => 'Tôi có thể giúp bạn tìm hiểu về các dịch vụ AI Agent, chuyển đổi số, và giải pháp doanh nghiệp của chúng tôi.',
        'services' => 'Chúng tôi cung cấp: AI Agents, Digital Transformation, Cybersecurity, Data Analytics, và Staff Training.',
        'contact' => 'Bạn có thể liên hệ với chúng tôi qua email: ducnguyen@digitizedbrains.online hoặc điện thoại: 0913 723 667',
        'ai' => 'AI Agents của chúng tôi bao gồm: Data Analysis, Process Automation, Business Intelligence, và nhiều giải pháp khác.',
        'price' => 'Để biết thêm thông tin về giá cả, vui lòng liên hệ trực tiếp với chúng tôi để được tư vấn phù hợp với nhu cầu doanh nghiệp.',
    ];
    
    $userMessage = strtolower($userMessage);
    
    foreach ($responses as $keyword => $response) {
        if (strpos($userMessage, $keyword) !== false) {
            return $response;
        }
    }
    
    return 'Cảm ơn bạn đã liên hệ! Để được hỗ trợ tốt nhất, vui lòng để lại thông tin liên hệ hoặc gọi điện thoại: 0913 723 667';
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>